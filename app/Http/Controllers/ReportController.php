<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ProjectWeek;
use App\Services\ProjectWeekService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportService;
    protected $projectWeekService;

    public function __construct(ReportService $reportService, ProjectWeekService $projectWeekService)
    {
        $this->reportService = $reportService;
        $this->projectWeekService = $projectWeekService;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        // [OPTIMASI TAHAP 1]: Tambahkan eager load 'evaluationLabel' dan 'reviewer'
        // Mencegah N+1 saat memunculkan label nilai atau nama peninjau di baris tabel
        $query = Report::where('author_id', $userId)
            ->with(['projectWeek', 'division', 'evaluationLabel', 'reviewer']);

        // Filter Pencarian Judul
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter Jenis Laporan
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter Status Laporan
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

        // [PERFORMA AMAN]: Penggunaan exists() di sini sudah SANGAT TEPAT.
        // Berjalan murni di level SQL (SELECT EXISTS) tanpa memberatkan RAM PHP.
        $hasRevision = Report::where('author_id', $userId)
            ->where('status', 'REVISION_REQUIRED')
            ->exists();

        if ($request->ajax()) {
            return view('reports.partials.table', compact('reports'))->render();
        }

        return view('reports.index', compact('reports', 'hasRevision'));
    }

    public function create()
    {
        $user = Auth::user();

        // [OPTIMASI]: Pastikan project di-load agar tidak meleset
        $membership = $user->projectMembers()->with('project')->first();

        if (!$membership) {
            return redirect()->route('dashboard')->with('error', 'Kamu tidak tergabung dalam project aktif.');
        }

        $currentWeek = $this->projectWeekService->getCurrentWeek($membership->project);

        if (!$currentWeek || !$this->projectWeekService->isReportWindowOpen($currentWeek)) {
            return redirect()->route('reports.index')->with('error', 'Waktu pelaporan mingguan belum dibuka atau sudah ditutup.');
        }

        // Menggunakan exists() adalah langkah performa yang sangat efisien
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
            'progress_percentage' => 'required|numeric|min:0|max:100', 
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

    public function show(Report $report)
    {
        // [PERFORMA AMAN]: Ini sudah SANGAT BAIK karena eksplisit mencegah N+1 di halaman detail!
        $report->load(['projectWeek', 'division', 'author', 'reviewer', 'evaluationLabel']);
        return view('reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        // [OPTIMASI TAHAP 2]: Amankan view 'edit' dari N+1 jika view menampilkan informasi minggu/divisi
        $report->load(['projectWeek', 'division']);

        if ($report->author_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($report->status === 'APPROVED') {
            return redirect()->route('reports.index')->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        return view('reports.edit', compact('report'));
    }

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

        $status = $report->status === 'REVISION_REQUIRED' ? 'SUBMITTED' : $report->status;

        $report->update(array_merge($validated, ['status' => $status]));

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil diperbarui.');
    }
}
