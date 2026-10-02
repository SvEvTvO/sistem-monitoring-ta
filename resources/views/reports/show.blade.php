<x-app-layout>

    @php
        /* ---------- Status laporan ---------- */
        $statusConfig = [
            'SUBMITTED' => ['icon' => 'ti-send',          'label' => 'Menunggu Review', 'hero' => 'border-[#D9FF3A]/40 bg-[#D9FF3A]/15 text-[#D9FF3A]'],
            'REVIEWED'  => ['icon' => 'ti-eye',           'label' => 'Sedang Direview', 'hero' => 'border-white/25 bg-white/10 text-white'],
            'APPROVED'  => ['icon' => 'ti-circle-check',  'label' => 'Disetujui',       'hero' => 'border-transparent bg-[#D9FF3A] text-[#0B0F14]'],
            'REVISION_REQUIRED' => ['icon' => 'ti-alert-triangle', 'label' => 'Perlu Revisi', 'hero' => 'border-transparent bg-[#0B0F14] text-white'],
        ];
        $conf         = $statusConfig[$report->status] ?? ['icon' => 'ti-file', 'label' => $report->status, 'hero' => 'border-white/25 bg-white/10 text-white'];
        $isApproved   = $report->status === 'APPROVED';
        $isRevision   = $report->status === 'REVISION_REQUIRED';

        /* ---------- Tipe laporan ---------- */
        $typeMap = [
            'DIVISION' => ['ti-users-group', 'Laporan Divisi', true],
            'PERSONAL' => ['ti-user',        'Laporan Pribadi', false],
        ];
        [$typeIcon, $typeText, $typeIsDivision] = $typeMap[strtoupper($report->type)] ?? ['ti-file-text', strtoupper($report->type), false];

        $weekRange = $report->projectWeek->week_start->format('d M') . ' – ' . $report->projectWeek->week_end->format('d M Y');

        /* ---------- Catatan reviewer ---------- */
        $noteIsRevision = $isRevision;
        $noteIsApproved = $isApproved;
        $noteIcon = $noteIsRevision ? 'ti-alert-triangle' : ($noteIsApproved ? 'ti-circle-check' : 'ti-message-circle-2');

        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
        $contentTxt = 'whitespace-pre-line text-sm font-medium leading-relaxed text-[#0B0F14]/80';

        /* Format angka progres tanpa nol buntut */
        $progressValue = $report->progress_percentage !== null
            ? rtrim(rtrim(number_format((float) $report->progress_percentage, 2), '0'), '.')
            : null;
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <a href="{{ route('reports.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                    <i class="ti ti-arrow-left text-lg"></i>
                </span>
                Kembali ke Riwayat
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Detail Laporan</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full {{ $isRevision ? 'bg-[#0B0F14]' : ($isApproved ? 'bg-[#D9FF3A]' : 'bg-[#0245EC]') }}"></span>
                    {{ $conf['label'] }}
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Tinjau isi laporan dan tanggapan reviewer.</p>
        </div>

        <!-- Tombol edit: hanya pemilik & belum disetujui -->
        @if($report->author_id === auth()->id() && $report->status !== 'APPROVED')
            <a href="{{ route('reports.edit', $report->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-extrabold shadow-sm transition-all duration-200 sm:w-auto lg:shrink-0 {{ $isRevision ? 'bg-[#0B0F14] text-white hover:opacity-90' : 'border border-[#0B0F14]/10 bg-white text-[#0B0F14] hover:border-[#0245EC]/40 hover:text-[#0245EC]' }}">
                <i class="ti {{ $isRevision ? 'ti-refresh' : 'ti-edit' }} text-lg {{ $isRevision ? 'text-[#D9FF3A]' : '' }}"></i>
                {{ $isRevision ? 'Buat Ulang Laporan' : 'Edit Laporan' }}
            </a>
        @endif
    </header>

    <!-- ===================== HERO: LAPORAN ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-file-description pointer-events-none absolute -bottom-8 -right-4 text-[9rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <!-- Badge konteks -->
            <div class="mb-5 flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-bold backdrop-blur-sm {{ $conf['hero'] }}">
                    <i class="ti {{ $conf['icon'] }} text-sm"></i>
                    {{ $conf['label'] }}
                </span>
                <span class="inline-flex items-center gap-2.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#D9FF3A] text-[11px] font-black text-[#0B0F14]">{{ $report->projectWeek->week_number }}</span>
                    Minggu ke-{{ $report->projectWeek->week_number }} <span class="font-medium text-white/50">{{ $weekRange }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti {{ $typeIcon }} text-sm {{ $typeIsDivision ? 'text-[#D9FF3A]' : '' }}"></i>
                    {{ $typeText }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti ti-category text-sm"></i>
                    {{ $report->division->name }}
                </span>
            </div>

            <!-- Judul -->
            <h2 class="max-w-3xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2.25rem]">
                {{ $report->title }}
            </h2>

            <!-- Penulis -->
            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-white/15 pt-6">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#D9FF3A] text-base font-black text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]">
                    {{ strtoupper(substr($report->author->name, 0, 2)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-extrabold text-white">{{ $report->author->name }}</p>
                    <p class="mt-0.5 flex items-center text-xs font-semibold text-white/60">
                        <i class="ti ti-clock mr-1.5"></i>
                        Dikirim: {{ $report->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== CATATAN REVIEWER ===================== -->
    @if($report->review_comment)
        <section class="mb-6 rounded-[24px] p-6 sm:p-7 {{ $noteIsRevision
            ? 'bg-[#0B0F14] text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)]'
            : ($noteIsApproved
                ? 'border border-[#D9FF3A]/60 bg-[#D9FF3A]/15'
                : 'border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]') }}">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $noteIsRevision ? 'bg-[#D9FF3A] text-[#0B0F14]' : ($noteIsApproved ? 'bg-[#0B0F14] text-[#D9FF3A]' : 'border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60') }}">
                    <i class="ti {{ $noteIcon }} text-xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] {{ $noteIsRevision ? 'text-[#D9FF3A]' : 'text-[#0B0F14]/50' }}">Catatan Evaluasi Reviewer</p>
                    <p class="mt-1.5 text-sm font-medium italic leading-relaxed {{ $noteIsRevision ? 'text-white/90' : 'text-[#0B0F14]/80' }}">&ldquo;{{ $report->review_comment }}&rdquo;</p>
                    @if($report->reviewer)
                        <p class="mt-2 text-xs font-bold {{ $noteIsRevision ? 'text-white/50' : 'text-[#0B0F14]/45' }}">&mdash; {{ $report->reviewer->name }}</p>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <!-- ===================== ISI LAPORAN ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="space-y-9 p-6 sm:p-8 lg:p-10">

            <!-- Progress (khusus laporan divisi) -->
            @if($report->progress_percentage !== null && $progressValue !== '')
                <div>
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC]/10 text-[#0245EC]">
                            <i class="ti ti-chart-pie text-lg"></i>
                        </span>
                        <h3 class="{{ $sectionLbl }}">Progress Keseluruhan</h3>
                    </div>
                    <div class="flex items-center gap-5 rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-5">
                        <p class="shrink-0 text-3xl font-black leading-none text-[#0B0F14]">{{ $progressValue }}<span class="text-lg text-[#0B0F14]/50">%</span></p>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-[#0B0F14]/10">
                            <div class="h-full rounded-full bg-[#D9FF3A]" style="width: {{ min(100, max(0, (float) $report->progress_percentage)) }}%"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Pekerjaan Minggu Ini -->
            <div>
                <div class="mb-3.5 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC]/10 text-[#0245EC]">
                        <i class="ti ti-target text-lg"></i>
                    </span>
                    <h3 class="{{ $sectionLbl }}">Pekerjaan Minggu Ini</h3>
                </div>
                <div class="min-h-[100px] rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-5">
                    <p class="{{ $contentTxt }}">{{ $report->work_done }}</p>
                </div>
            </div>

            <!-- Hasil / Pencapaian -->
            <div>
                <div class="mb-3.5 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]">
                        <i class="ti ti-trophy text-lg"></i>
                    </span>
                    <h3 class="{{ $sectionLbl }}">Hasil / Pencapaian</h3>
                </div>
                <div class="min-h-[80px] rounded-2xl border border-[#D9FF3A]/60 border-l-4 border-l-[#D9FF3A] bg-[#D9FF3A]/15 p-5">
                    <p class="{{ $contentTxt }}">{{ $report->achievements ?: '—' }}</p>
                </div>
            </div>

            <!-- Kendala & Solusi -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0B0F14] text-[#D9FF3A]">
                            <i class="ti ti-barrier-block text-lg"></i>
                        </span>
                        <h3 class="{{ $sectionLbl }}">Kendala yang Dihadapi</h3>
                    </div>
                    <div class="min-h-[100px] rounded-2xl border border-[#0B0F14]/15 border-l-4 border-l-[#0B0F14] bg-[#0B0F14]/[0.04] p-5">
                        <p class="{{ $contentTxt }}">{{ $report->obstacles ?: '—' }}</p>
                    </div>
                </div>
                <div>
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC] text-white">
                            <i class="ti ti-bulb text-lg"></i>
                        </span>
                        <h3 class="{{ $sectionLbl }}">Solusi Tindakan</h3>
                    </div>
                    <div class="min-h-[100px] rounded-2xl border border-[#0245EC]/20 border-l-4 border-l-[#0245EC] bg-[#0245EC]/5 p-5">
                        <p class="{{ $contentTxt }}">{{ $report->solutions ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Rencana & Kebutuhan Bantuan -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="{{ $report->support_needed ? '' : 'md:col-span-2' }}">
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                            <i class="ti ti-calendar-time text-lg"></i>
                        </span>
                        <h3 class="{{ $sectionLbl }}">Rencana Minggu Depan</h3>
                    </div>
                    <div class="min-h-[100px] rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-5">
                        <p class="{{ $contentTxt }}">{{ $report->next_plan ?: '—' }}</p>
                    </div>
                </div>

                @if($report->support_needed)
                    <div>
                        <div class="mb-3.5 flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                                <i class="ti ti-help text-lg"></i>
                            </span>
                            <h3 class="{{ $sectionLbl }}">Kebutuhan Bantuan</h3>
                        </div>
                        <div class="min-h-[100px] rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-5">
                            <p class="{{ $contentTxt }}">{{ $report->support_needed }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app-layout>