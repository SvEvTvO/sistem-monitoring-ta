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

// Redirect root dinamis berdasarkan status user
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // =========================================================
    // AREA BEBAS AKSES (Untuk Semua User Login)
    // =========================================================

    // 1. Halaman Ruang Tunggu (Unemployed)
    Route::get('/waiting', function () {
        $user = Auth::user();

        // VALIDASI BARU: Jika ini adalah ADMIN, lemparkan langsung ke Dashboard Admin-nya!
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Jika ternyata dia sudah punya role (Ketua/Anggota), paksa ke dashboard utama
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


// ==========================================
        // MODUL ADMIN PANEL
        // ==========================================
        Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\EnsureIsAdmin::class])->group(function () {
            // Dashboard Admin
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

            // Pusat Data Master
            Route::prefix('master-data')->name('master.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\MasterDataController::class, 'index'])->name('index');

                // CREATE
                Route::post('/academic-years', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeAcademicYear'])->name('academic-years.store');
                Route::post('/departments', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeDepartment'])->name('departments.store');
                Route::post('/levels', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeLevel'])->name('levels.store');
                Route::post('/classes', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeClass'])->name('classes.store');

                // UPDATE
                Route::put('/academic-years/{academicYear}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateAcademicYear'])->name('academic-years.update');
                Route::put('/departments/{department}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateDepartment'])->name('departments.update');
                Route::put('/levels/{level}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateLevel'])->name('levels.update');
                Route::put('/classes/{schoolClass}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateClass'])->name('classes.update');

                // DELETE
                Route::delete('/academic-years/{academicYear}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyAcademicYear'])->name('academic-years.destroy');
                Route::delete('/departments/{department}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyDepartment'])->name('departments.destroy');
                Route::delete('/levels/{level}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyLevel'])->name('levels.destroy');
                Route::delete('/classes/{schoolClass}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyClass'])->name('classes.destroy');
            });


            // Manajemen Pengguna (Users)
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
                Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show'); // <-- TAMBAHKAN INI
                Route::put('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
            });

            // Manajemen Project
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ProjectController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\ProjectController::class, 'store'])->name('store');
                Route::put('/{project}', [\App\Http\Controllers\Admin\ProjectController::class, 'update'])->name('update');
                Route::delete('/{project}', [\App\Http\Controllers\Admin\ProjectController::class, 'destroy'])->name('destroy');
            });

            // Manajemen Label Evaluasi
            Route::prefix('evaluation-labels')->name('evaluation-labels.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\EvaluationLabelController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\EvaluationLabelController::class, 'store'])->name('store');
                Route::put('/{label}', [\App\Http\Controllers\Admin\EvaluationLabelController::class, 'update'])->name('update');
                Route::delete('/{label}', [\App\Http\Controllers\Admin\EvaluationLabelController::class, 'destroy'])->name('destroy');
            });

        // Pemantauan Audit Log
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
