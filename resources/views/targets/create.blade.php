<x-app-layout>

    @php
        /* ---------- Setup UI ---------- */
        $isGlobalScope = $isProjectLeader;

        $scopeIcon  = $isGlobalScope ? 'ti-world' : 'ti-users-group';
        $scopeBadge = $isGlobalScope ? 'Target Global Project' : 'Target Divisi ' . $ledDivision->name;
        $scopeBold  = $isGlobalScope ? 'Seluruh Anggota Project (Global)' : 'Hanya Anggota Divisi ' . $ledDivision->name;

        $inputClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $hintClass  = 'mt-2 text-xs font-medium text-[#0B0F14]/40';
        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7">
        <a href="{{ route('targets.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                <i class="ti ti-arrow-left text-lg"></i>
            </span>
            Kembali ke Timeline
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Buat Target Baru</h1>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                <span class="h-2 w-2 rounded-full bg-[#D9FF3A]"></span> {{ $scopeBadge }}
            </span>
        </div>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Tetapkan nama dan periode pengerjaan target baru untuk tim.</p>
    </header>

    <!-- ===================== HERO: INFO VISIBILITAS ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 p-6 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)] sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-target pointer-events-none absolute -bottom-6 -right-3 text-[7rem] leading-none text-white/[0.06]"></i>

        <div class="relative flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]">
                <i class="ti {{ $scopeIcon }} text-2xl"></i>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#D9FF3A]">Informasi Visibilitas Target</p>
                <p class="mt-1.5 max-w-xl text-sm font-semibold leading-relaxed text-white/80">
                    Target yang kamu buat ini akan terlihat oleh
                    <strong class="font-extrabold text-white">{{ $scopeBold }}</strong>.
                </p>
            </div>
        </div>
    </section>

    <!-- ===================== FORM ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <form action="{{ route('targets.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <div class="space-y-9 p-6 sm:p-8 lg:p-10">

                <!-- Ringkasan error validasi -->
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

                <!-- 1. Detail Target -->
                <div>
                    <div class="mb-5 flex items-center gap-2.5">
                        <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                        <h3 class="{{ $sectionLbl }}">Detail Target</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Input Judul -->
                        <div>
                            <label for="titleInput" class="{{ $labelClass }}">Nama / Judul Target <span class="font-black">*</span></label>
                            <div class="relative">
                                <i class="ti ti-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                <input type="text" name="title" id="titleInput" placeholder="Contoh: Selesai Slicing Halaman Dashboard" required value="{{ old('title') }}" class="{{ $inputClass }} pl-12">
                            </div>
                            <p class="{{ $hintClass }}">Gunakan kalimat singkat agar mudah dibaca di timeline.</p>
                        </div>
                        
                        <!-- Input Deskripsi -->
                        <div>
                            <label for="descInput" class="{{ $labelClass }}">Catatan Tambahan (Opsional)</label>
                            <div class="relative">
                                <i class="ti ti-align-left pointer-events-none absolute left-4 top-4 text-lg text-[#0B0F14]/30"></i>
                                <textarea name="description" id="descInput" rows="3" placeholder="Tuliskan detail spesifik, tautan referensi, atau instruksi tambahan..." class="{{ $inputClass }} resize-none pl-12 pt-3.5">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Periode Pengerjaan -->
                <div>
                    <div class="mb-5 flex items-center gap-2.5">
                        <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                        <h3 class="{{ $sectionLbl }}">Periode Pengerjaan</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">
                        <div>
                            <label for="startDateInput" class="{{ $labelClass }}">Tanggal Mulai <span class="font-black">*</span></label>
                            <div class="relative">
                                <i class="ti ti-rocket pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                <input type="date" name="start_date" id="startDateInput" required value="{{ old('start_date') }}" class="{{ $inputClass }} pl-12">
                            </div>
                            <p class="{{ $hintClass }}">Target mulai dikerjakan pada tanggal ini.</p>
                        </div>
                        <div>
                            <label for="deadlineInput" class="{{ $labelClass }}">Deadline (Tenggat Waktu) <span class="font-black">*</span></label>
                            <div class="relative">
                                <i class="ti ti-flag pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                <input type="date" name="deadline" id="deadlineInput" required value="{{ old('deadline') }}" class="{{ $inputClass }} pl-12">
                            </div>
                            <p class="{{ $hintClass }}">Target wajib selesai sebelum tanggal ini berakhir.</p>
                        </div>
                    </div>

                    <!-- Estimasi durasi (live) -->
                    <div id="durationPanel" class="mt-5 flex items-center justify-between gap-4 rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] px-5 py-4 transition-all duration-300">
                        <div class="flex min-w-0 items-center gap-3.5">
                            <span id="durationIcon" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-white text-[#0B0F14]/50">
                                <i class="ti ti-hourglass text-lg"></i>
                            </span>
                            <p id="durationLabel" class="truncate text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Estimasi Durasi Pengerjaan</p>
                        </div>
                        <p id="durationValue" class="shrink-0 text-sm font-extrabold text-[#0B0F14]/35">Pilih kedua tanggal</p>
                    </div>
                </div>
            </div>

            <!-- Footer / Aksi -->
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] p-6 sm:flex-row sm:items-center lg:px-10">
                <a href="{{ route('targets.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC] sm:w-auto">
                    Batal
                </a>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                    <i class="ti ti-device-floppy text-lg"></i> Simpan Target
                </button>
            </div>
        </form>
    </section>

    <!-- ===================== SCRIPT DURASI LIVE ===================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startDateInput = document.getElementById('startDateInput');
            const deadlineInput  = document.getElementById('deadlineInput');
            const panel  = document.getElementById('durationPanel');
            const icon   = document.getElementById('durationIcon');
            const label  = document.getElementById('durationLabel');
            const value  = document.getElementById('durationValue');

            const panelIdle = 'mt-5 flex items-center justify-between gap-4 rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] px-5 py-4 transition-all duration-300';
            const panelOk   = 'mt-5 flex items-center justify-between gap-4 rounded-2xl border border-[#D9FF3A]/70 bg-[#D9FF3A]/15 px-5 py-4 transition-all duration-300';
            const panelWarn = 'mt-5 flex items-center justify-between gap-4 rounded-2xl border border-[#0B0F14] bg-[#0B0F14] px-5 py-4 shadow-[0_16px_40px_-18px_rgba(11,15,20,0.55)] transition-all duration-300';

            const iconIdle = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-white text-[#0B0F14]/50';
            const iconOk   = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0B0F14] text-[#D9FF3A]';
            const iconWarn = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]';

            const labelIdle = 'truncate text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45';
            const labelWarn = 'truncate text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50';

            const valueIdle = 'shrink-0 text-sm font-extrabold text-[#0B0F14]/35';
            const valueOk   = 'shrink-0 text-2xl font-black leading-none text-[#0B0F14]';
            const valueOkSm = 'shrink-0 text-sm font-extrabold text-[#0B0F14]';
            const valueWarn = 'shrink-0 text-sm font-extrabold text-[#D9FF3A]';

            function updateDuration() {
                const s = startDateInput.value;
                const d = deadlineInput.value;

                if (!s || !d) {
                    panel.className = panelIdle;
                    icon.className  = iconIdle;
                    icon.querySelector('i').className = 'ti ti-hourglass text-lg';
                    label.className = labelIdle;
                    label.textContent = 'Estimasi Durasi Pengerjaan';
                    value.className = valueIdle;
                    value.textContent = 'Pilih kedua tanggal';
                    return;
                }

                const diffDays = Math.round((new Date(d) - new Date(s)) / 86400000);

                if (diffDays < 0) {
                    panel.className = panelWarn;
                    icon.className  = iconWarn;
                    icon.querySelector('i').className = 'ti ti-alert-triangle text-lg';
                    label.className = labelWarn;
                    label.textContent = 'Rentang Tanggal Tidak Valid';
                    value.className = valueWarn;
                    value.textContent = 'Tenggat sebelum tanggal mulai';
                } else {
                    panel.className = panelOk;
                    icon.className  = iconOk;
                    icon.querySelector('i').className = 'ti ti-hourglass text-lg';
                    label.className = labelIdle;
                    label.textContent = 'Estimasi Durasi Pengerjaan';
                    if (diffDays === 0) {
                        value.className = valueOkSm;
                        value.textContent = 'Selesai di hari yang sama';
                    } else {
                        value.className = valueOk;
                        value.innerHTML = diffDays + ' <span class="text-sm font-bold">hari</span>';
                    }
                }
            }

            startDateInput.addEventListener('change', updateDuration);
            deadlineInput.addEventListener('change', updateDuration);
            updateDuration();
        });
    </script>
</x-app-layout>