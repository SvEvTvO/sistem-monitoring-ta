<x-app-layout>

    @php
        /* ---------- Setup UI ---------- */
        $initials  = strtoupper(substr(Auth::user()->name, 0, 1));
        $firstName = explode(' ', Auth::user()->name)[0];

        $inputClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $pwClass    = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-12 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $hintClass  = 'mt-2 text-xs font-medium text-[#0B0F14]/40';
        $errorChip  = 'mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-bold text-white';
        $eyeBtn     = 'absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/35 transition-colors hover:bg-[#F7F8FA] hover:text-[#0245EC]';
    @endphp

    <!-- Form tersembunyi bawaan Breeze untuk pengiriman ulang email verifikasi (dipertahankan) -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}"></form>

    <!-- ===================== HERO: IDENTITAS AKUN ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-user-circle pointer-events-none absolute -bottom-10 -right-4 text-[10rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-5">
                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[22px] bg-[#D9FF3A] text-4xl font-black text-[#0B0F14] shadow-[0_16px_36px_-14px_rgba(217,255,58,0.9)]">
                        {{ $initials }}
                    </span>
                    <div class="min-w-0">
                        <p class="mb-1 text-[10px] font-extrabold uppercase tracking-[0.22em] text-[#D9FF3A]">Kelola Profil</p>
                        <h1 class="truncate text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl">{{ Auth::user()->name }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-white/60">
                            <span class="flex items-center"><i class="ti ti-mail mr-1.5"></i> {{ Auth::user()->email }}</span>
                            @if(Auth::user()->whatsapp_number)
                                <span class="hidden items-center sm:flex"><span class="mr-3 text-white/25">&bull;</span> <i class="ti ti-brand-whatsapp mr-1.5 text-[#D9FF3A]"></i> {{ Auth::user()->whatsapp_number }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-[#D9FF3A] px-4 py-2 text-xs font-extrabold text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]">
                    <i class="ti ti-shield-check text-sm"></i> Akun Terverifikasi
                </span>
            </div>
        </div>
    </section>

    <!-- ===================== GRID FORM ===================== -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- ========== KOLOM 1: INFORMASI PRIBADI ========== -->
        <section class="flex h-full flex-col overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

            <div class="flex flex-1 flex-col p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0245EC]/10 text-[#0245EC]">
                        <i class="ti ti-user-edit text-xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold text-[#0B0F14]">Informasi Pribadi</h2>
                        <p class="mt-0.5 text-xs font-medium text-[#0B0F14]/45">Kelola identitas dan alamat email Anda.</p>
                    </div>
                </div>

                <form method="post" action="{{ route('profile.update') }}" class="flex flex-1 flex-col space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="{{ $labelClass }}">Nama Lengkap <span class="font-black">*</span></label>
                        <div class="relative">
                            <i class="ti ti-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="{{ $inputClass }} pl-12">
                        </div>
                        @error('name') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClass }}">Alamat Email <span class="font-black">*</span></label>
                        <div class="relative">
                            <i class="ti ti-mail pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="{{ $inputClass }} pl-12">
                        </div>
                        <p class="{{ $hintClass }}">Email digunakan untuk notifikasi dan pemulihan akun.</p>
                        @error('email') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                    </div>

                    <div class="flex-1"></div>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0">
                            <i class="ti ti-device-floppy text-lg"></i> Simpan Profil
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="inline-flex items-center gap-1.5 rounded-xl bg-[#D9FF3A]/20 px-3.5 py-2 text-xs font-extrabold text-[#0B0F14]">
                                <i class="ti ti-check"></i> Tersimpan
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <!-- ========== KOLOM 2: KEAMANAN & SANDI ========== -->
        <section class="flex h-full flex-col overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="h-2 w-full bg-[#0B0F14]"></div>

            <div class="flex flex-1 flex-col p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0B0F14] text-[#D9FF3A]">
                        <i class="ti ti-lock text-xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold text-[#0B0F14]">Keamanan &amp; Sandi</h2>
                        <p class="mt-0.5 text-xs font-medium text-[#0B0F14]/45">Perbarui kata sandi untuk menjaga akun tetap aman.</p>
                    </div>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="flex flex-1 flex-col space-y-5">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="{{ $labelClass }}">Kata Sandi Saat Ini</label>
                        <div class="relative">
                            <i class="ti ti-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $pwClass }}">
                            <button type="button" data-pw-toggle data-pw-target="update_password_current_password" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                                <i class="ti ti-eye text-base"></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="update_password_password" class="{{ $labelClass }}">Kata Sandi Baru</label>
                        <div class="relative">
                            <i class="ti ti-key pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="{{ $pwClass }}">
                            <button type="button" data-pw-toggle data-pw-target="update_password_password" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                                <i class="ti ti-eye text-base"></i>
                            </button>
                        </div>

                        <!-- Pengukur kekuatan sandi (CSS murni, JS hanya set width + 1 class) -->
                        <div class="pw-meter" aria-hidden="true">
                            <div id="pwStrengthFill" class="pw-fill"></div>
                        </div>
                        <p id="pwStrengthLabel" class="{{ $hintClass }}">Minimal 8 karakter, kombinasi huruf besar, angka, dan simbol.</p>
                        @error('password', 'updatePassword') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="{{ $labelClass }}">Konfirmasi Sandi Baru</label>
                        <div class="relative">
                            <i class="ti ti-lock-check pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $pwClass }}">
                            <button type="button" data-pw-toggle data-pw-target="update_password_password_confirmation" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                                <i class="ti ti-eye text-base"></i>
                            </button>
                        </div>
                        <p id="pwMatchLabel" class="{{ $hintClass }}"></p>
                        @error('password_confirmation', 'updatePassword') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
                    </div>

                    <div class="flex-1"></div>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl border-2 border-[#0B0F14]/15 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0B0F14] hover:bg-[#0B0F14] hover:text-white">
                            <i class="ti ti-refresh text-lg"></i> Perbarui Sandi
                        </button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="inline-flex items-center gap-1.5 rounded-xl bg-[#D9FF3A]/20 px-3.5 py-2 text-xs font-extrabold text-[#0B0F14]">
                                <i class="ti ti-check"></i> Tersimpan
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- ===================== ZONA BAHAYA: HAPUS AKUN (HITAM = BERBAHAYA) ===================== -->
    <section class="relative mb-8 overflow-hidden rounded-[28px] bg-[#0B0F14] text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)]" x-data="{ confirmDeletion: false }">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[#D9FF3A]/15 blur-3xl"></div>
        <i class="ti ti-alert-triangle pointer-events-none absolute -bottom-8 -right-4 text-[9rem] leading-none text-white/[0.05]"></i>

        <div class="relative p-6 sm:p-8">
            <div class="mb-6 flex items-center gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14]">
                    <i class="ti ti-alert-triangle text-xl"></i>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-white">Hapus Akun Permanen</h2>
                    <p class="mt-0.5 text-xs font-medium text-white/50">Tindakan ini tidak dapat dibatalkan. Seluruh data Anda akan lenyap.</p>
                </div>
            </div>

            <!-- Pesan Pengantar -->
            <div x-show="!confirmDeletion">
                <p class="mb-6 max-w-3xl text-sm font-medium leading-relaxed text-white/70">
                    Setelah akun Anda dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen. Pastikan Anda telah mengunduh data atau informasi apa pun yang ingin Anda simpan sebelum melanjutkan.
                </p>
                <button type="button" @click="confirmDeletion = true" class="inline-flex items-center gap-2 rounded-2xl border-2 border-[#D9FF3A]/50 px-6 py-3 text-sm font-extrabold text-[#D9FF3A] transition-all duration-200 hover:bg-[#D9FF3A] hover:text-[#0B0F14]">
                    <i class="ti ti-trash text-lg"></i> Mulai Penghapusan Akun
                </button>
            </div>

            <!-- Form Konfirmasi -->
            <div x-show="confirmDeletion" x-cloak style="display: none;">
                <form method="post" action="{{ route('profile.destroy') }}" class="rounded-2xl border border-white/15 bg-white/10 p-6 backdrop-blur-sm">
                    @csrf
                    @method('delete')

                    <h3 class="mb-2 text-sm font-extrabold text-[#D9FF3A]">Apakah Anda yakin ingin menghapus akun ini?</h3>
                    <p class="mb-5 max-w-2xl text-sm font-medium text-white/70">
                        Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
                    </p>

                    <div class="mb-6 max-w-md">
                        <div class="relative">
                            <i class="ti ti-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                            <input type="password" name="password" id="password" placeholder="Masukkan kata sandi..." required class="w-full rounded-2xl border border-transparent bg-white py-3.5 pl-12 pr-12 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:ring-4 focus:ring-[#D9FF3A]/30">
                            <button type="button" data-pw-toggle data-pw-target="password" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/35 transition-colors hover:bg-[#F7F8FA] hover:text-[#0245EC]">
                                <i class="ti ti-eye text-base"></i>
                            </button>
                        </div>
                        @error('password', 'userDeletion') <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#D9FF3A] px-2.5 py-1 text-[11px] font-bold text-[#0B0F14]"><i class="ti ti-alert-circle text-xs"></i> {{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="button" @click="confirmDeletion = false" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/20 px-6 py-3 text-sm font-extrabold text-white/70 transition-colors hover:border-white/50 hover:text-white sm:w-auto">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-lg transition-all duration-200 hover:-translate-y-0.5 sm:w-auto">
                            <i class="ti ti-trash text-lg"></i> Ya, Hapus Akun Saya
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- ===================== STYLE & SCRIPT (ANTI-BUG: CSS murni, JS hanya set width/teks/class custom) ===================== -->
    <style>
        .pw-meter { height: 8px; margin-top: 10px; border-radius: 9999px; background: rgba(11, 15, 20, 0.08); overflow: hidden; }
        .pw-fill  { height: 100%; width: 0%; border-radius: 9999px; background: #0B0F14; transition: width .25s ease, background-color .25s ease; }
        .pw-fill--fair   { background: #0245EC; }
        .pw-fill--good   { background: #D9FF3A; }
        .pw-fill--strong { background: #D9FF3A; box-shadow: 0 0 12px rgba(217, 255, 58, 0.55); }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ---------- 1. Toggle lihat/sembunyi sandi ---------- */
            document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(this.dataset.pwTarget);
                    if (!input) return;
                    var icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('ti-eye');
                        icon.classList.add('ti-eye-off');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('ti-eye-off');
                        icon.classList.add('ti-eye');
                    }
                });
            });

            /* ---------- 2. Pengukur kekuatan sandi + indikator kecocokan ---------- */
            var pwInput     = document.getElementById('update_password_password');
            var pwConfirm   = document.getElementById('update_password_password_confirmation');
            var fill        = document.getElementById('pwStrengthFill');
            var strengthLbl = document.getElementById('pwStrengthLabel');
            var matchLbl    = document.getElementById('pwMatchLabel');

            function scorePassword(v) {
                var score = 0;
                if (v.length >= 8)  score++;
                if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
                if (/\d/.test(v))    score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                if (v.length >= 12) score++;
                return score; // 0 - 5
            }

            function updateStrength() {
                if (!pwInput || !fill) return;
                var v = pwInput.value;

                fill.classList.remove('pw-fill--fair', 'pw-fill--good', 'pw-fill--strong');

                if (v === '') {
                    fill.style.width = '0%';
                    strengthLbl.textContent = 'Minimal 8 karakter, kombinasi huruf besar, angka, dan simbol.';
                    return;
                }

                var s = scorePassword(v);
                if (s <= 1)      { fill.style.width = '20%'; strengthLbl.textContent = 'Kekuatan: lemah — tambahkan variasi karakter.'; }
                else if (s === 2){ fill.style.width = '45%'; fill.classList.add('pw-fill--fair');   strengthLbl.textContent = 'Kekuatan: cukup — masih bisa diperkuat.'; }
                else if (s === 3){ fill.style.width = '70%'; fill.classList.add('pw-fill--good');   strengthLbl.textContent = 'Kekuatan: baik.'; }
                else             { fill.style.width = '100%'; fill.classList.add('pw-fill--strong'); strengthLbl.textContent = 'Kekuatan: kuat — pertahankan!'; }
            }

            function updateMatch() {
                if (!pwConfirm || !matchLbl) return;
                var c = pwConfirm.value;

                if (c === '') {
                    matchLbl.textContent = '';
                    return;
                }
                matchLbl.textContent = (pwInput && c === pwInput.value)
                    ? 'Sandi cocok.'
                    : 'Sandi belum cocok dengan kata sandi baru.';
            }

            if (pwInput)    { pwInput.addEventListener('input', function () { updateStrength(); updateMatch(); }); }
            if (pwConfirm)  { pwConfirm.addEventListener('input', updateMatch); }
            updateStrength();
        });
    </script>
</x-app-layout>