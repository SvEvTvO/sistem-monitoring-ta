<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Monitoring TA') }}</title>

    <!-- Font & ikon. CATATAN: jika guest layout-mu sebelumnya memuat asset lain (vite/font lokal), pertahankan bagian <head> milikmu. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FA] font-sans text-[#0B0F14] antialiased">

    <!-- Dekorasi latar halus -->
    <div class="pointer-events-none fixed -right-32 -top-32 h-96 w-96 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-40 -left-24 h-96 w-96 rounded-full bg-[#0245EC]/10 blur-3xl"></div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">

        <!-- Brand -->
        <a href="/" class="group mb-7 flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0245EC] shadow-[0_14px_30px_-12px_rgba(2,69,236,0.7)] transition-transform duration-200 group-hover:-translate-y-0.5">
                <i class="ti ti-chart-bar text-2xl text-[#D9FF3A]"></i>
            </span>
            <span class="text-xl font-black tracking-tight text-[#0B0F14]">{{ config('app.name', 'Monitoring TA') }}</span>
        </a>

        <!-- Kartu utama: SEMUA halaman auth (login, register, lupa/reset sandi, verifikasi) dirender di sini -->
        <div class="w-full max-w-md overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_8px_40px_rgba(11,15,20,0.08)]">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>
            <div class="p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-7 text-center text-xs font-medium text-[#0B0F14]/35">
            &copy; {{ date('Y') }} {{ config('app.name', 'Monitoring TA') }} &mdash; Semua hak dilindungi.
        </p>
    </div>
</body>
</html>
