<x-guest-layout>

    @php
        $inputClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $pwClass    = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-12 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $hintClass  = 'mt-2 text-xs font-medium text-[#0B0F14]/40';
        $errorChip  = 'mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-bold text-white';
        $eyeBtn     = 'absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/35 transition-colors hover:bg-[#F7F8FA] hover:text-[#0245EC]';
    @endphp

    <!-- ===================== HEADER ===================== -->
    <div class="mb-7 text-center">
        <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14]">
            <i class="ti ti-user-plus text-3xl"></i>
        </span>
        <h1 class="text-2xl font-black tracking-tight text-[#0B0F14]">Buat Akun Baru</h1>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Daftarkan dirimu untuk mulai berkontribusi di project.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lengkap <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkapmu" class="{{ $inputClass }} pl-12">
            </div>
            @error('name') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Username -->
        <div>
            <label for="username" class="{{ $labelClass }}">Username <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-at pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="pilih username unik" class="{{ $inputClass }} pl-12">
            </div>
            @error('username') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="{{ $labelClass }}">Alamat Email <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-mail pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com" class="{{ $inputClass }} pl-12">
            </div>
            @error('email') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Kata Sandi -->
        <div>
            <label for="password" class="{{ $labelClass }}">Kata Sandi <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Masukkan kata sandi..." class="{{ $pwClass }}">
                <button type="button" data-pw-toggle data-pw-target="password" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                    <i class="ti ti-eye text-base"></i>
                </button>
            </div>

            <!-- Pengukur kekuatan sandi (CSS murni, JS hanya set width + 1 class) -->
            <div class="pw-meter" aria-hidden="true">
                <div id="pwStrengthFill" class="pw-fill"></div>
            </div>
            <p id="pwStrengthLabel" class="{{ $hintClass }}">Minimal 8 karakter, kombinasi huruf besar, angka, dan simbol.</p>
            @error('password') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div>
            <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Kata Sandi <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-lock-check pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi..." class="{{ $pwClass }}">
                <button type="button" data-pw-toggle data-pw-target="password_confirmation" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                    <i class="ti ti-eye text-base"></i>
                </button>
            </div>
            <p id="pwMatchLabel" class="{{ $hintClass }}"></p>
            @error('password_confirmation') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3.5 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0">
            <i class="ti ti-user-plus text-lg"></i> Daftar
        </button>
    </form>

    <!-- Footer -->
    <p class="mt-6 text-center text-sm font-medium text-[#0B0F14]/50">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-extrabold text-[#0245EC] hover:underline">Masuk di sini</a>
    </p>

    <!-- ===================== STYLE & SCRIPT (ANTI-BUG) ===================== -->
    <style>
        .pw-meter { height: 8px; margin-top: 10px; border-radius: 9999px; background: rgba(11, 15, 20, 0.08); overflow: hidden; }
        .pw-fill  { height: 100%; width: 0%; border-radius: 9999px; background: #0B0F14; transition: width .25s ease, background-color .25s ease; }
        .pw-fill--fair   { background: #0245EC; }
        .pw-fill--good   { background: #D9FF3A; }
        .pw-fill--strong { background: #D9FF3A; box-shadow: 0 0 12px rgba(217, 255, 58, 0.55); }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* 1. Toggle lihat/sembunyi sandi */
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

            /* 2. Pengukur kekuatan sandi + indikator kecocokan */
            var pwInput     = document.getElementById('password');
            var pwConfirm   = document.getElementById('password_confirmation');
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
                if (s <= 1)      { fill.style.width = '20%';  strengthLbl.textContent = 'Kekuatan: lemah — tambahkan variasi karakter.'; }
                else if (s === 2){ fill.style.width = '45%';  fill.classList.add('pw-fill--fair');   strengthLbl.textContent = 'Kekuatan: cukup — masih bisa diperkuat.'; }
                else if (s === 3){ fill.style.width = '70%';  fill.classList.add('pw-fill--good');   strengthLbl.textContent = 'Kekuatan: baik.'; }
                else             { fill.style.width = '100%'; fill.classList.add('pw-fill--strong'); strengthLbl.textContent = 'Kekuatan: kuat — pertahankan!'; }
            }

            function updateMatch() {
                if (!pwConfirm || !matchLbl) return;
                var c = pwConfirm.value;
                if (c === '') { matchLbl.textContent = ''; return; }
                matchLbl.textContent = (pwInput && c === pwInput.value)
                    ? 'Kata sandi cocok.'
                    : 'Kata sandi belum cocok.';
            }

            if (pwInput)   { pwInput.addEventListener('input', function () { updateStrength(); updateMatch(); }); }
            if (pwConfirm) { pwConfirm.addEventListener('input', updateMatch); }
            updateStrength();
        });
    </script>
</x-guest-layout>