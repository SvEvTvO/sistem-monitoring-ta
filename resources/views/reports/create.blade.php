<x-app-layout>

    @php
        $weekRange   = $currentWeek->week_start->format('d M') . ' – ' . $currentWeek->week_end->format('d M Y');

        $inputClass  = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $taClass     = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white p-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $taIconClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass  = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $hintClass   = 'mb-2 text-xs font-medium text-[#0B0F14]/40';
        $errorChip   = 'mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-bold text-white';
        $optionalTag = 'ml-1.5 rounded-md bg-[#0B0F14]/5 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#0B0F14]/40';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7">
        <a href="{{ route('reports.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                <i class="ti ti-arrow-left text-lg"></i>
            </span>
            Kembali ke Riwayat
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Buat Laporan Pribadi</h1>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                <span class="h-2 w-2 rounded-full bg-[#0245EC]"></span> Laporan Pribadi
            </span>
        </div>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Laporkan hasil kerjamu untuk periode berjalan.</p>
    </header>

    <!-- ===================== HERO: TIPS PENGISIAN ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 p-6 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)] sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-file-pencil pointer-events-none absolute -bottom-6 -right-3 text-[7rem] leading-none text-white/[0.06]"></i>

        <div class="relative">
            <div class="mb-5 flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-2.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#D9FF3A] text-[11px] font-black text-[#0B0F14]">{{ $currentWeek->week_number }}</span>
                    Minggu ke-{{ $currentWeek->week_number }} <span class="font-medium text-white/50">{{ $weekRange }}</span>
                </span>
            </div>

            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]">
                    <i class="ti ti-info-circle text-2xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#D9FF3A]">Tips Pengisian Laporan</p>
                    <p class="mt-1.5 max-w-2xl text-sm font-semibold leading-relaxed text-white/80">
                        Isilah laporan ini selengkap mungkin. Laporan yang sudah disubmit akan langsung masuk ke antrean Ketua Divisi/Project dan tidak dapat diedit kembali kecuali dikembalikan untuk revisi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FORM ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_week_id" value="{{ $currentWeek->id }}">

            <div class="space-y-9 p-6 sm:p-8 lg:p-10">

                <!-- 1. Pekerjaan Utama -->
                <section>
                    <div class="mb-6 flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-sm font-black text-[#0B0F14]">1</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Pekerjaan Utama</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="title" class="{{ $labelClass }}">Judul Laporan <span class="font-black">*</span></label>
                            <div class="relative">
                                <i class="ti ti-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                <input type="text" name="title" id="title" placeholder="Contoh: Slicing UI Dashboard & Setup API" required value="{{ old('title') }}" class="{{ $inputClass }} pl-12">
                            </div>
                            @error('title') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="work_done" class="{{ $labelClass }}">Pekerjaan Minggu Ini <span class="font-black">*</span></label>
                            <p class="{{ $hintClass }}">Jelaskan secara detail apa saja task yang berhasil kamu selesaikan.</p>
                            <textarea name="work_done" id="work_done" rows="4" required placeholder="- Membuat desain form login&#10;- Integrasi API autentikasi&#10;- Fixing bug pada sidebar" class="{{ $taClass }}">{{ old('work_done') }}</textarea>
                            @error('work_done') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- 2. Refleksi & Pemecahan Masalah -->
                <section>
                    <div class="mb-6 flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-sm font-black text-[#0B0F14]">2</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Refleksi &amp; Pemecahan Masalah</h3>
                    </div>

                    <div class="mb-6">
                        <label for="achievements" class="{{ $labelClass }}">Hasil / Pencapaian<span class="{{ $optionalTag }}">Opsional</span></label>
                        <div class="relative">
                            <i class="ti ti-trophy pointer-events-none absolute left-4 top-4 text-lg text-[#0B0F14]/30"></i>
                            <textarea name="achievements" id="achievements" rows="2" placeholder="Adakah target khusus yang berhasil dilampaui?" class="{{ $taIconClass }}">{{ old('achievements') }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">
                        <div>
                            <label for="obstacles" class="{{ $labelClass }}">Kendala yang Dihadapi</label>
                            <div class="relative">
                                <i class="ti ti-barrier-block pointer-events-none absolute left-4 top-4 text-lg text-[#0B0F14]/30"></i>
                                <textarea name="obstacles" id="obstacles" rows="3" placeholder="Masalah apa yang memperlambat kerjamu?" class="{{ $taIconClass }}">{{ old('obstacles') }}</textarea>
                            </div>
                        </div>
                        <div>
                            <label for="solutions" class="{{ $labelClass }}">Solusi / Tindakan</label>
                            <div class="relative">
                                <i class="ti ti-bulb pointer-events-none absolute left-4 top-4 text-lg text-[#0B0F14]/30"></i>
                                <textarea name="solutions" id="solutions" rows="3" placeholder="Bagaimana caramu mengatasi kendala tersebut?" class="{{ $taIconClass }}">{{ old('solutions') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Perencanaan Selanjutnya -->
                <section>
                    <div class="mb-6 flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-sm font-black text-[#0B0F14]">3</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Perencanaan Selanjutnya</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">
                        <div>
                            <label for="next_plan" class="{{ $labelClass }}">Rencana Minggu Depan <span class="font-black">*</span></label>
                            <textarea name="next_plan" id="next_plan" rows="3" required placeholder="Target apa yang akan kamu kerjakan minggu depan?" class="{{ $taClass }}">{{ old('next_plan') }}</textarea>
                            @error('next_plan') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="support_needed" class="{{ $labelClass }}">Kebutuhan Bantuan / Catatan<span class="{{ $optionalTag }}">Opsional</span></label>
                            <textarea name="support_needed" id="support_needed" rows="3" placeholder="Butuh bantuan dari divisi lain atau Ketua Project? Tulis di sini." class="{{ $taClass }}">{{ old('support_needed') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Footer / Aksi -->
            <div class="flex flex-col gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] p-6 sm:flex-row sm:items-center sm:justify-end lg:px-10">
                <a href="{{ route('reports.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC] sm:w-auto">
                    Batal
                </a>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                    <i class="ti ti-send text-lg"></i> Kirim Laporan
                </button>
            </div>
        </form>
    </section>
</x-app-layout>