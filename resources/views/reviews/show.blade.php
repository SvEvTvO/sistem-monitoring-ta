<x-app-layout>

    @php
        /* ---------- Status laporan ---------- */
        $statusConfig = [
            'SUBMITTED' => [
                'icon'  => 'ti-send',
                'label' => 'Menunggu Review',
                'hero'  => 'border-[#D9FF3A]/40 bg-[#D9FF3A]/15 text-[#D9FF3A]',
            ],
            'REVIEWED' => [
                'icon'  => 'ti-eye',
                'label' => 'Sedang Direview',
                'hero'  => 'border-white/25 bg-white/10 text-white',
            ],
            'APPROVED' => [
                'icon'  => 'ti-circle-check',
                'label' => 'Telah Disetujui',
                'hero'  => 'border-transparent bg-[#D9FF3A] text-[#0B0F14]',
            ],
            'REVISION_REQUIRED' => [
                'icon'  => 'ti-alert-triangle',
                'label' => 'Perlu Revisi',
                'hero'  => 'border-transparent bg-[#0B0F14] text-white',
            ],
        ];
        $conf = $statusConfig[$report->status] ?? [
            'icon'  => 'ti-file',
            'label' => $report->status,
            'hero'  => 'border-white/25 bg-white/10 text-white',
        ];

        /* Form keputusan hanya muncul jika BELUM ada keputusan final */
        $isResponded = in_array($report->status, ['APPROVED', 'REVISION_REQUIRED']);
        $isApproved  = $report->status === 'APPROVED';

        $chosenLabel    = $isResponded ? $labels->firstWhere('id', $report->evaluation_label_id) : null;
        $currentLabelId = old('evaluation_label_id', $report->evaluation_label_id);

        /* Ikon & varian warna chip label evaluasi (kelas CSS murni, lihat <style>) */
        $labelStyles = [
            'SANGAT BAIK'     => ['ti-star',       'eval-icon--gold'],
            'BAIK'            => ['ti-thumb-up',   'eval-icon--blue'],
            'PERLU PERBAIKAN' => ['ti-tool',       'eval-icon--neutral'],
            'KURANG'          => ['ti-thumb-down', 'eval-icon--dark'],
        ];

        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
        $contentTxt = 'whitespace-pre-line text-sm font-medium leading-relaxed text-[#0B0F14]/80';
    @endphp

    <!-- ================================================================
         KARTU EVALUASI: CSS MURNI (bukan Tailwind) agar state "terpilih"
         tidak bergantung pada proses compile Tailwind / manipulasi className.
         JS hanya men-toggle satu kelas .is-selected — dampaknya murni
         warna/border/opacity, TIDAK menyentuh layout sama sekali.
    ================================================================= -->
    <style>
        .eval-option { position: relative; display: block; cursor: pointer; }

        /* Radio tetap fokus-able (keyboard), tapi tak terlihat */
        .eval-option input[type="radio"] {
            position: absolute;
            width: 1px; height: 1px;
            margin: 0; padding: 0;
            opacity: 0;
            pointer-events: none;
        }

        .eval-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            height: 100%;
            box-sizing: border-box;
            padding: 20px 16px;
            border: 2px solid rgba(11, 15, 20, 0.10);
            border-radius: 16px;
            background: #FFFFFF;
            text-align: center;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }
        .eval-option:hover .eval-card        { border-color: rgba(2, 69, 236, 0.45); }
        .eval-option:focus-within .eval-card { border-color: rgba(2, 69, 236, 0.60); }

        .eval-option.is-selected .eval-card {
            border-color: #0245EC;
            background: rgba(2, 69, 236, 0.05);
            box-shadow: 0 10px 24px -12px rgba(2, 69, 236, 0.50);
        }

        .eval-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 20px;
            flex-shrink: 0;
        }
        .eval-icon i { line-height: 1; }
        .eval-icon--gold    { background: #D9FF3A;             color: #0B0F14; }
        .eval-icon--blue    { background: rgba(2,69,236,0.10); color: #0245EC; }
        .eval-icon--neutral { background: rgba(11,15,20,0.06); color: rgba(11,15,20,0.7); }
        .eval-icon--dark    { background: #0B0F14;             color: #D9FF3A; }

        .eval-name {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(11, 15, 20, 0.70);
            transition: color .2s ease;
        }
        .eval-option.is-selected .eval-name { color: #0245EC; }

        /* Badge centang: SELALU dirender, hanya naik/turun opacity — tidak ada
           perubahan display/ukuran yang bisa menggeser layout */
        .eval-check {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            background: #0245EC;
            color: #FFFFFF;
            opacity: 0;
            transform: scale(0.5);
            transition: opacity .2s ease, transform .2s ease;
        }
        .eval-check i { font-size: 12px; line-height: 1; }
        .eval-option.is-selected .eval-check { opacity: 1; transform: scale(1); }
    </style>

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7">
        <a href="{{ route('reviews.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                <i class="ti ti-arrow-left text-lg"></i>
            </span>
            Kembali ke Daftar Tugas
        </a>
        <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Pemeriksaan Laporan</h1>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Tinjau isi laporan mingguan, lalu berikan keputusan review.</p>
    </header>

    <!-- ===================== HERO: LAPORAN & PENGIRIM ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-file-description pointer-events-none absolute -bottom-8 -right-4 text-[9rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="mb-5 flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-bold backdrop-blur-sm {{ $conf['hero'] }}">
                    <i class="ti {{ $conf['icon'] }} text-sm"></i>
                    {{ $conf['label'] }}
                </span>
                <span class="inline-flex items-center gap-2.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#D9FF3A] text-[11px] font-black text-[#0B0F14]">{{ $report->projectWeek->week_number }}</span>
                    Minggu ke-{{ $report->projectWeek->week_number }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti ti-category text-sm text-[#D9FF3A]"></i>
                    {{ $report->division->name }}
                </span>
            </div>

            <h2 class="max-w-3xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2.25rem]">
                {{ $report->title }}
            </h2>

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

    <!-- ===================== ISI LAPORAN ===================== -->
    <section class="mb-6 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="space-y-9 p-6 sm:p-8 lg:p-10">

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

            @if($report->achievements)
                <div>
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]">
                            <i class="ti ti-trophy text-lg"></i>
                        </span>
                        <h3 class="{{ $sectionLbl }}">Hasil / Pencapaian</h3>
                    </div>
                    <div class="min-h-[100px] rounded-2xl border border-[#D9FF3A]/60 border-l-4 border-l-[#D9FF3A] bg-[#D9FF3A]/15 p-5">
                        <p class="{{ $contentTxt }}">{{ $report->achievements }}</p>
                    </div>
                </div>
            @endif

            @if($report->obstacles || $report->solutions)
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @if($report->obstacles)
                        <div>
                            <div class="mb-3.5 flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0B0F14] text-[#D9FF3A]">
                                    <i class="ti ti-barrier-block text-lg"></i>
                                </span>
                                <h3 class="{{ $sectionLbl }}">Kendala yang Dihadapi</h3>
                            </div>
                            <div class="min-h-[100px] rounded-2xl border border-[#0B0F14]/15 border-l-4 border-l-[#0B0F14] bg-[#0B0F14]/[0.04] p-5">
                                <p class="{{ $contentTxt }}">{{ $report->obstacles }}</p>
                            </div>
                        </div>
                    @endif

                    @if($report->solutions)
                        <div>
                            <div class="mb-3.5 flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0245EC] text-white">
                                    <i class="ti ti-bulb text-lg"></i>
                                </span>
                                <h3 class="{{ $sectionLbl }}">Solusi Tindakan</h3>
                            </div>
                            <div class="min-h-[100px] rounded-2xl border border-[#0245EC]/20 border-l-4 border-l-[#0245EC] bg-[#0245EC]/5 p-5">
                                <p class="{{ $contentTxt }}">{{ $report->solutions }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

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

    @if($isResponded)
        <!-- ===================== HASIL REVIEW (READ-ONLY) ===================== -->
        <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="h-2 w-full {{ $isApproved ? 'bg-[#D9FF3A]' : 'bg-[#0B0F14]' }}"></div>

            <div class="space-y-8 p-6 sm:p-8 lg:p-10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $isApproved ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'bg-[#0B0F14] text-[#D9FF3A]' }}">
                            <i class="ti {{ $isApproved ? 'ti-circle-check' : 'ti-refresh' }} text-2xl"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#0B0F14]">Hasil Review</h3>
                            <p class="text-xs font-medium text-[#0B0F14]/45">Keputusan sudah diberikan dan tidak dapat diubah di halaman ini.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-2 text-xs font-extrabold {{ $isApproved ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'bg-[#0B0F14] text-white' }}">
                        <i class="ti {{ $conf['icon'] }} text-sm {{ $isApproved ? '' : 'text-[#D9FF3A]' }}"></i>
                        {{ $conf['label'] }}
                    </span>
                </div>

                @if($chosenLabel)
                    @php [$roIcon] = $labelStyles[$chosenLabel->name] ?? ['ti-tag']; @endphp
                    <div>
                        <div class="mb-3.5 flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                                <i class="ti {{ $roIcon }} text-lg"></i>
                            </span>
                            <h4 class="{{ $sectionLbl }}">Label Evaluasi</h4>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] px-4 py-2.5 text-sm font-extrabold text-[#0B0F14]">
                            {{ $chosenLabel->name }}
                        </span>
                    </div>
                @endif

                <div>
                    <div class="mb-3.5 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-[#0B0F14]/60">
                            <i class="ti ti-message-circle-2 text-lg"></i>
                        </span>
                        <h4 class="{{ $sectionLbl }}">Komentar / Catatan Review</h4>
                    </div>
                    <div class="rounded-2xl border p-5 {{ $isApproved ? 'border-[#D9FF3A]/60 border-l-4 border-l-[#D9FF3A] bg-[#D9FF3A]/15' : 'border-[#0B0F14]/15 border-l-4 border-l-[#0B0F14] bg-[#0B0F14]/[0.04]' }}">
                        <p class="{{ $contentTxt }}">{{ $report->review_comment }}</p>
                    </div>
                </div>

                <p class="text-xs font-medium text-[#0B0F14]/40">
                    <i class="ti ti-history mr-1"></i> Direspon: {{ $report->updated_at->format('d M Y, H:i') }}
                </p>
            </div>
        </section>
    @else
        <!-- ===================== FORM KEPUTUSAN REVIEW ===================== -->
        <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

            <form action="{{ route('reviews.decide', $report) }}" method="POST" id="reviewForm">
                @csrf

                <div class="space-y-9 p-6 sm:p-8 lg:p-10">

                    @if ($errors->any())
                        <div class="flex items-start gap-4 rounded-2xl bg-[#0B0F14] px-5 py-4 shadow-[0_16px_40px_-18px_rgba(11,15,20,0.55)]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]">
                                <i class="ti ti-alert-circle text-xl"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-white">Periksa kembali isian Anda</h3>
                                <ul class="mt-1.5 space-y-1 text-xs font-medium text-white/70">
                                    @foreach ($errors->all() as $error)
                                        <li>&bull; {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0245EC] text-white shadow-[0_10px_24px_-10px_rgba(2,69,236,0.7)]">
                            <i class="ti ti-gavel text-2xl"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#0B0F14]">Berikan Keputusan Review</h3>
                            <p class="text-xs font-medium text-[#0B0F14]/45">Pilih label evaluasi, tulis komentar, lalu tentukan keputusan.</p>
                        </div>
                    </div>

                    <!-- Label Evaluasi -->
                    <div>
                        <div class="mb-5 flex items-center gap-2.5">
                            <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                            <h4 class="{{ $sectionLbl }}">Pilih Label Evaluasi <span class="ml-1 font-black text-[#0B0F14]">*</span></h4>
                        </div>

                        <div id="evalGrid" class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            @foreach($labels as $label)
                                @php
                                    [$lIcon, $lStyle] = $labelStyles[$label->name] ?? ['ti-tag', 'eval-icon--neutral'];
                                @endphp
                                <label class="eval-option">
                                    <input type="radio" name="evaluation_label_id" value="{{ $label->id }}" required {{ $currentLabelId == $label->id ? 'checked' : '' }}>
                                    <span class="eval-card">
                                        <span class="eval-check"><i class="ti ti-check"></i></span>
                                        <span class="eval-icon {{ $lStyle }}"><i class="ti {{ $lIcon }}"></i></span>
                                        <span class="eval-name">{{ $label->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Komentar Review -->
                    <div>
                        <div class="mb-5 flex items-center gap-2.5">
                            <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                            <h4 class="{{ $sectionLbl }}">Komentar / Catatan Review <span class="ml-1 font-black text-[#0B0F14]">*</span></h4>
                        </div>

                        <div class="relative">
                            <i class="ti ti-message-circle-2 pointer-events-none absolute left-4 top-4 text-lg text-[#0B0F14]/30"></i>
                            <textarea name="review_comment" id="review_comment" rows="5" required placeholder="Berikan arahan untuk perbaikan, atau apresiasi atas kinerja mereka..." class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">{{ old('review_comment', $report->review_comment) }}</textarea>
                        </div>
                        <p class="mt-2 text-xs font-medium text-[#0B0F14]/40">Komentar ini terlihat oleh pelapor sebagai bagian dari hasil review.</p>
                    </div>
                </div>

                <!-- Footer / Aksi -->
                <div class="flex flex-col gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] p-6 sm:flex-row sm:items-center sm:justify-end lg:px-10">
                    <button type="submit" name="decision" value="REVISION_REQUIRED" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-[#0B0F14]/15 bg-white px-8 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0B0F14] hover:bg-[#0B0F14] hover:text-white sm:w-auto">
                        <i class="ti ti-refresh text-lg"></i> Minta Revisi
                    </button>
                    <button type="submit" name="decision" value="APPROVED" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-colors hover:bg-[#D9FF3A]/90 sm:w-auto">
                        <i class="ti ti-check text-lg"></i> Setujui Laporan
                    </button>
                </div>
            </form>
        </section>
    @endif

    <!-- ===================== SCRIPT ===================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* 1. Seleksi kartu evaluasi — hanya toggle SATU kelas 'is-selected'.
                  Semua efek visualnya didefinisikan di <style> (CSS murni):
                  border, warna, opacity badge. Tidak ada perubahan struktur,
                  ukuran, atau kelas lain — layout tidak mungkin bergeser. */
            var evalGrid = document.getElementById('evalGrid');

            if (evalGrid) {
                var syncEvalSelection = function () {
                    evalGrid.querySelectorAll('.eval-option').forEach(function (option) {
                        var radio = option.querySelector('input[type="radio"]');
                        option.classList.toggle('is-selected', !!(radio && radio.checked));
                    });
                };

                evalGrid.addEventListener('change', syncEvalSelection);
                syncEvalSelection(); // kondisi awal (pre-checked / old input)
            }

            /* 2. Konfirmasi sebelum kirim keputusan */
            var reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.querySelectorAll('button[type="submit"]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        var msg = this.value === 'APPROVED'
                            ? 'Setujui laporan ini? Keputusan akan dikirim ke pelapor.'
                            : 'Kirim permintaan revisi untuk laporan ini?';
                        if (!confirm(msg)) e.preventDefault();
                    });
                });
            }
        });
    </script>
</x-app-layout>