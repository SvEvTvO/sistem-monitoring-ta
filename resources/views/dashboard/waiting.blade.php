<x-app-layout>

    @php
        /* Sapaan dinamis sesuai jam (senada dashboard utama) */
        $hour     = \Carbon\Carbon::now()->hour;
        $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));

        $firstName = explode(' ', Auth::user()->name)[0];
        $initial   = strtoupper(substr(Auth::user()->name, 0, 1));
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center gap-5 p-4 py-10 sm:p-6">

        <!-- ===================== HERO: MENUNGGU PENEMPATAN ===================== -->
        <section class="relative w-full max-w-2xl overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
            <div class="pointer-events-none absolute -right-24 -top-28 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -left-14 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
            <i class="ti ti-coffee pointer-events-none absolute -bottom-10 -right-8 text-[10rem] leading-none text-white/[0.06]"></i>

            <div class="relative p-8 text-center sm:p-10 lg:p-12">

                <!-- Ikon utama -->
                <span class="mx-auto mb-7 flex h-20 w-20 items-center justify-center rounded-[24px] bg-[#D9FF3A] text-[#0B0F14] shadow-[0_16px_36px_-14px_rgba(217,255,58,0.9)]">
                    <i class="ti ti-hourglass-empty animate-pulse text-4xl"></i>
                </span>

                <!-- Sapaan & judul -->
                <p class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-[#D9FF3A]">{{ $greeting }}</p>
                <h1 class="mt-2.5 text-3xl font-black leading-tight tracking-tight text-white">Menunggu Penempatan</h1>

                <p class="mx-auto mt-5 max-w-md text-sm font-semibold leading-relaxed text-white/70">
                    Halo, <strong class="font-extrabold text-white">{{ Auth::user()->name }}</strong>! Akun Anda berhasil terdaftar ke dalam sistem, namun saat ini belum dialokasikan ke dalam Divisi mana pun.
                </p>
                <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-relaxed text-white/50">
                    Mohon tunggu hingga Ketua Project mengatur penempatan tim Anda.
                </p>

                <!-- ===================== STEPPER PERJALANAN AKUN ===================== -->
                <div class="relative mt-10">
                    <!-- Garis dasar -->
                    <div class="absolute left-[12.5%] right-[12.5%] top-[22px] h-0.5 rounded-full bg-white/20"></div>
                    <!-- Garis progres: dari "terdaftar" ke "menunggu" -->
                    <div class="absolute left-[12.5%] top-[22px] h-0.5 w-[25%] rounded-full bg-[#D9FF3A]"></div>

                    <div class="relative grid grid-cols-4">
                        <!-- 1. Selesai -->
                        <div class="flex flex-col items-center gap-2.5">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14] shadow-[0_8px_20px_-8px_rgba(217,255,58,0.9)]">
                                <i class="ti ti-circle-check text-xl"></i>
                            </span>
                            <span class="text-center text-[10px] font-bold leading-tight text-white/70">Akun Terdaftar</span>
                        </div>

                        <!-- 2. Sedang berjalan -->
                        <div class="flex flex-col items-center gap-2.5">
                            <span class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-[#0245EC] shadow-lg">
                                <span class="absolute inset-0 animate-ping rounded-2xl bg-white/30" aria-hidden="true"></span>
                                <i class="ti ti-hourglass-empty relative animate-pulse text-xl"></i>
                            </span>
                            <span class="text-center text-[10px] font-bold leading-tight text-[#D9FF3A]">Menunggu Penempatan</span>
                        </div>

                        <!-- 3. Menanti -->
                        <div class="flex flex-col items-center gap-2.5">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/25 bg-white/10 text-white/50 backdrop-blur-sm">
                                <i class="ti ti-users-group text-xl"></i>
                            </span>
                            <span class="text-center text-[10px] font-bold leading-tight text-white/40">Masuk Divisi</span>
                        </div>

                        <!-- 4. Menanti -->
                        <div class="flex flex-col items-center gap-2.5">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/25 bg-white/10 text-white/50 backdrop-blur-sm">
                                <i class="ti ti-flag text-xl"></i>
                            </span>
                            <span class="text-center text-[10px] font-bold leading-tight text-white/40">Mulai Berkontribusi</span>
                        </div>
                    </div>
                </div>

                <!-- ===================== TIPS Sambil MENUNGGU ===================== -->
                <div class="mt-10 flex items-start gap-4 rounded-[20px] border border-white/15 bg-white/10 p-5 text-left backdrop-blur-sm">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-[#D9FF3A]">
                        <i class="ti ti-user-edit text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-extrabold text-white">Sambil menunggu...</p>
                        <p class="mt-1 text-xs font-medium leading-relaxed text-white/70">
                            Anda dapat memeriksa atau melengkapi
                            <a href="{{ route('profile.edit') }}" class="font-bold text-[#D9FF3A] underline decoration-2 underline-offset-2 transition-colors hover:text-white">Profil Anda</a>
                            terlebih dahulu — data yang lengkap membantu Ketua Project menentukan divisi yang paling sesuai.
                        </p>
                    </div>
                </div>

                <!-- ===================== AKSI ===================== -->
                <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('profile.edit') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-7 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto">
                        <i class="ti ti-user-edit text-lg"></i> Lengkapi Profil
                    </a>
                    <button type="button" onclick="window.location.reload()" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-7 py-3 text-sm font-extrabold text-white backdrop-blur-sm transition-colors hover:bg-white/20 sm:w-auto">
                        <i class="ti ti-refresh text-lg"></i> Periksa Status
                    </button>
                </div>
            </div>
        </section>

        <!-- Caption di bawah kartu -->
        <p class="flex max-w-md items-center justify-center text-center text-xs font-medium leading-relaxed text-[#0B0F14]/40">
            <i class="ti ti-info-circle mr-1.5 shrink-0"></i>
            Setelah Anda ditempatkan ke divisi, dashboard lengkap beserta laporan dan timeline akan otomatis tersedia.
        </p>
    </div>
</x-app-layout>