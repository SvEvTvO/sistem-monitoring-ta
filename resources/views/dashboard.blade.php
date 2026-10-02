<x-app-layout>

    @php
        /* ---------- Setup UI ---------- */
        $hour = \Carbon\Carbon::now()->hour;
        $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));

        /* Status laporan (murni turunan 5 root color) */
        $statusConfig = [
            'SUBMITTED'         => ['chip' => 'bg-[#0245EC]/10 text-[#0245EC]',   'icon' => 'ti-send',         'text' => 'Menunggu Review'],
            'REVIEWED'          => ['chip' => 'bg-[#0B0F14]/5 text-[#0B0F14]/70', 'icon' => 'ti-eye',          'text' => 'Sedang Direview'],
            'APPROVED'          => ['chip' => 'bg-[#D9FF3A] text-[#0B0F14]',      'icon' => 'ti-circle-check', 'text' => 'Disetujui'],
            'REVISION_REQUIRED' => ['chip' => 'bg-[#0B0F14] text-[#D9FF3A]',      'icon' => 'ti-refresh',      'text' => 'Perlu Revisi'],
        ];

        /* Kelas tombol pemilih divisi (dipakai di HTML & disuntik ke JS agar sinkron) */
        $btnIdle   = 'div-selector-btn w-full rounded-2xl border border-[#0B0F14]/10 bg-white p-4 text-left transition-all duration-200 hover:border-[#0245EC]/40';
        $btnActive = 'div-selector-btn w-full rounded-2xl border border-[#0245EC] bg-[#0245EC]/5 p-4 text-left shadow-[0_10px_24px_-12px_rgba(2,69,236,0.45)] transition-all duration-200';

        $cardClass = 'rounded-[24px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]';
        $labelClass = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    <!-- ===================== HERO: SAMBUTAN + PROGRESS ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-layout-dashboard pointer-events-none absolute -bottom-8 -right-4 text-[9rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <!-- Badge konteks -->
            <div class="mb-5 flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti ti-building text-sm"></i>
                    {{ $membership ? $membership->project->name : 'Belum ada project aktif' }}
                </span>
                @if($membership)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                        <i class="ti ti-users-group text-sm text-[#D9FF3A]"></i>
                        Divisi {{ $membership->division->name }}
                    </span>
                @endif
                @if($currentWeek)
                    <span class="inline-flex items-center gap-2.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#D9FF3A] text-[11px] font-black text-[#0B0F14]">{{ $currentWeek->week_number }}</span>
                        Minggu ke-{{ $currentWeek->week_number }}
                    </span>
                @endif
            </div>

            <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#D9FF3A]">{{ $greeting }}</p>
            <h1 class="text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">Dashboard Overview</h1>

            <!-- Progress keseluruhan -->
            <div class="mt-9">
                <div class="mb-3.5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">Progress Keseluruhan</p>
                        <p class="mt-1 text-sm font-semibold text-white/70">Dihitung otomatis dari laporan Divisi yang telah disetujui.</p>
                    </div>
                    <p class="text-4xl font-black leading-none text-white sm:text-5xl">{{ $projectProgress }}<span class="text-2xl text-[#D9FF3A]">%</span></p>
                </div>

                <div class="h-3.5 overflow-hidden rounded-full bg-white/20">
                    <div class="relative h-full overflow-hidden rounded-full bg-[#D9FF3A] transition-all duration-1000 ease-out" style="width: {{ $projectProgress }}%">
                        <div class="absolute inset-0 opacity-15" style="background-image: linear-gradient(45deg, #0B0F14 25%, transparent 25%, transparent 50%, #0B0F14 50%, #0B0F14 75%, transparent 75%, transparent); background-size: 14px 14px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== BARIS STATISTIK ===================== -->
    <section class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3 md:gap-6">

        <!-- Status Laporanku -->
        <div class="flex flex-col {{ $cardClass }} p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0245EC]/10 text-[#0245EC]">
                    <i class="ti ti-file-pencil text-xl"></i>
                </span>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Status Laporanku</p>
            </div>

            <div class="flex flex-1 flex-col justify-center">
                @if(!$currentWeek)
                    <p class="text-sm font-semibold text-[#0B0F14]/40">Belum ada periode aktif.</p>
                @elseif(!$myReport)
                    <div class="space-y-3">
                        <span class="inline-flex w-fit items-center gap-2 rounded-xl bg-[#0B0F14] px-4 py-2 text-sm font-extrabold text-white">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-[#D9FF3A]"></span> Belum Lapor
                        </span>
                        <a href="{{ route('reports.create') }}" class="inline-flex w-fit items-center text-xs font-bold text-[#0245EC] hover:underline">Buat sekarang &rarr;</a>
                    </div>
                @else
                    @php $conf = $statusConfig[$myReport->status]; @endphp
                    <div class="space-y-3">
                        <span class="inline-flex w-fit items-center gap-2 rounded-xl px-4 py-2 text-sm font-extrabold {{ $conf['chip'] }}">
                            <i class="ti {{ $conf['icon'] }} text-base"></i> {{ $conf['text'] }}
                        </span>
                        <a href="{{ route('reports.index') }}" class="inline-flex w-fit items-center text-xs font-bold text-[#0245EC] hover:underline">Lihat detail &rarr;</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Peran & Tugas -->
        <div class="flex flex-col {{ $cardClass }} p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14]">
                    <i class="ti ti-shield-check text-xl"></i>
                </span>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Peran & Tugas</p>
            </div>

            <div class="flex flex-1 flex-col justify-center">
                <div class="mb-4 flex flex-wrap gap-2">
                    @if($ledProject)
                        <span class="rounded-lg bg-[#D9FF3A] px-2.5 py-1 text-xs font-extrabold text-[#0B0F14]">Ketua Project</span>
                    @endif
                    @if($ledDivision)
                        <span class="rounded-lg bg-[#0245EC] px-2.5 py-1 text-xs font-extrabold text-white">Ketua Divisi</span>
                    @endif
                    @if(!$ledProject && !$ledDivision)
                        <span class="rounded-lg border border-[#0B0F14]/10 bg-[#F7F8FA] px-2.5 py-1 text-xs font-extrabold text-[#0B0F14]/60">Anggota Tim</span>
                    @endif
                </div>

                @if($pendingReviews > 0)
                    <a href="{{ route('reviews.index') }}" class="flex items-center justify-between rounded-2xl bg-[#D9FF3A] px-4 py-3 transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_24px_-10px_rgba(217,255,58,0.9)]">
                        <span class="flex items-center text-xs font-extrabold text-[#0B0F14]"><i class="ti ti-bell mr-1.5 text-sm"></i> Perlu Direview</span>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#0B0F14] text-xs font-extrabold text-[#D9FF3A]">{{ $pendingReviews }}</span>
                    </a>
                @else
                    <p class="text-xs font-semibold text-[#0B0F14]/40">Tidak ada laporan yang menunggu review Anda.</p>
                @endif
            </div>
        </div>

        <!-- Target Mendatang -->
        <a href="{{ route('targets.index') }}" class="group flex flex-col {{ $cardClass }} p-6 transition-colors hover:border-[#0245EC]/30">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60 transition-colors group-hover:bg-[#0245EC]/10 group-hover:text-[#0245EC]">
                    <i class="ti ti-calendar-due text-xl"></i>
                </span>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Target Mendatang</p>
            </div>

            <div class="flex flex-1 flex-col justify-center">
                <div class="flex items-end justify-between gap-3">
                    <p class="text-4xl font-black leading-none text-[#0B0F14] md:text-5xl">{{ $upcomingTargets->count() }}</p>
                    <span class="text-xs font-bold text-[#0245EC] group-hover:underline">Lihat Semua &rarr;</span>
                </div>
                <p class="mt-2 text-xs font-semibold text-[#0B0F14]/45">target terjadwal menunggu pencapaian</p>
            </div>
        </a>
    </section>

    <!-- ===================== TREN LAPORAN (KHUSUS KETUA DIVISI) ===================== -->
    @if($ledDivision)
        <section class="mb-6 {{ $cardClass }} p-6 sm:p-7">
            <div class="mb-2 flex items-center gap-2.5">
                <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                <h3 class="{{ $labelClass }}">Tren Laporan Divisi {{ $ledDivision->name }}</h3>
            </div>

            @if(count($chartData) > 0)
                <div id="reportsChart" class="mt-4 w-full"></div>
            @else
                <div class="mt-4 flex flex-col items-center justify-center rounded-2xl border border-dashed border-[#0B0F14]/15 bg-[#F7F8FA] py-12 text-center">
                    <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm">
                        <i class="ti ti-chart-bar text-3xl text-[#0B0F14]/30"></i>
                    </span>
                    <p class="text-sm font-semibold text-[#0B0F14]/50">Belum ada data laporan yang masuk untuk divisi ini.</p>
                </div>
            @endif
        </section>
    @endif

    <!-- ===================== PANTAUAN KINERJA DIVISI (SEMUA ANGGOTA) ===================== -->
    @if($membership && count($divisionDetails) > 0)
        <section class="mb-6 space-y-5">
            <div class="flex items-center gap-2.5">
                <span class="h-5 w-1.5 rounded-full bg-[#D9FF3A]"></span>
                <h3 class="text-sm font-black uppercase tracking-[0.15em] text-[#0B0F14]/60">Pantauan Kinerja Divisi Project</h3>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Chart perbandingan -->
                <div class="{{ $cardClass }} p-6 lg:col-span-2 sm:p-7">
                    <div class="mb-4 flex items-center gap-2.5">
                        <span class="h-3.5 w-1 rounded-full bg-[#0B0F14]/20"></span>
                        <h4 class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Perbandingan Progress Divisi</h4>
                    </div>
                    <div id="projectDivisionsChart" class="w-full"></div>
                </div>

                <!-- Daftar status divisi -->
                <div class="flex flex-col {{ $cardClass }} p-6 sm:p-7">
                    <div class="mb-4 flex items-center gap-2.5">
                        <span class="h-3.5 w-1 rounded-full bg-[#0B0F14]/20"></span>
                        <h4 class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Status Tim Aktif</h4>
                    </div>

                    <div class="custom-scrollbar max-h-[340px] flex-1 space-y-3 overflow-y-auto pr-2">
                        @foreach($divisionDetails as $div)
                            <div class="rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-4 transition-all hover:bg-white hover:shadow-sm" style="border-left: 4px solid {{ $div->color }};">
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <h5 class="text-sm font-bold text-[#0B0F14]">{{ $div->name }}</h5>
                                    <span class="rounded-md px-2 py-1 text-xs font-extrabold" style="background-color: {{ $div->color }}20; color: {{ $div->color }};">{{ $div->progress }}%</span>
                                </div>
                                <div class="space-y-1">
                                    <p class="flex items-center text-xs font-semibold text-[#0B0F14]/60">
                                        <i class="ti ti-user-circle mr-1.5 text-sm"></i> {{ $div->leader_name }}
                                    </p>
                                    <p class="flex items-center text-[11px] font-medium text-[#0B0F14]/40">
                                        <i class="ti ti-clock-check mr-1.5 text-sm"></i> Update: {{ $div->last_update }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- ===================== PARTISIPASI LAPORAN (KHUSUS KETUA PROJECT) ===================== -->
    @if($ledProject && count($interactiveChartData) > 0)
        <section class="mb-6 space-y-5">
            <div class="flex items-center gap-2.5">
                <span class="h-5 w-1.5 rounded-full bg-[#D9FF3A]"></span>
                <h3 class="text-sm font-black uppercase tracking-[0.15em] text-[#0B0F14]/60">Tingkat Partisipasi Laporan per Divisi</h3>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <!-- Pemilih divisi -->
                <div class="space-y-3 lg:col-span-1">
                    <p class="mb-1 text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Pilih Divisi</p>
                    @foreach($divisionDetails as $index => $div)
                        <button type="button" onclick="updateInteractiveChart({{ $div->id }})" id="btn-div-{{ $div->id }}" class="{{ $index === 0 ? $btnActive : $btnIdle }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="mr-3 h-3 w-3 rounded-full" style="background-color: {{ $div->color }}"></span>
                                    <span class="text-sm font-bold text-[#0B0F14]">{{ $div->name }}</span>
                                </div>
                                <i class="ti ti-chevron-right text-base text-[#0B0F14]/25"></i>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- Area chart -->
                <div class="{{ $cardClass }} p-6 lg:col-span-3 sm:p-7">
                    <h4 id="interactiveChartTitle" class="mb-2 text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Tren Laporan Masuk</h4>
                    <div id="interactiveLineChart" class="w-full"></div>
                </div>
            </div>
        </section>
    @endif

    <!-- ===================== GRID UTAMA ===================== -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Kolom kiri -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Pengumuman terbaru -->
            @if($latestAnnouncement)
                <section class="{{ $cardClass }} p-6 sm:p-7">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                            <h3 class="{{ $labelClass }}">Pengumuman Terbaru</h3>
                        </div>
                        <span class="whitespace-nowrap rounded-full border border-[#0B0F14]/10 bg-[#F7F8FA] px-3 py-1 text-[11px] font-bold text-[#0B0F14]/50">{{ $latestAnnouncement->created_at->diffForHumans() }}</span>
                    </div>

                    <h4 class="text-lg font-extrabold text-[#0B0F14]">{{ $latestAnnouncement->title }}</h4>
                    <div class="mt-3 rounded-2xl border-l-4 border-l-[#D9FF3A] bg-[#F7F8FA] p-4">
                        <p class="text-sm font-medium leading-relaxed text-[#0B0F14]/70">{{ $latestAnnouncement->content }}</p>
                    </div>
                </section>
            @endif

            <!-- Target mendatang -->
            <section class="{{ $cardClass }} p-6 sm:p-7">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                        <h3 class="{{ $labelClass }}">Target Mendatang</h3>
                    </div>
                    <a href="{{ route('targets.index') }}" class="text-xs font-bold text-[#0245EC] hover:underline">Lihat Semua</a>
                </div>

                @if($upcomingTargets->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingTargets as $target)
                            @php
                                $tDeadline = \Carbon\Carbon::parse($target->deadline)->endOfDay();
                                $tDaysLeft = \Carbon\Carbon::now()->diffInDays($tDeadline, false);
                            @endphp
                            <div class="group flex items-center justify-between gap-4 rounded-2xl border border-[#0B0F14]/10 p-3.5 transition-all hover:border-[#0245EC]/30 hover:bg-[#F7F8FA]">
                                <div class="flex min-w-0 items-center gap-3.5">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0B0F14]/5 text-[#0B0F14]/60 transition-colors group-hover:bg-[#0245EC]/10 group-hover:text-[#0245EC]">
                                        <i class="{{ $target->division_id ? 'ti ti-users-group' : 'ti ti-building' }} text-xl"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-[#0B0F14]">{{ $target->title }}</p>
                                        <p class="text-xs font-medium text-[#0B0F14]/50">Deadline: {{ \Carbon\Carbon::parse($target->deadline)->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    @if($tDaysLeft < 0)
                                        <span class="inline-flex items-center rounded-full bg-[#0B0F14] px-3 py-1 text-xs font-extrabold text-white">Terlewat</span>
                                    @elseif($tDaysLeft < 1)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3 py-1 text-xs font-extrabold text-[#0B0F14]">
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#0B0F14]"></span> Hari Ini
                                        </span>
                                    @else
                                        <span class="whitespace-nowrap text-xs font-extrabold text-[#0B0F14]/50">{{ (int) ceil($tDaysLeft) }} Hari lagi</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-[#0B0F14]/15 bg-[#F7F8FA] py-10 text-center">
                        <span class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D9FF3A]">
                            <i class="ti ti-check text-3xl text-[#0B0F14]"></i>
                        </span>
                        <p class="text-sm font-semibold text-[#0B0F14]/50">Semua target saat ini sudah selesai!</p>
                    </div>
                @endif
            </section>
        </div>

        <!-- Kolom kanan: aksi cepat -->
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <div class="{{ $cardClass }} p-6 sm:p-7">
                <div class="mb-4 flex items-center gap-2.5">
                    <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                    <h3 class="{{ $labelClass }}">Aksi Cepat</h3>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('reports.create') }}" class="group flex items-center justify-between rounded-2xl bg-[#0245EC] p-4 text-white shadow-[0_14px_30px_-12px_rgba(2,69,236,0.7)] transition-all hover:-translate-y-0.5">
                        <div class="flex items-center">
                            <i class="ti ti-plus mr-3 text-xl transition-transform group-hover:scale-110"></i>
                            <span class="text-sm font-extrabold">Buat Laporan Pribadi</span>
                        </div>
                        <i class="ti ti-chevron-right text-[#D9FF3A] transition-transform group-hover:translate-x-0.5"></i>
                    </a>

                    @if($ledDivision)
                        <a href="{{ route('reports.create-division') }}" class="group flex items-center justify-between rounded-2xl bg-[#D9FF3A] p-4 text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all hover:-translate-y-0.5">
                            <div class="flex items-center">
                                <i class="ti ti-folder-plus mr-3 text-xl transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-extrabold">Buat Laporan Divisi</span>
                            </div>
                            <i class="ti ti-chevron-right text-[#0B0F14]/40 transition-transform group-hover:translate-x-0.5"></i>
                        </a>
                    @endif

                    <a href="{{ route('targets.index') }}" class="group flex items-center justify-between rounded-2xl border border-[#0B0F14]/10 bg-[#D9FF3A] p-4 text-[#0B0F14] transition-all hover:border-[#0245EC]/40 hover:text-[#0245EC]">
                        <div class="flex items-center">
                            <i class="ti ti-target-arrow mr-3 text-xl"></i>
                            <span class="text-sm font-extrabold">Lihat Timeline Target</span>
                        </div>
                        <i class="ti ti-chevron-right text-[#0B0F14]/30 transition-transform group-hover:translate-x-0.5 group-hover:text-[#0245EC]"></i>
                    </a>
                </div>
            </div>
        </aside>
    </div>

    <!-- ===================== SCRIPT APEXCHARTS ===================== -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Chart tren laporan (Khusus Ketua Divisi)
            @if($ledDivision && count($chartData) > 0)
                var optionsDiv = {
                    chart: { type: 'area', height: 320, fontFamily: 'Inter, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
                    series: [{ name: 'Laporan Masuk', data: {!! json_encode($chartData) !!} }],
                    xaxis: { categories: {!! json_encode($chartLabels) !!}, tooltip: { enabled: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { tickAmount: 4, labels: { formatter: function(val) { return Math.round(val); } } },
                    colors: ['#0245EC'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                    dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
                    grid: { borderColor: 'rgba(11,15,20,0.08)', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } }
                };
                new ApexCharts(document.querySelector("#reportsChart"), optionsDiv).render();
            @endif

            // 2. Bar chart perbandingan progress (Semua Anggota)
            @if($membership && count($divisionProgressData) > 0)
                var optionsProject = {
                    chart: { type: 'bar', height: 320, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                    series: [{ name: 'Progress', data: {!! json_encode($divisionProgressData) !!} }],
                    xaxis: { categories: {!! json_encode($divisionLabels) !!}, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { max: 100, tickAmount: 5, labels: { formatter: function (val) { return val + "%"; } } },
                    colors: {!! json_encode(array_column($divisionDetails, 'color')) !!},
                    plotOptions: { bar: { borderRadius: 8, horizontal: false, columnWidth: '45%', distributed: true } },
                    dataLabels: { enabled: true, formatter: function (val) { return val + "%"; }, style: { fontSize: '12px', colors: ["#ffffff"] } },
                    legend: { show: false },
                    grid: { borderColor: 'rgba(11,15,20,0.08)', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#projectDivisionsChart"), optionsProject).render();
            @endif

            // 3. Chart interaktif partisipasi (Khusus Ketua Project)
            @if($ledProject && count($interactiveChartData) > 0)
                window.interactiveData = {!! json_encode($interactiveChartData) !!};
                window.btnIdleClass   = {!! json_encode($btnIdle) !!};
                window.btnActiveClass = {!! json_encode($btnActive) !!};

                var firstDivId = Object.keys(window.interactiveData)[0];
                var initData = window.interactiveData[firstDivId];

                var optionsInteractive = {
                    chart: { type: 'area', height: 320, fontFamily: 'Inter, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
                    series: [{ name: 'Laporan Masuk', data: initData.data }],
                    xaxis: { categories: initData.labels, tooltip: { enabled: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { tickAmount: 4, labels: { formatter: function(val) { return Math.round(val); } } },
                    colors: [initData.color],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                    dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
                    grid: { borderColor: 'rgba(11,15,20,0.08)', strokeDashArray: 4 }
                };

                window.chartInteractive = new ApexCharts(document.querySelector("#interactiveLineChart"), optionsInteractive);
                window.chartInteractive.render();
                document.getElementById('interactiveChartTitle').innerText = 'Tren Laporan Masuk: ' + initData.name;
            @endif
        });

        // Fungsi tombol pemilihan divisi pada chart interaktif
        function updateInteractiveChart(divId) {
            if (!window.interactiveData || !window.chartInteractive) return;

            var selectedData = window.interactiveData[divId];

            window.chartInteractive.updateOptions({
                xaxis: { categories: selectedData.labels },
                colors: [selectedData.color]
            });
            window.chartInteractive.updateSeries([{
                name: 'Laporan Masuk',
                data: selectedData.data
            }]);

            document.getElementById('interactiveChartTitle').innerText = 'Tren Laporan Masuk: ' + selectedData.name;

            // Reset dan highlight tombol (kelas disuntik dari PHP agar selalu sinkron dengan tampilan)
            document.querySelectorAll('.div-selector-btn').forEach(function (btn) {
                btn.className = window.btnIdleClass;
            });
            var activeBtn = document.getElementById('btn-div-' + divId);
            if (activeBtn) {
                activeBtn.className = window.btnActiveClass;
            }
        }
    </script>
</x-app-layout>