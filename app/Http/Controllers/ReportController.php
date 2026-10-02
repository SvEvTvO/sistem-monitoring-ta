<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ProjectWeek;
use App\Services\ProjectWeekService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    protected $reportService;
    protected $projectWeekService;

    public function __construct(ReportService $reportService, ProjectWeekService $projectWeekService)
    {
        $this->reportService = $reportService;
        $this->projectWeekService = $projectWeekService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Report::where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['projectWeek', 'division']);

        // Filter Pencarian Judul
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter Jenis Laporan
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter Status Laporan (Baru ditambahkan untuk tombol Tinjau Revisi)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $reports = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $hasRevision = \App\Models\Report::where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('status', 'REVISION_REQUIRED')
            ->exists();

        // JIKA REQUEST DARI AJAX: Kembalikan file partial table saja
        if ($request->ajax()) {
            return view('reports.partials.table', compact('reports'))->render();
        }

        // JIKA BUKAN AJAX (Load Halaman Pertama): Kembalikan full view
        return view('reports.index', compact('reports', 'hasRevision'));
    
    }

    public function create()
    {
        $user = Auth::user();
        $membership = $user->projectMembers()->with('project')->first();
        
        if (!$membership) {
            return redirect()->route('dashboard')->with('error', 'Kamu tidak tergabung dalam project aktif.');
        }

        $currentWeek = $this->projectWeekService->getCurrentWeek($membership->project);

        // Validasi: Apakah saat ini berada pada periode Sabtu 00:01 - Minggu 23:59[cite: 1]
        if (!$currentWeek || !$this->projectWeekService->isReportWindowOpen($currentWeek)) {
            return redirect()->route('reports.index')->with('error', 'Waktu pelaporan mingguan belum dibuka atau sudah ditutup.');
        }

        // Validasi: 1 Laporan per user per minggu[cite: 3]
        $hasReport = Report::where('project_week_id', $currentWeek->id)
            ->where('author_id', $user->id)
            ->where('type', 'PERSONAL')
            ->exists();

        if ($hasReport) {
            return redirect()->route('reports.index')->with('error', 'Kamu sudah membuat laporan untuk minggu ini.');
        }

        return view('reports.create', compact('currentWeek', 'membership'));
    }

    public function store(Request $request)
    {
        // Validasi input form[cite: 1]
        $validated = $request->validate([
            'project_week_id' => 'required|exists:project_weeks,id',
            'title' => 'required|string|max:200',
            'work_done' => 'required|string',
            'achievements' => 'nullable|string',
            'obstacles' => 'nullable|string',
            'solutions' => 'nullable|string',
            'next_plan' => 'required|string',
            'support_needed' => 'nullable|string',
        ]);

        $week = ProjectWeek::findOrFail($validated['project_week_id']);
        $user = Auth::user();

        try {
            // Eksekusi pembuatan laporan melalui Service[cite: 3]
            $this->reportService->createPersonalReport($user, $week, $validated);
            return redirect()->route('reports.index')->with('success', 'Laporan berhasil dikirim dan berstatus SUBMITTED!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function createDivision()
    {
        $user = Auth::user();
        $ledDivision = $user->ledDivisions()->with('project')->first();
        
        if (!$ledDivision) {
            return redirect()->route('dashboard')->with('error', 'Kamu bukan ketua divisi aktif.');
        }

        $currentWeek = $this->projectWeekService->getCurrentWeek($ledDivision->project);

        if (!$currentWeek || !$this->projectWeekService->isReportWindowOpen($currentWeek)) {
            return redirect()->route('reports.index')->with('error', 'Waktu pelaporan mingguan belum dibuka atau sudah ditutup.');
        }

        $hasReport = Report::where('project_week_id', $currentWeek->id)
            ->where('division_id', $ledDivision->id)
            ->where('type', 'DIVISION')
            ->exists();

        if ($hasReport) {
            return redirect()->route('reports.index')->with('error', 'Divisimu sudah membuat laporan untuk minggu ini.');
        }

        return view('reports.create-division', compact('currentWeek', 'ledDivision'));
    }

    public function storeDivision(Request $request)
    {
        $validated = $request->validate([
            'project_week_id' => 'required|exists:project_weeks,id',
            'title' => 'required|string|max:200',
            'work_done' => 'required|string',
            'achievements' => 'nullable|string',
            'obstacles' => 'nullable|string',
            'solutions' => 'nullable|string',
            'next_plan' => 'required|string',
            'support_needed' => 'nullable|string',
            'progress_percentage' => 'required|numeric|min:0|max:100', // Validasi progress 0 - 100%[cite: 1, 3]
        ]);

        $week = ProjectWeek::findOrFail($validated['project_week_id']);
        $user = Auth::user();

        try {
            $this->reportService->createDivisionReport($user, $week, $validated);
            return redirect()->route('reports.index')->with('success', 'Laporan Divisi berhasil dikirim dan berstatus SUBMITTED!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    // ==========================================
    // FUNGSI UNTUK MELIHAT DETAIL LAPORAN
    // ==========================================
    public function show(Report $report)
    {
        // Muat relasi tabel agar datanya lengkap
        $report->load(['projectWeek', 'division', 'author', 'reviewer', 'evaluationLabel']);
        return view('reports.show', compact('report'));
    }

    // ==========================================
    // FUNGSI UNTUK HALAMAN EDIT LAPORAN
    // ==========================================
    public function edit(Report $report)
    {
        // Validasi: Hanya penulis laporan yang boleh mengedit
        if ($report->author_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Laporan yang sudah disetujui (APPROVED) tidak boleh diedit lagi
        if ($report->status === 'APPROVED') {
            return redirect()->route('reports.index')->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        return view('reports.edit', compact('report'));
    }

    // ==========================================
    // FUNGSI UNTUK MENYIMPAN PERUBAHAN LAPORAN
    // ==========================================
    public function update(Request $request, Report $report)
    {
        if ($report->author_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($report->status === 'APPROVED') {
            return redirect()->route('reports.index')->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'work_done' => 'required|string',
            'achievements' => 'nullable|string',
            'obstacles' => 'nullable|string',
            'solutions' => 'nullable|string',
            'next_plan' => 'required|string',
            'support_needed' => 'nullable|string',
        ]);

        // Jika laporan diedit karena disuruh revisi, otomatis ubah statusnya jadi SUBMITTED lagi
        $status = $report->status === 'REVISION_REQUIRED' ? 'SUBMITTED' : $report->status;

        $report->update(array_merge($validated, ['status' => $status]));

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil diperbarui.');
    }
}