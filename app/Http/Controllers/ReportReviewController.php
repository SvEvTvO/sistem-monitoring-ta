<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportEvaluationLabel;
use App\Services\ReportReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReportReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        
        // [OPTIMASI TAHAP 1]: Konversi langsung ke Array untuk menghemat memory Collection
        $divisionIds = $user->ledDivisions()->pluck('id')->toArray();
        $projectIds = $user->ledProjects()->pluck('id')->toArray();

        // [OPTIMASI TAHAP 2]: Tambahkan 'project' dalam daftar Eager Loading
        // untuk mencegah N+1 jika tampilan tabel membutuhkan data nama project
        $query = Report::with(['author', 'division', 'projectWeek', 'project'])
            ->where(function($q) use ($divisionIds, $projectIds) {
                $q->whereIn('division_id', $divisionIds)->where('type', 'PERSONAL')
                  ->orWhereIn('project_id', $projectIds)->where('type', 'DIVISION');
            })
            ->where('author_id', '!=', $user->id); // Mengecualikan laporan milik sendiri

        // 1. Filter Pencarian (Judul Laporan ATAU Nama Penulis)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('author', function($qAuthor) use ($search) {
                      $qAuthor->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Urutkan: Yang SUBMITTED (Butuh diperiksa) di paling atas, sisanya berdasarkan tanggal terbaru
        $reportsToReview = $query->orderByRaw("CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 2 END")
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(10)
                                 ->withQueryString();

        // Jika Request dari AJAX (Filter/Pagination)
        if ($request->ajax()) {
            return view('reviews.partials.table', compact('reportsToReview'))->render();
        }

        return view('reviews.index', compact('reportsToReview'));
    }

    public function show(Report $report)
    {
        // [OPTIMASI FATAL N+1]: Wajib meload seluruh relasi SEBELUM masuk ke Gate / Policy
        // agar proses verifikasi hak akses dan rendering view berjalan dalam 0 query tambahan!
        $report->load(['author', 'division', 'projectWeek', 'project', 'reviewer', 'evaluationLabel']);

        // Validasi hak akses menggunakan ReportPolicy
        Gate::authorize('review', $report);

        $user = Auth::user();

        // Jika laporan masih SUBMITTED, otomatis ubah menjadi REVIEWED karena sudah dibaca
        if ($report->status === 'SUBMITTED') {
            $this->reviewService->markAsReviewed($report, $user);
        }

        // Ambil data label evaluasi (Sangat Baik, Baik, dsb) untuk form keputusan
        $labels = ReportEvaluationLabel::where('is_active', true)->orderBy('sort_order')->get();

        return view('reviews.show', compact('report', 'labels'));
    }

    public function decide(Request $request, Report $report)
    {
        // [OPTIMASI]: Load relasi dasar yang umumnya dibutuhkan oleh Gate (Policy)
        $report->load(['project', 'division']);

        // Validasi hak akses
        Gate::authorize('review', $report);

        // Validasi input form: wajib menyertakan label dan komentar
        $validated = $request->validate([
            'decision' => 'required|in:APPROVED,REVISION_REQUIRED',
            'evaluation_label_id' => 'required|exists:report_evaluation_labels,id',
            'review_comment' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            if ($validated['decision'] === 'APPROVED') {
                $this->reviewService->approve($report, $user, $validated);
                $message = 'Laporan berhasil disetujui (APPROVED).';
            } else {
                $this->reviewService->requestRevision($report, $user, $validated);
                $message = 'Laporan dikembalikan untuk perbaikan (REVISION REQUIRED).';
            }

            return redirect()->route('reviews.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
