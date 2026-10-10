<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportEvaluationLabel;

class EvaluationLabelController extends Controller
{
    public function index()
    {
        // [OPTIMASI PROAKTIF]: Menggunakan withCount('reports')
        // Ini memungkinkan kamu memanggil $label->reports_count di file Blade
        // untuk menampilkan "Dipakai di 10 Laporan" tanpa memicu query tambahan (N+1).
        // *Pastikan model ReportEvaluationLabel memiliki fungsi relasi reports()
        $labels = ReportEvaluationLabel::withCount('reports')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.evaluation-labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            // Deskripsi ditambahkan sebagai jaga-jaga karena ada di struktur database
            'description' => 'nullable|string',
            'sort_order' => 'required|integer|min:1'
        ]);

        ReportEvaluationLabel::create($validated);
        return back()->with('success', 'Label evaluasi baru berhasil ditambahkan.');
    }

    public function update(Request $request, ReportEvaluationLabel $label)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer|min:1'
        ]);

        $label->update($validated);
        return back()->with('success', 'Label evaluasi berhasil diperbarui.');
    }

    public function destroy(ReportEvaluationLabel $label)
    {
        try {
            $label->delete();
            return back()->with('success', 'Label evaluasi berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // [OPTIMASI TANGKAPAN ERROR]:
            // Hanya tangkap penolakan query database (misal karena Foreign Key Constraint),
            // biarkan error sistem lainnya tetap muncul agar mudah di-debug saat development.
            return back()->with('error', 'Gagal! Label ini tidak bisa dihapus karena sedang digunakan pada data laporan atau riwayat evaluasi.');
        }
    }
}
