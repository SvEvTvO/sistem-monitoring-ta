<x-app-layout>

    @php
        $isGlobalScope = $isProjectLeader;

        $scopeIcon  = $isGlobalScope ? 'ti-world' : 'ti-users-group';
        $scopeText  = $isGlobalScope ? 'Semua Project' : 'Divisi ' . $ledDivision->name;
        $scopeBold  = $isGlobalScope ? 'Seluruh Anggota Project (Global)' : 'Hanya Anggota Divisi ' . $ledDivision->name;
        $scopeDot   = $isGlobalScope ? 'bg-[#0245EC]' : 'bg-[#D9FF3A]';

        $authorName = auth()->user()->name ?? 'Anda';
        $authorFirst = explode(' ', $authorName)[0];

        $inputClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $taClass    = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white p-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $hintClass  = 'mt-2 text-xs font-medium text-[#0B0F14]/40';
        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7">
        <a href="{{ route('announcements.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                <i class="ti ti-arrow-left text-lg"></i>
            </span>
            Kembali ke Daftar
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Buat Pengumuman Baru</h1>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                <span class="h-2 w-2 rounded-full {{ $scopeDot }}"></span> {{ $scopeText }}
            </span>
        </div>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Sampaikan informasi penting kepada tim.</p>
    </header>

    <!-- ===================== HERO: VISIBILITAS ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 p-6 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)] sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-speakerphone pointer-events-none absolute -bottom-6 -right-3 text-[7rem] leading-none text-white/[0.06]"></i>

        <div class="relative flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]">
                <i class="ti {{ $scopeIcon }} text-2xl"></i>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#D9FF3A]">Visibilitas Pengumuman Otomatis</p>
                <p class="mt-1.5 max-w-2xl text-sm font-semibold leading-relaxed text-white/80">
                    Sistem mendeteksi Anda sebagai <strong class="font-extrabold text-white">{{ $isGlobalScope ? 'Ketua Project' : 'Ketua Divisi' }}</strong>.
                    Pengumuman yang Anda buat akan otomatis disiarkan kepada
                    <strong class="font-extrabold text-white">{{ $scopeBold }}</strong>.
                </p>
            </div>
        </div>
    </section>

    <!-- ===================== FORM ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <form action="{{ route('announcements.store') }}" method="POST">
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

                <!-- 1. Isi Pengumuman -->
                <div>
                    <div class="mb-5 flex items-center gap-2.5">
                        <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                        <h3 class="{{ $sectionLbl }}">Tulis Pengumuman</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <label for="titleInput" class="{{ $labelClass }}">Judul Pengumuman <span class="font-black">*</span></label>
                                <span id="titleCounter" class="text-[11px] font-bold text-[#0B0F14]/35">0 karakter</span>
                            </div>
                            <div class="relative">
                                <i class="ti ti-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                <input type="text" name="title" id="titleInput" placeholder="Contoh: Jadwal Meeting Mingguan Diundur" required value="{{ old('title') }}" class="{{ $inputClass }} pl-12">
                            </div>
                            <p class="{{ $hintClass }}">Buat judul singkat dan jelas — ini yang pertama dibaca anggota di feed.</p>
                        </div>

                        <div>
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <label for="contentInput" class="{{ $labelClass }}">Isi Pengumuman <span class="font-black">*</span></label>
                                <span id="contentCounter" class="text-[11px] font-bold text-[#0B0F14]/35">0 karakter</span>
                            </div>
                            <textarea name="content" id="contentInput" rows="6" required placeholder="Tuliskan detail informasi yang ingin disampaikan..." class="{{ $taClass }} resize-y">{{ old('content') }}</textarea>
                            <p id="readingTime" class="{{ $hintClass }}">Estimasi waktu baca: kurang dari 1 menit.</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Pratinjau -->
                <div>
                    <div class="mb-5 flex items-center gap-2.5">
                        <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                        <h3 class="{{ $sectionLbl }}">Pratinjau di Feed</h3>
                    </div>

                    <!-- Kartu ini meniru gaya kartu feed announcements.index -->
                    <div class="pointer-events-none select-none rounded-2xl border border-[#0B0F14]/10 bg-white p-5">
                        <div class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $isGlobalScope ? 'bg-[#0245EC]/10 text-[#0245EC]' : 'bg-[#D9FF3A] text-[#0B0F14]' }}">
                                <i class="ti {{ $isGlobalScope ? 'ti-building' : 'ti-users-group' }} text-xl"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                    <span class="font-extrabold {{ $isGlobalScope ? 'text-[#0245EC]' : 'text-[#0B0F14]/70' }}">{{ $scopeText }}</span>
                                    <span class="text-[#0B0F14]/25">&bull;</span>
                                    <span class="font-semibold text-[#0B0F14]/45">oleh {{ $authorFirst }}</span>
                                    <span class="text-[#0B0F14]/25">&bull;</span>
                                    <span class="font-semibold text-[#0B0F14]/45">baru saja</span>
                                </div>
                                <h4 id="pvTitle" class="truncate text-base font-extrabold leading-snug tracking-tight text-[#0B0F14] sm:text-lg">Judul pengumuman akan tampil di sini</h4>
                                <p id="pvContent" class="mt-1 line-clamp-2 overflow-hidden text-sm font-medium leading-relaxed text-[#0B0F14]/55">Cuplikan isi pengumuman akan tampil di sini...</p>
                            </div>
                            <i class="ti ti-chevron-right text-lg text-[#0B0F14]/25"></i>
                        </div>
                    </div>
                    <p class="{{ $hintClass }}">Beginilah cara pengumuman Anda muncul di daftar pengumuman anggota.</p>
                </div>
            </div>

            <!-- Footer / Aksi -->
            <div class="flex flex-col gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] p-6 sm:flex-row sm:items-center sm:justify-end lg:px-10">
                <a href="{{ route('announcements.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC] sm:w-auto">
                    Batal
                </a>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                    <i class="ti ti-send text-lg"></i> Publikasikan Sekarang
                </button>
            </div>
        </form>
    </section>

    <!-- ===================== SCRIPT: PRATINJAU & PENGHITUNG ===================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var titleInput    = document.getElementById('titleInput');
            var contentInput  = document.getElementById('contentInput');
            var titleCounter  = document.getElementById('titleCounter');
            var contentCounter = document.getElementById('contentCounter');
            var readingTime   = document.getElementById('readingTime');
            var pvTitle       = document.getElementById('pvTitle');
            var pvContent     = document.getElementById('pvContent');

            function updatePreview() {
                var t = titleInput.value.trim();
                var c = contentInput.value.trim();

                /* Pratinjau — hanya mengisi teks, tidak menyentuh class apa pun */
                pvTitle.textContent   = t !== '' ? t : 'Judul pengumuman akan tampil di sini';
                pvContent.textContent = c !== '' ? c : 'Cuplikan isi pengumuman akan tampil di sini...';

                /* Penghitung karakter */
                titleCounter.textContent   = titleInput.value.length + ' karakter';
                contentCounter.textContent = contentInput.value.length + ' karakter';

                /* Estimasi waktu baca (±200 kata/menit) */
                var words = c === '' ? 0 : c.split(/\s+/).length;
                var minutes = Math.max(1, Math.round(words / 200));
                readingTime.textContent = words === 0
                    ? 'Estimasi waktu baca: kurang dari 1 menit.'
                    : (words <= 200
                        ? 'Estimasi waktu baca: kurang dari 1 menit (' + words + ' kata).'
                        : 'Estimasi waktu baca: sekitar ' + minutes + ' menit (' + words + ' kata).');
            }

            titleInput.addEventListener('input', updatePreview);
            contentInput.addEventListener('input', updatePreview);
            updatePreview();
        });
    </script>
</x-app-layout>