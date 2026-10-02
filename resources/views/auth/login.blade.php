<x-guest-layout>

    @php
        $inputClass = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $pwClass    = 'w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-12 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10';
        $labelClass = 'mb-2 block text-sm font-bold text-[#0B0F14]';
        $errorChip  = 'mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-bold text-white';
        $eyeBtn     = 'absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/35 transition-colors hover:bg-[#F7F8FA] hover:text-[#0245EC]';
    @endphp

    <!-- ===================== HEADER ===================== -->
    <div class="mb-7 text-center">
        <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#0245EC]/10 text-[#0245EC]">
            <i class="ti ti-door-enter text-3xl"></i>
        </span>
        <h1 class="text-2xl font-black tracking-tight text-[#0B0F14]">Selamat Datang Kembali</h1>
        <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Masuk untuk melanjutkan pemantauan project-mu.</p>
    </div>

    <!-- Status sesi (flash message, mis. reset password) -->
    @if (session('status'))
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-[#D9FF3A]/60 bg-[#D9FF3A]/15 px-4 py-3">
            <i class="ti ti-info-circle mt-0.5 text-lg text-[#0B0F14]/70"></i>
            <p class="text-xs font-bold leading-relaxed text-[#0B0F14]/80">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="{{ $labelClass }}">Alamat Email <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-mail pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com" class="{{ $inputClass }} pl-12">
            </div>
            @error('email') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Kata Sandi -->
        <div>
            <label for="password" class="{{ $labelClass }}">Kata Sandi <span class="font-black">*</span></label>
            <div class="relative">
                <i class="ti ti-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi..." class="{{ $pwClass }}">
                <button type="button" data-pw-toggle data-pw-target="password" aria-label="Tampilkan kata sandi" class="{{ $eyeBtn }}">
                    <i class="ti ti-eye text-base"></i>
                </button>
            </div>
            @error('password') <p class="{{ $errorChip }}"><i class="ti ti-alert-circle text-xs text-[#D9FF3A]"></i> {{ $message }}</p> @enderror
        </div>

        <!-- Ingat saya + Lupa sandi -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <label for="remember_me" class="flex cursor-pointer select-none items-center gap-2.5">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 cursor-pointer rounded border-[#0B0F14]/20 accent-[#0245EC]">
                <span class="text-sm font-semibold text-[#0B0F14]/60">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#0245EC] hover:underline">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-8 py-3.5 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0">
            <i class="ti ti-arrow-right text-lg"></i> Masuk
        </button>
    </form>

    <!-- Footer -->
    <p class="mt-6 text-center text-sm font-medium text-[#0B0F14]/50">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-extrabold text-[#0245EC] hover:underline">Daftar sekarang</a>
    </p>

    <!-- Toggle lihat/sembunyi sandi -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
        });
    </script>
</x-guest-layout>