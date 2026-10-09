<x-app-layout>

    @php
        /* ---------- Setup Data User ---------- */
        $userName  = $user->name;
        $initial   = strtoupper(substr($userName, 0, 1));
        
        $classMembership = $user->classMemberships->first();
        $className = $classMembership ? $classMembership->schoolClass->name : 'Unassigned';
        $joinedAt  = $classMembership ? \Carbon\Carbon::parse($classMembership->joined_at)->translatedFormat('d M Y') : null;

        $pm = $user->projectMembers->first();
        $isLeader = $pm ? ($pm->division->leader_user_id == $user->id) : false;
        $isProjectLeaderAccount = $pm ? ($pm->project->project_leader_id == $user->id) : false;
        $divisionName = $pm ? $pm->division->name : 'Belum Ada Divisi';
        $projectName = $pm ? $pm->project->name : 'Belum Ada Project';

        /* ---------- Rekap status laporan ---------- */
        $approvedCount = 0;
        $revisionCount = 0;
        $pendingCount  = 0;
        foreach ($reports as $r) {
            $s = strtoupper((string) ($r->status ?? ''));
            if ($s === 'APPROVED')           $approvedCount++;
            elseif (str_contains($s, 'REV')) $revisionCount++;
            else                             $pendingCount++;
        }
        $totalReports = $reports->count();

        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7">
        <a href="{{ route('admin.users.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                <i class="ti ti-arrow-left text-lg"></i>
            </span>
            Kembali ke Manajemen Pengguna
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Detail Akun Pengguna</h1>

            @if($user->is_admin)
                <span class="inline-flex items-center gap-2 rounded-full border border-semantic-warning/20 bg-semantic-warningBg px-3.5 py-1.5 text-xs font-extrabold text-semantic-warning shadow-sm">
                    <i class="ti ti-shield-lock"></i> Administrator
                </span>
            @endif
        </div>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Profil, penempatan rombel, dan riwayat laporan dari pengguna ini.</p>
    </header>

    <!-- ===================== HERO: PROFIL ANGGOTA ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-user-circle pointer-events-none absolute -bottom-10 -right-4 text-[10rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col items-center gap-7 text-center sm:flex-row sm:items-start sm:text-left lg:items-center">

                <!-- Avatar -->
                <div class="relative shrink-0">
                    <span class="flex h-28 w-28 items-center justify-center rounded-[26px] text-5xl font-black shadow-lg {{ $isLeader || $isProjectLeaderAccount ? 'bg-[#D9FF3A] text-[#0B0F14] shadow-[0_16px_36px_-14px_rgba(217,255,58,0.9)]' : 'bg-white text-[#0245EC]' }}">
                        {{ $initial }}
                    </span>
                    @if($isLeader || $isProjectLeaderAccount)
                        <span class="absolute -bottom-3 -right-3 flex h-10 w-10 rotate-12 items-center justify-center rounded-xl bg-[#0B0F14] text-[#D9FF3A] shadow-lg">
                            <i class="ti ti-star-filled text-xl"></i>
                        </span>
                    @endif
                </div>

                <!-- Identitas -->
                <div class="min-w-0 flex-1">
                    <div class="mb-3 flex flex-wrap items-center justify-center gap-2.5 sm:justify-start">
                        <!-- Rombel -->
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                            <i class="ti ti-chalkboard text-sm text-[#D9FF3A]"></i>
                            Rombel {{ $className }}
                        </span>

                        <!-- Lencana Utama: Ketua Project -->
                        @if($isProjectLeaderAccount)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0B0F14] border border-white/20 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wide text-white shadow-sm">
                                <i class="ti ti-crown text-sm text-[#D9FF3A]"></i> Ketua Project
                            </span>
                        @endif

                        <!-- Lencana Peran Divisi -->
                        @if($isLeader)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wide text-[#0B0F14] shadow-sm">
                                <i class="ti ti-star-filled text-sm"></i> Ketua Divisi
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2.25rem]">{{ $userName }}</h2>

                    <p class="mt-2.5 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs font-semibold text-white/60 sm:justify-start">
                        <span class="flex items-center"><i class="ti ti-at mr-1.5"></i> {{ $user->username }}</span>
                        <span class="flex items-center"><i class="ti ti-mail mr-1.5"></i> {{ $user->email }}</span>
                    </p>
                </div>
            </div>

            <!-- Strip statistik -->
            <div class="mt-8 grid grid-cols-2 gap-4 rounded-[20px] border border-white/15 bg-white/10 p-5 backdrop-blur-sm lg:grid-cols-4">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Project</p>
                    <p class="mt-1 truncate text-sm font-extrabold text-white">{{ $projectName }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Divisi</p>
                    <p class="mt-1 text-sm font-extrabold text-white">{{ $divisionName }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Total Laporan</p>
                    <p class="mt-1 text-sm font-extrabold text-white">{{ $totalReports }} Laporan</p>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Disetujui</p>
                    <p class="mt-1 text-sm font-extrabold text-white">{{ $approvedCount }} <span class="font-semibold text-white/50">dari {{ $totalReports }}</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== RIWAYAT LAPORAN ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC]/10 text-[#0245EC]">
                    <i class="ti ti-file-text text-lg"></i>
                </span>
                <h3 class="{{ $sectionLbl }}">Riwayat Laporan Pengguna</h3>
            </div>

            @if($totalReports > 0)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3 py-1 text-[11px] font-extrabold text-[#0B0F14]">
                        <i class="ti ti-circle-check text-xs"></i> {{ $approvedCount }} Disetujui
                    </span>
                    @if($revisionCount > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0B0F14] px-3 py-1 text-[11px] font-extrabold text-white">
                            <i class="ti ti-refresh text-xs text-[#D9FF3A]"></i> {{ $revisionCount }} Revisi
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($totalReports > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] border-collapse text-left">
                    <thead>
                        <tr class="border-y border-[#0B0F14]/10 bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                            <th class="px-6 sm:px-8 py-5">Tanggal & Waktu</th>
                            <th class="px-6 sm:px-8 py-5">Tipe Laporan</th>
                            <th class="px-6 sm:px-8 py-5">Judul Laporan</th>
                            <th class="px-6 sm:px-8 py-5">Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0B0F14]/10">
                        @foreach($reports as $report)
                            @php
                                $s = strtoupper((string) ($report->status ?? ''));
                                $rApproved   = $s === 'APPROVED';
                                $rRevision   = str_contains($s, 'REV');
                                $reportTitle = $report->title ?? 'Laporan Mingguan';
                            @endphp
                            <tr class="transition-colors hover:bg-[#F7F8FA]/50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="text-sm font-extrabold text-[#0B0F14]">Minggu ke-{{ $report->week_number ?? '-' }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-[#0B0F14]/45">{{ \Carbon\Carbon::parse($report->created_at)->translatedFormat('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($report->type === 'DIVISION')
                                        <span class="inline-flex rounded-lg border border-[#0245EC]/20 bg-[#0245EC]/10 px-2.5 py-1 text-[10px] font-extrabold text-[#0245EC] tracking-wider uppercase">Laporan Divisi</span>
                                    @else
                                        <span class="inline-flex rounded-lg border border-[#0B0F14]/10 bg-[#F7F8FA] px-2.5 py-1 text-[10px] font-extrabold text-[#0B0F14]/70 tracking-wider uppercase">Personal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="block max-w-xs truncate text-sm font-bold text-[#0B0F14]">
                                        {{ $reportTitle }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($rApproved)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#0B0F14]">
                                            <i class="ti ti-check"></i> Disetujui
                                        </span>
                                    @elseif($rRevision)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0B0F14] px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                            <i class="ti ti-refresh text-[#D9FF3A]"></i> Revisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0245EC]/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#0245EC]">
                                            <i class="ti ti-clock"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA]">
                    <i class="ti ti-file-x text-4xl text-[#0B0F14]/30"></i>
                </span>
                <h4 class="text-lg font-black text-[#0B0F14]">Belum Ada Laporan</h4>
                <p class="mt-1.5 max-w-md text-sm font-medium text-[#0B0F14]/50">
                    Pengguna ini belum mengirimkan laporan mingguan apa pun.
                </p>
            </div>
        @endif
    </section>

</x-app-layout>