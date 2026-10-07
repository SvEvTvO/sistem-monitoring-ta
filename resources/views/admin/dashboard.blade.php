<x-app-layout>

    @php
        /* ---------- Setup UI ---------- */
        $hour     = \Carbon\Carbon::now()->hour;
        $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));

        $adminName  = auth()->user()->name ?? 'Admin';
        $adminFirst = explode(' ', $adminName)[0];

        /* ---------- Statistik (dengan fallback aman) ---------- */
        $totalUsers       = (int) ($stats['total_users'] ?? 0);
        $totalProjects    = (int) ($stats['total_projects'] ?? 0);
        $activeProjects   = (int) ($stats['active_projects'] ?? 0);
        $totalClasses     = (int) ($stats['total_classes'] ?? 0);
        $totalDepartments = (int) ($stats['total_departments'] ?? 0);

        /* Turunan: rasio project aktif (murni aritmetika, tanpa query baru) */
        $inactiveProjects = max(0, $totalProjects - $activeProjects);
        $activePercentage = $totalProjects > 0
            ? min(100, max(0, (int) round(($activeProjects / $totalProjects) * 100)))
            : 0;

        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    {{-- ============================================================
         TODO: arahkan href di bawah ke route admin yang tersedia, contoh:
         - "Tambah User"      → route('admin.users.create')
         - "Inisiasi Project" → route('admin.projects.create')
         - "Lihat Semua"      → route('admin.projects.index')
    ============================================================ --}}

    <!-- ===================== HERO: DASHBOARD ADMIN ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-shield-lock pointer-events-none absolute -bottom-10 -right-4 text-[10rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <p class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-[#D9FF3A]">Administrator Panel</p>
            <h1 class="mt-2 text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">Dashboard Utama</h1>
            <p class="mt-2 max-w-2xl text-sm font-semibold text-white/70">
                {{ $greeting }}, {{ $adminFirst }}! Ringkasan aktivitas dan data master Sistem Monitoring TA.
            </p>

            <!-- Strip bawah: progress project aktif + aksi cepat -->
            <div class="mt-9 flex flex-col gap-7 border-t border-white/15 pt-6 lg:flex-row lg:items-end lg:justify-between">
                <!-- Progress Project Aktif -->
                <div class="w-full max-w-md">
                    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">Project Aktif</p>
                            <p class="mt-1 text-sm font-semibold text-white/70">{{ $activeProjects }} dari {{ $totalProjects }} project sedang berjalan</p>
                        </div>
                        <p class="text-3xl font-black leading-none text-white">{{ $activePercentage }}<span class="text-lg text-[#D9FF3A]">%</span></p>
                    </div>
                    <div class="h-3.5 overflow-hidden rounded-full bg-white/20">
                        <div class="relative h-full overflow-hidden rounded-full bg-[#D9FF3A] transition-all duration-1000 ease-out" style="width: {{ $activePercentage }}%">
                            <div class="absolute inset-0 opacity-15" style="background-image: linear-gradient(45deg, #0B0F14 25%, transparent 25%, transparent 50%, #0B0F14 50%, #0B0F14 75%, transparent 75%, transparent); background-size: 14px 14px;"></div>
                        </div>
                    </div>
                    @if($inactiveProjects > 0)
                        <p class="mt-2.5 text-[11px] font-semibold text-white/45">
                            <i class="ti ti-flag-pause mr-1"></i> {{ $inactiveProjects }} project tidak aktif
                        </p>
                    @endif
                </div>

                <!-- Aksi Cepat -->
                <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                    <a href="#" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-6 py-3 text-sm font-extrabold text-white backdrop-blur-sm transition-colors hover:bg-white/20 sm:w-auto">
                        <i class="ti ti-user-plus text-lg"></i> Tambah User
                    </a>
                    <a href="#" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                        <i class="ti ti-folder-plus text-lg"></i> Inisiasi Project
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== STATISTIK BENTO ===================== -->
    <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">

        <!-- Total Pengguna -->
        <div class="flex flex-col justify-between gap-5 rounded-[24px] border border-[#0B0F14]/10 bg-white p-6 shadow-[0_4px_24px_rgba(11,15,20,0.04)] transition-shadow hover:shadow-[0_12px_32px_rgba(11,15,20,0.08)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0245EC]/10 text-[#0245EC]">
                <i class="ti ti-users text-xl"></i>
            </span>
            <div>
                <p class="text-4xl font-black leading-none text-[#0B0F14] md:text-5xl">{{ $totalUsers }}</p>
                <p class="mt-2 text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Total Pengguna Aktif</p>
            </div>
        </div>

        <!-- Total Project (highlight hitam) -->
        <div class="relative flex flex-col justify-between gap-5 overflow-hidden rounded-[24px] bg-[#0B0F14] p-6 text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)] transition-all hover:-translate-y-1">
            <div class="pointer-events-none absolute -right-14 -top-14 h-40 w-40 rounded-full bg-[#D9FF3A]/15 blur-3xl"></div>
            <i class="ti ti-rocket pointer-events-none absolute -bottom-5 -right-4 text-[6rem] leading-none text-white/[0.05]"></i>

            <div class="relative flex items-center justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14]">
                    <i class="ti ti-rocket text-xl"></i>
                </span>
                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-[#D9FF3A]/30 bg-[#D9FF3A]/10 px-2.5 py-1 text-[10px] font-bold text-[#D9FF3A]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#D9FF3A] {{ $activeProjects > 0 ? 'animate-pulse' : '' }}"></span> {{ $activeProjects }} Aktif
                </span>
            </div>
            <div class="relative">
                <p class="text-4xl font-black leading-none md:text-5xl {{ $activeProjects > 0 ? 'text-[#D9FF3A]' : 'text-white' }}">{{ $totalProjects }}</p>
                <p class="mt-2 text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Total Project Terdaftar</p>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="flex flex-col justify-between gap-5 rounded-[24px] border border-[#0B0F14]/10 bg-white p-6 shadow-[0_4px_24px_rgba(11,15,20,0.04)] transition-shadow hover:shadow-[0_12px_32px_rgba(11,15,20,0.08)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                <i class="ti ti-chalkboard text-xl"></i>
            </span>
            <div>
                <p class="text-4xl font-black leading-none text-[#0B0F14] md:text-5xl">{{ $totalClasses }}</p>
                <p class="mt-2 text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Kelas Terdaftar</p>
            </div>
        </div>

        <!-- Total Jurusan -->
        <div class="flex flex-col justify-between gap-5 rounded-[24px] border border-[#0B0F14]/10 bg-white p-6 shadow-[0_4px_24px_rgba(11,15,20,0.04)] transition-shadow hover:shadow-[0_12px_32px_rgba(11,15,20,0.08)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                <i class="ti ti-books text-xl"></i>
            </span>
            <div>
                <p class="text-4xl font-black leading-none text-[#0B0F14] md:text-5xl">{{ $totalDepartments }}</p>
                <p class="mt-2 text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Jurusan Tersedia</p>
            </div>
        </div>
    </section>

    <!-- ===================== PROJECT TERBARU ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#0B0F14]/10 px-6 py-5 sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC]/10 text-[#0245EC]">
                    <i class="ti ti-rocket text-lg"></i>
                </span>
                <h3 class="{{ $sectionLbl }}">Project Terbaru Diinisiasi</h3>
            </div>
            <a href="#" class="group/btn inline-flex items-center gap-1.5 text-xs font-bold text-[#0245EC] hover:underline">
                Lihat Semua <i class="ti ti-arrow-right text-sm transition-transform duration-200 group-hover/btn:translate-x-0.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left">
                <thead>
                    <tr class="bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                        <th class="px-6 py-4">Nama Project</th>
                        <th class="px-6 py-4">Ketua Project</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">Tanggal Inisiasi</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#0B0F14]/10">
                    @forelse($recentProjects as $project)
                        @php
                            $pStatus = strtoupper((string) $project->status);
                            $isCompleted = in_array($pStatus, ['COMPLETED', 'FINISHED', 'ARCHIVED']);
                        @endphp
                        <tr class="transition-colors hover:bg-[#F7F8FA]/60">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $pStatus === 'ACTIVE' ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/50' }}">
                                        <i class="ti ti-rocket text-lg"></i>
                                    </span>
                                    <p class="text-sm font-bold text-[#0B0F14]">{{ $project->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-black {{ $project->leader ? 'bg-[#0B0F14] text-[#D9FF3A]' : 'border border-dashed border-[#0B0F14]/25 text-[#0B0F14]/30' }}">
                                        {{ $project->leader ? strtoupper(substr($project->leader->name, 0, 1)) : '?' }}
                                    </span>
                                    <span class="text-sm {{ $project->leader ? 'font-bold text-[#0B0F14]' : 'font-medium italic text-[#0B0F14]/40' }}">
                                        {{ $project->leader->name ?? 'Belum Ditunjuk' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($project->schoolClass)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#0B0F14]/10 bg-[#F7F8FA] px-3 py-1 text-xs font-bold text-[#0B0F14]/70">
                                        <i class="ti ti-school text-sm"></i> {{ $project->schoolClass->name }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-[#0B0F14]/35">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-bold text-[#0B0F14]">{{ $project->created_at->format('d M Y') }}</p>
                                <p class="mt-0.5 text-xs font-medium text-[#0B0F14]/45">{{ $project->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($pStatus === 'ACTIVE')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#0B0F14]">
                                        <i class="ti ti-point-filled"></i> Aktif
                                    </span>
                                @elseif($isCompleted)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0B0F14] px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                        <i class="ti ti-flag-check text-[#D9FF3A]"></i> {{ $pStatus === 'ARCHIVED' ? 'Arsip' : 'Selesai' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#0B0F14]/10 bg-[#0B0F14]/5 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#0B0F14]/50">
                                        <i class="ti ti-point"></i> {{ $project->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA]">
                                        <i class="ti ti-rocket-off text-4xl text-[#0B0F14]/30"></i>
                                    </span>
                                    <h4 class="text-lg font-black text-[#0B0F14]">Belum Ada Project</h4>
                                    <p class="mt-1.5 max-w-md text-sm font-medium text-[#0B0F14]/50">
                                        Belum ada project yang terdaftar di sistem. Mulai dengan menekan tombol
                                        <span class="font-bold text-[#0245EC]">Inisiasi Project</span> di atas.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
