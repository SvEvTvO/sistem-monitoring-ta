<x-app-layout>

    @php
        $now      = \Carbon\Carbon::now();
        $deadline = \Carbon\Carbon::parse($target->deadline)->endOfDay();
        $start    = \Carbon\Carbon::parse($target->start_date)->startOfDay();

        $isCompleted = !is_null($target->completed_at);
        $isUpcoming  = $start->isFuture();
        $daysLeft    = $now->diffInDays($deadline, false); // negatif = sudah lewat

        /* ---------- Angka pendukung ---------- */
        $totalDuration = max(1, (int) abs($start->diffInDays($deadline, false)));
        $daysTaken     = $isCompleted ? max(1, (int) abs($start->diffInDays(\Carbon\Carbon::parse($target->completed_at), false))) : 0;
        $daysToStart   = max(1, (int) ceil(abs($now->diffInDays($start, false))));
        $overdueDays   = max(1, (int) abs(ceil($daysLeft)));
        $hoursLeft     = max(1, (int) ceil(abs($deadline->diffInHours($now, false))));
        $elapsedDays   = max(0, (int) floor($start->diffInDays($now, false)));

        /* ---------- Tema warna (murni turunan 5 root color) ---------- */
        if ($isCompleted) {
            $completedAt = \Carbon\Carbon::parse($target->completed_at);
            $elapsedDays = $daysTaken;
            $theme = [
                'icon'        => 'ti-circle-check',
                'statusText'  => 'Target Selesai',
                'dot'         => 'bg-[#D9FF3A]',
                'panel'       => 'bg-[#D9FF3A] text-[#0B0F14] shadow-[0_20px_45px_-18px_rgba(217,255,58,0.65)]',
                'panelMuted'  => 'text-[#0B0F14]/60',
                'panelDivide' => 'border-[#0B0F14]/20',
                'panelIcon'   => 'bg-[#0B0F14] text-[#D9FF3A]',
                'big'         => (string) $daysTaken,
                'bigLabel'    => 'hari total pengerjaan',
                'panelNote'   => 'Batas tenggat: ' . $deadline->format('d M Y'),
                'alertBox'    => 'bg-[#D9FF3A]/20 border border-[#D9FF3A]/70',
                'alertChip'   => 'bg-[#0B0F14] text-[#D9FF3A]',
                'alertTitle'  => 'text-[#0B0F14]',
                'alertSub'    => 'text-[#0B0F14]/70',
                'msg'         => 'Kerja bagus! Target ini telah berhasil diselesaikan.',
                'subMsg'      => 'Ditutup pada ' . $completedAt->format('d M Y, H:i') . '.',
            ];
        } elseif ($isUpcoming) {
            $theme = [
                'icon'        => 'ti-calendar-time',
                'statusText'  => 'Belum Dimulai',
                'dot'         => 'bg-[#0B0F14]/30',
                'panel'       => 'bg-white border border-[#0B0F14]/10 text-[#0B0F14] shadow-[0_4px_24px_rgba(11,15,20,0.04)]',
                'panelMuted'  => 'text-[#0B0F14]/50',
                'panelDivide' => 'border-[#0B0F14]/10',
                'panelIcon'   => 'bg-[#F7F8FA] border border-[#0B0F14]/10 text-[#0B0F14]/60',
                'big'         => (string) $daysToStart,
                'bigLabel'    => 'hari menuju tanggal mulai',
                'panelNote'   => 'Mulai pada ' . $start->format('d M Y'),
                'alertBox'    => 'bg-white border border-[#0B0F14]/10',
                'alertChip'   => 'bg-[#F7F8FA] border border-[#0B0F14]/10 text-[#0B0F14]/60',
                'alertTitle'  => 'text-[#0B0F14]',
                'alertSub'    => 'text-[#0B0F14]/60',
                'msg'         => 'Target ini belum memasuki masa pengerjaan.',
                'subMsg'      => 'Dijadwalkan mulai dalam ' . $start->diffForHumans() . '.',
            ];
        } elseif ($daysLeft < 0) {
            $theme = [
                'icon'        => 'ti-alert-circle',
                'statusText'  => 'Terlewat (Overdue)',
                'dot'         => 'bg-[#0B0F14] animate-pulse',
                'panel'       => 'bg-[#0B0F14] text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)]',
                'panelMuted'  => 'text-white/60',
                'panelDivide' => 'border-white/15',
                'panelIcon'   => 'bg-[#D9FF3A] text-[#0B0F14]',
                'big'         => (string) $overdueDays,
                'bigLabel'    => 'hari melewati batas tenggat',
                'panelNote'   => 'Batas tenggat: ' . $deadline->format('d M Y'),
                'alertBox'    => 'bg-[#0B0F14] shadow-[0_16px_40px_-18px_rgba(11,15,20,0.55)]',
                'alertChip'   => 'bg-[#D9FF3A] text-[#0B0F14]',
                'alertTitle'  => 'text-white',
                'alertSub'    => 'text-white/70',
                'msg'         => 'Perhatian! Target ini telah melewati batas waktu.',
                'subMsg'      => 'Terlewat ' . $overdueDays . ' hari — tenggat berakhir ' . $deadline->format('d M Y') . '.',
            ];
        } elseif ($daysLeft < 1) {
            $theme = [
                'icon'        => 'ti-clock-exclamation',
                'statusText'  => 'Deadline Hari Ini',
                'dot'         => 'bg-[#D9FF3A] animate-pulse',
                'panel'       => 'bg-[#0245EC] text-white shadow-[0_20px_45px_-18px_rgba(2,69,236,0.6)]',
                'panelMuted'  => 'text-white/70',
                'panelDivide' => 'border-white/20',
                'panelIcon'   => 'bg-[#D9FF3A] text-[#0B0F14]',
                'big'         => (string) $hoursLeft,
                'bigLabel'    => 'jam tersisa hingga tenggat',
                'panelNote'   => 'Batas tenggat: ' . $deadline->format('d M Y'),
                'alertBox'    => 'bg-[#D9FF3A] border border-[#0B0F14]/10',
                'alertChip'   => 'bg-[#0B0F14] text-[#D9FF3A]',
                'alertTitle'  => 'text-[#0B0F14]',
                'alertSub'    => 'text-[#0B0F14]/70',
                'msg'         => 'Segera selesaikan, tenggat waktu berakhir hari ini!',
                'subMsg'      => 'Sisa waktu pengerjaan kurang dari 24 jam.',
            ];
        } else {
            $theme = [
                'icon'        => 'ti-clock-play',
                'statusText'  => 'Sedang Berjalan',
                'dot'         => 'bg-[#0245EC]',
                'panel'       => 'bg-[#0245EC]/5 border border-[#0245EC]/20 text-[#0245EC]',
                'panelMuted'  => 'text-[#0245EC]/70',
                'panelDivide' => 'border-[#0245EC]/20',
                'panelIcon'   => 'bg-[#0245EC] text-white',
                'big'         => (string) (int) ceil($daysLeft),
                'bigLabel'    => 'hari tersisa menuju tenggat',
                'panelNote'   => 'Batas tenggat: ' . $deadline->format('d M Y'),
                'alertBox'    => 'bg-[#0245EC]/5 border border-[#0245EC]/20',
                'alertChip'   => 'bg-[#0245EC] text-white',
                'alertTitle'  => 'text-[#0245EC]',
                'alertSub'    => 'text-[#0245EC]/70',
                'msg'         => 'Pengerjaan target sedang berlangsung secara aktif.',
                'subMsg'      => 'Tersisa ' . (int) ceil($daysLeft) . ' hari lagi sebelum tenggat waktu berakhir.',
            ];
        }

        /* ---------- Progress waktu (0 - 100) ---------- */
        if ($isCompleted || $now->gt($deadline)) {
            $timeProgress = 100;
        } elseif ($isUpcoming) {
            $timeProgress = 0;
        } else {
            $elapsedRaw   = $start->diffInDays($now, false);
            $timeProgress = min(100, max(0, (int) round(($elapsedRaw / $totalDuration) * 100)));
        }

        /* ---------- Caption kecil di bawah progress bar ---------- */
        if ($isCompleted) {
            $barCaption = 'Target ditutup — durasi penuh terpakai';
        } elseif ($isUpcoming) {
            $barCaption = 'Menunggu tanggal mulai';
        } elseif ($daysLeft < 0) {
            $barCaption = 'Melewati tenggat ' . $overdueDays . ' hari';
        } elseif ($daysLeft < 1) {
            $barCaption = 'Tenggat berakhir hari ini';
        } else {
            $barCaption = 'Sisa ' . (int) ceil($daysLeft) . ' hari menuju tenggat';
        }

        /* ---------- Rekap waktu (sidebar) ---------- */
        if ($isCompleted) {
            $recapLast = ['ti-circle-check', 'Total Pengerjaan', $daysTaken . ' hari'];
        } elseif ($isUpcoming) {
            $recapLast = ['ti-calendar-time', 'Menuju Mulai', $daysToStart . ' hari'];
        } elseif ($daysLeft < 0) {
            $recapLast = ['ti-alert-circle', 'Hari Terlewat', $overdueDays . ' hari'];
        } elseif ($daysLeft < 1) {
            $recapLast = ['ti-clock-exclamation', 'Sisa Waktu', '< 1 hari'];
        } else {
            $recapLast = ['ti-clock-play', 'Sisa Waktu', (int) ceil($daysLeft) . ' hari'];
        }

        $recap = [
            ['ti-rocket', 'Tanggal Mulai', $start->format('d M Y')],
            ['ti-flag', 'Batas Tenggat', $deadline->format('d M Y')],
            ['ti-calendar', 'Durasi Total', $totalDuration . ' hari'],
            ['ti-hourglass', 'Waktu Berjalan', $elapsedDays . ' hari'],
            $recapLast,
        ];

        $scopeIcon = $target->division_id ? 'ti-users-group' : 'ti-building';
        $scopeText = $target->division_id ? 'Target Divisi Khusus' : 'Target Global Project';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <a href="{{ route('targets.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                    <i class="ti ti-arrow-left text-lg"></i>
                </span>
                Kembali ke Timeline
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Detail Target</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full {{ $theme['dot'] }}"></span>
                    {{ $theme['statusText'] }}
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Pantau progres waktu dan informasi terkait target ini.</p>
        </div>

        @if($canEdit)
            <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                
                <!-- TOMBOL HAPUS (Hanya Muncul Jika Target Belum Dimulai) -->
                @if($isUpcoming)
                    <form action="{{ route('targets.destroy', $target->id) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-semantic-danger/30 bg-semantic-dangerBg px-6 py-3 font-extrabold text-semantic-danger shadow-sm transition-colors hover:bg-semantic-danger hover:text-white sm:w-auto">
                            <i class="ti ti-trash text-xl"></i> Hapus Target
                        </button>
                    </form>
                @endif

                @if(!$isCompleted)
                    <form action="{{ route('targets.complete', $target) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Tandai target ini sebagai selesai?')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                            <i class="ti ti-check text-xl"></i> Tandai Selesai
                        </button>
                    </form>
                @endif
                <a href="{{ route('targets.edit', $target->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/50 hover:text-[#0245EC] sm:w-auto">
                    <i class="ti ti-edit text-xl"></i> Edit Target
                </a>
            </div>
        @endif
    </header>

    <!-- ===================== GRID UTAMA ===================== -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-start lg:gap-7">

        <!-- ============ KOLOM UTAMA ============ -->
        <div class="space-y-6 lg:col-span-2">

            <!-- ---------- HERO PANEL ---------- -->
            <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
                <!-- Dekorasi -->
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute right-48 top-10 hidden h-40 w-40 rounded-full border border-[#D9FF3A]/25 sm:block"></div>
                <div class="pointer-events-none absolute bottom-14 left-1/3 hidden h-2 w-2 rounded-full bg-[#D9FF3A]/60 sm:block"></div>

                <div class="relative p-6 sm:p-8 lg:p-10">
                    <!-- Badges -->
                    <div class="mb-5 flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                            <i class="ti {{ $theme['icon'] }} text-sm"></i> {{ $theme['statusText'] }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                            <i class="ti {{ $scopeIcon }} text-sm {{ $target->division_id ? 'text-[#D9FF3A]' : '' }}"></i> {{ $scopeText }}
                        </span>
                    </div>

                    <!-- Judul -->
                    <h2 class="text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2.5rem] {{ $isCompleted ? 'line-through decoration-[#D9FF3A] decoration-[3px]' : '' }}">
                        {{ $target->title }}
                    </h2>

                    <!-- Timeline -->
                    <div class="mt-9">
                        <div class="mb-3.5 grid grid-cols-[1fr_auto_1fr] items-end gap-2 sm:gap-4">
                            <div class="min-w-0">
                                <p class="mb-1 flex items-center text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">
                                    <i class="ti ti-rocket mr-1"></i> Mulai
                                </p>
                                <p class="truncate text-sm font-bold text-white sm:text-base">{{ $start->format('d M Y') }}</p>
                            </div>

                            <div class="pb-0.5">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-white/15 bg-[#0B0F14]/40 px-3.5 py-1.5 text-[11px] font-extrabold text-white backdrop-blur-sm">
                                    <i class="ti ti-hourglass text-xs"></i> {{ $timeProgress }}% Berlalu
                                </span>
                            </div>

                            <div class="min-w-0 text-right">
                                <p class="mb-1 flex items-center justify-end text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">
                                    <i class="ti ti-flag mr-1"></i> Tenggat
                                </p>
                                <p class="truncate text-sm font-bold sm:text-base {{ (!$isCompleted && !$isUpcoming && $daysLeft < 1) ? 'text-[#D9FF3A]' : 'text-white' }}">{{ $deadline->format('d M Y') }}</p>
                            </div>
                        </div>

                        <!-- Track + fill aksen -->
                        <div class="relative">
                            <div class="h-3 overflow-hidden rounded-full bg-white/20 sm:h-3.5">
                                <div class="relative h-full overflow-hidden rounded-full bg-[#D9FF3A] transition-all duration-1000 ease-out" style="width: {{ $timeProgress }}%">
                                    <div class="absolute inset-0 opacity-15" style="background-image: linear-gradient(45deg, #0B0F14 25%, transparent 25%, transparent 50%, #0B0F14 50%, #0B0F14 75%, transparent 75%, transparent); background-size: 14px 14px;"></div>
                                </div>
                            </div>
                            @if(!$isCompleted && !$isUpcoming && !$now->gt($deadline))
                                <div class="pointer-events-none absolute top-1/2 h-[22px] w-[6px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white shadow ring-[3px] ring-[#0245EC] sm:h-[26px]" style="left: {{ $timeProgress }}%" title="Posisi hari ini"></div>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-[11px] font-semibold text-white/50">Durasi {{ $totalDuration }} hari</p>
                            <p class="text-[11px] font-semibold text-white/50">{{ $barCaption }}</p>
                        </div>
                    </div>

                    <!-- ================= DESKRIPSI TAMBAHAN ================= -->
                    @if($target->description)
                        <div class="mt-7 rounded-2xl bg-[#0B0F14]/20 p-5 border border-white/10 backdrop-blur-sm shadow-inner">
                            <div class="mb-2.5 flex items-center gap-2">
                                <i class="ti ti-align-left text-white/50"></i>
                                <h4 class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/50">Catatan / Detail Tambahan</h4>
                            </div>
                            <!-- whitespace-pre-wrap agar enter/baris baru dari database tetap terbaca -->
                            <p class="text-sm font-medium leading-relaxed text-white/90 whitespace-pre-wrap">{{ $target->description }}</p>
                        </div>
                    @endif
                    <!-- ====================================================== -->
                </div>
            </section>

            <!-- ---------- PESAN STATUS ---------- -->
            <section class="flex items-start gap-4 rounded-[24px] px-5 py-4 sm:items-center sm:px-6 sm:py-5 {{ $theme['alertBox'] }}">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $theme['alertChip'] }}">
                    <i class="ti {{ $theme['icon'] }} text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-extrabold leading-snug {{ $theme['alertTitle'] }}">{{ $theme['msg'] }}</h3>
                    <p class="mt-0.5 text-sm font-semibold {{ $theme['alertSub'] }}">{{ $theme['subMsg'] }}</p>
                </div>
            </section>

            <!-- ---------- INFORMASI TAMBAHAN ---------- -->
            <section>
                <div class="mb-4 flex items-center gap-2.5">
                    <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Informasi Tambahan</h3>
                </div>

                <div class="overflow-hidden rounded-[24px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
                    <div class="grid grid-cols-1 sm:grid-cols-2">
                        <!-- Cakupan -->
                        <div class="border-b border-[#0B0F14]/10 p-5 transition-colors hover:bg-[#F7F8FA] sm:p-6">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $target->division_id ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'bg-[#0245EC]/10 text-[#0245EC]' }}">
                                    <i class="ti {{ $scopeIcon }} text-lg"></i>
                                </span>
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Cakupan</p>
                            </div>
                            <p class="truncate text-lg font-extrabold text-[#0B0F14]" title="{{ $target->division_id ? $target->division->name : 'Global Project' }}">
                                {{ $target->division_id ? $target->division->name : 'Global Project' }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div class="border-b border-[#0B0F14]/10 p-5 transition-colors hover:bg-[#F7F8FA] sm:border-l sm:p-6">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $isCompleted ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'bg-[#0245EC]/10 text-[#0245EC]' }}">
                                    <i class="ti {{ $isCompleted ? 'ti-check' : 'ti-loader' }} text-lg"></i>
                                </span>
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Status</p>
                            </div>
                            <p class="text-lg font-extrabold text-[#0B0F14]">{{ $isCompleted ? 'Ditutup' : 'Aktif' }}</p>
                        </div>

                        <!-- Dibuat Oleh -->
                        <div class="border-b border-[#0B0F14]/10 p-5 transition-colors hover:bg-[#F7F8FA] sm:border-b-0 sm:p-6">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#0245EC] to-[#0245EC]/60 text-sm font-black text-white">
                                    {{ strtoupper(substr($target->creator->name ?? 'S', 0, 1)) }}
                                </span>
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Dibuat Oleh</p>
                            </div>
                            <p class="truncate text-lg font-extrabold text-[#0B0F14]" title="{{ $target->creator->name ?? 'Sistem' }}">
                                {{ explode(' ', $target->creator->name ?? 'Sistem')[0] }}
                            </p>
                        </div>

                        <!-- Tgl Terdaftar -->
                        <div class="p-5 transition-colors hover:bg-[#F7F8FA] sm:border-l sm:p-6">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                                    <i class="ti ti-calendar-plus text-lg"></i>
                                </span>
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Tgl Terdaftar</p>
                            </div>
                            <p class="text-lg font-extrabold text-[#0B0F14]">{{ $target->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ============ SIDEBAR ============ -->
        <aside class="space-y-6 lg:sticky lg:top-6">

            <!-- Panel Status (big number) -->
            <section class="relative overflow-hidden rounded-[28px] p-6 sm:p-7 {{ $theme['panel'] }}">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $theme['panelIcon'] }}">
                        <i class="ti {{ $theme['icon'] }} text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] {{ $theme['panelMuted'] }}">Status Target</p>
                        <p class="truncate text-lg font-extrabold leading-tight">{{ $theme['statusText'] }}</p>
                    </div>
                </div>

                <div class="mt-7">
                    <p class="text-6xl font-black leading-none tracking-tight">{{ $theme['big'] }}</p>
                    <p class="mt-2 text-sm font-bold {{ $theme['panelMuted'] }}">{{ $theme['bigLabel'] }}</p>
                </div>

                <p class="mt-5 border-t pt-4 text-xs font-semibold {{ $theme['panelDivide'] }} {{ $theme['panelMuted'] }}">
                    {{ $theme['panelNote'] }}
                </p>
            </section>

            <!-- Rekap Waktu -->
            <section class="rounded-[28px] border border-[#0B0F14]/10 bg-white p-6 shadow-[0_4px_24px_rgba(11,15,20,0.04)] sm:p-7">
                <div class="mb-2 flex items-center gap-2.5">
                    <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Rekap Waktu</h3>
                </div>
                <ul class="divide-y divide-[#0B0F14]/10">
                    @foreach($recap as [$recapIcon, $recapLabel, $recapValue])
                        <li class="flex items-center gap-3 py-3.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                                <i class="ti {{ $recapIcon }} text-lg"></i>
                            </span>
                            <span class="flex-1 text-sm font-semibold text-[#0B0F14]/55">{{ $recapLabel }}</span>
                            <span class="text-sm font-extrabold text-[#0B0F14]">{{ $recapValue }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
</x-app-layout>