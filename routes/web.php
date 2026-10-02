<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectTargetController;
use App\Http\Controllers\AnnouncementController;

use App\Http\Middleware\EnsureProjectMember;

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // =========================================================
    // AREA BEBAS AKSES (Untuk Semua User Login)
    // =========================================================
    
    // 1. Halaman Ruang Tunggu (Unemployed)
    Route::get('/waiting', function () {
        $user = Auth::user();
        
        // Jika ternyata dia sudah punya role, paksa kembali ke dashboard utama
        if ($user->ledProjects()->exists() || $user->projectMembers()->exists()) {
            return redirect()->route('dashboard');
        }
        
        return view('dashboard.waiting');
    })->name('dashboard.waiting');

    // 2. Modul Kelola Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // =========================================================
    // AREA TERLARANG (Hanya Untuk User yang Memiliki Divisi/Role)
    // =========================================================
    Route::middleware([EnsureProjectMember::class])->group(function () {
        
        // Dashboard Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Modul Laporan (Pembuatan & Edit)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/create', [ReportController::class, 'create'])->name('create');
            Route::post('/', [ReportController::class, 'store'])->name('store');
            
            // Laporan Divisi Khusus
            Route::get('/create-division', [ReportController::class, 'createDivision'])->name('create-division');
            Route::post('/store-division', [ReportController::class, 'storeDivision'])->name('store-division');

            Route::get('/{report}', [ReportController::class, 'show'])->name('show');
            Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
            Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        });

        // Modul Review (Khusus Ketua Divisi & Ketua Project)
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReportReviewController::class, 'index'])->name('index');
            Route::get('/{report}', [ReportReviewController::class, 'show'])->name('show');
            Route::post('/{report}/decide', [ReportReviewController::class, 'decide'])->name('decide');
        });

        // Modul Target Project
        Route::prefix('targets')->name('targets.')->group(function () {
            Route::get('/', [ProjectTargetController::class, 'index'])->name('index');
            Route::get('/create', [ProjectTargetController::class, 'create'])->name('create');
            Route::post('/', [ProjectTargetController::class, 'store'])->name('store');
            Route::get('/{target}', [ProjectTargetController::class, 'show'])->name('show');
            Route::get('/{target}/edit', [ProjectTargetController::class, 'edit'])->name('edit');
            Route::put('/{target}', [ProjectTargetController::class, 'update'])->name('update');
            
            // TAMBAHKAN BARIS INI UNTUK DELETE:
            Route::delete('/{target}', [ProjectTargetController::class, 'destroy'])->name('destroy');
            
            Route::post('/{target}/complete', [ProjectTargetController::class, 'complete'])->name('complete');
        });

        
        // Modul Pengumuman
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('index');
            Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');
            Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        });


        // Modul Manajemen Anggota
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MemberController::class, 'index'])->name('index');
            Route::post('/division', [\App\Http\Controllers\MemberController::class, 'storeDivision'])->name('storeDivision');
            Route::post('/division/{division}/leader', [\App\Http\Controllers\MemberController::class, 'setLeader'])->name('setLeader');
            Route::post('/assign', [\App\Http\Controllers\MemberController::class, 'assignMember'])->name('assign');
            Route::delete('/{member}/remove', [\App\Http\Controllers\MemberController::class, 'removeMember'])->name('remove');
            Route::get('/{member}', [\App\Http\Controllers\MemberController::class, 'show'])->name('show');
        });

    }); // <-- Akhir dari Middleware EnsureProjectMember
});

require __DIR__.'/auth.php';