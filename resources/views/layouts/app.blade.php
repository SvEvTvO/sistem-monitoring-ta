<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Monitoring TA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tabler Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-neutral-bg text-text-primary overflow-hidden">
    <!-- Wrapper Utama: Flex Row -->
    <div class="flex h-screen w-full">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-neutral-surface border-r border-neutral-border flex flex-col shrink-0 shadow-sm z-20">
            <!-- Branding / Logo -->
            <div class="h-16 flex items-center px-6 border-b border-neutral-border shrink-0">
                <i class="ti ti-device-laptop text-2xl text-primary mr-2"></i>
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight text-primary leading-tight">Monitoring TA</span>
                    <!-- Label tambahan khusus admin -->
                    @if(auth()->user()->is_admin)
                        <span class="text-[10px] font-black uppercase text-semantic-warning tracking-widest">Admin Panel</span>
                    @endif
                </div>
            </div>

            <!-- Navigasi Menu -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 flex flex-col">

                <!-- Pengecekan Hak Akses User -->
                @php
                    $user = auth()->user();
                    $isProjectLeader = $user->ledProjects()->exists();
                    $isDivisionLeader = $user->ledDivisions()->exists();

                    // hasProject menentukan apakah dia "Employed" atau "Unemployed"
                    $hasProject = $isProjectLeader || $isDivisionLeader || $user->projectMembers()->exists();

                    // Hak khusus manajemen
                    $canManageMembers = $isProjectLeader || $isDivisionLeader;
                @endphp

                <!-- LOGIKA PEMISAH SIDEBAR BERDASARKAN ROLE -->
                @if($user->is_admin)
                    <!-- ========================================== -->
                    <!-- MENU KHUSUS ADMIN PANEL -->
                    <!-- ========================================== -->
                    <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3">Utama</div>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-layout-dashboard text-xl mr-3"></i> Dashboard Admin
                    </a>

                    <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3 mt-2">Operasional</div>

                    <a href="{{ route('admin.projects.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('admin.projects.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-rocket text-xl mr-3"></i> Manajemen Project
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('admin.users.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-users text-xl mr-3"></i> Manajemen Pengguna
                    </a>

                    <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3 mt-2">Sistem & Master</div>

                    <a href="{{ route('admin.master.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('admin.master.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-database text-xl mr-3"></i> Pusat Data Master
                    </a>

                    <a href="{{ route('admin.evaluation-labels.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('admin.evaluation-labels.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-tags text-xl mr-3"></i> Label Evaluasi
                    </a>

                    <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-history text-xl mr-3"></i> Audit Logs
                    </a>

                @else
                    <!-- ========================================== -->
                    <!-- MENU PENGGUNA BIASA (Siswa/Ketua) -->
                    <!-- ========================================== -->
                    <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3">Menu Utama</div>
                    <a href="{{ $hasProject ? route('dashboard') : route('dashboard.waiting') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('dashboard*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                        <i class="ti ti-layout-dashboard text-xl mr-3"></i>
                        Dashboard
                    </a>

                    @if($hasProject)
                        <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3">Pekerjaan</div>

                        <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('reports.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                            <i class="ti ti-file-text text-xl mr-3"></i>
                            Laporan Mingguan
                        </a>

                        @if($canManageMembers)
                            <a href="{{ route('reviews.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('reviews.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                                <i class="ti ti-clipboard-check text-xl mr-3"></i>
                                Tugas Review
                            </a>
                        @else
                            <div class="mb-4"></div>
                        @endif

                        <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3">Project & Info</div>

                        <a href="{{ route('targets.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('targets.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                            <i class="ti ti-target text-xl mr-3"></i>
                            Target Project
                        </a>

                        <a href="{{ route('announcements.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->routeIs('announcements.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                            <i class="ti ti-megaphone text-xl mr-3"></i>
                            Pengumuman
                        </a>

                        @if($canManageMembers)
                            <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3 mt-2">Manajemen</div>
                            <a href="{{ url('/members') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-4 {{ request()->is('members*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                                <i class="ti ti-users text-xl mr-3"></i>
                                Kelola Anggota
                            </a>
                        @endif
                    @endif
                @endif

                <!-- Tambahan Menu Pengaturan & Logout (Berlaku untuk Keduanya) -->
                <div class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2 px-3 {{ !$hasProject && !$user->is_admin ? 'mt-4' : '' }}">Pengaturan</div>

                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 rounded-xl transition-all mb-1 {{ request()->routeIs('profile.*') ? 'bg-accent text-text-primary font-bold shadow-sm' : 'text-text-secondary hover:bg-neutral-bg hover:text-text-primary font-medium' }}">
                    <i class="ti ti-user-circle text-xl mr-3"></i>
                    Kelola Profil
                </a>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Keluar Akun -->
                <div class="pt-4 mt-6 border-t border-neutral-border">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-semantic-danger font-bold rounded-xl hover:bg-semantic-dangerBg transition-colors">
                            <i class="ti ti-logout text-xl mr-3"></i>
                            Keluar Akun
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- AREA KONTEN KANAN -->
        <div class="flex-1 flex flex-col w-full h-full relative">

            <!-- NAVBAR TOP -->
            <header class="h-16 bg-primary border-b border-primary-dark flex items-center justify-between px-6 shrink-0 z-10 shadow-sm">
                <div class="flex items-center text-white/80">
                    <i class="ti ti-sparkles text-xl mr-2 text-accent"></i>
                    <span class="text-sm font-medium">Selamat bekerja!</span>
                </div>

                <!-- Area Kanan: User Info -->
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 group hover:bg-white/10 p-1.5 pl-3 rounded-full transition-colors cursor-pointer" title="Kelola Profil Anda">
                    <span class="text-sm font-bold text-white group-hover:text-white/90">{{ Auth::user()->name }}</span>
                    <div class="w-9 h-9 rounded-full bg-accent text-text-primary flex items-center justify-center font-extrabold text-sm shadow-sm ring-2 ring-white/20 group-hover:scale-105 transition-transform">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </a>
            </header>

            <!-- KONTEN HALAMAN UTAMA -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-8 w-full bg-neutral-bg">
                <div class="max-w-6xl mx-auto">
                    <!-- Flash Messages Umum -->
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-xl bg-semantic-successBg text-semantic-success border border-semantic-success/20 font-medium shadow-sm flex items-center">
                            <i class="ti ti-circle-check text-xl mr-2 shrink-0"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-xl bg-semantic-dangerBg text-semantic-danger border border-semantic-danger/20 font-medium shadow-sm flex items-center">
                            <i class="ti ti-alert-triangle text-xl mr-2 shrink-0"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>