<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportEvaluationLabel;

class EvaluationLabelController extends Controller
{
    public function index()
    {
        $labels = ReportEvaluationLabel::orderBy('sort_order', 'asc')->get();
        return view('admin.evaluation-labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:1'
        ]);

        ReportEvaluationLabel::create($validated);
        return back()->with('success', 'Label evaluasi baru berhasil ditambahkan.');
    }

    public function update(Request $request, ReportEvaluationLabel $label)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
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
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal! Label ini sudah pernah digunakan pada laporan.');
        }
    }
}
