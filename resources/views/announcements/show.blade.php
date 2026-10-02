<x-app-layout>

    @php
        $isGlobal = $announcement->audience_type === 'ALL_PROJECT';

        $scopeIcon  = $isGlobal ? 'ti-building' : 'ti-users-group';
        $scopeText  = $isGlobal ? 'Pengumuman Global' : 'Divisi ' . $announcement->division->name;

        $creatorName  = $announcement->creator->name ?? 'Sistem';
        $creatorFirst = explode(' ', $creatorName)[0];
        $creatorRole  = $isGlobal ? 'Ketua Project' : 'Ketua Divisi';

        /* Avatar: putih/biru untuk pengumuman global, lime untuk divisi (senada feed index) */
        $avatarClass = $isGlobal
            ? 'bg-white text-[#0245EC]'
            : 'bg-[#D9FF3A] text-[#0B0F14] shadow-[0_10px_24px_-10px_rgba(217,255,58,0.8)]';

        /* Badge "Baru" — ambang sama dengan feed index (< 3 hari) */
        $isNew = $announcement->created_at->gt(\Carbon\Carbon::now()->copy()->subDays(3));

        /* Fallback isi kosong */
        $contentBody = trim((string) $announcement->content) !== '' ? $announcement->content : null;
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <a href="{{ route('announcements.index') }}" class="group mb-4 inline-flex items-center gap-2.5 text-sm font-bold text-[#0B0F14]/50 transition-colors hover:text-[#0245EC]">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[#0B0F14]/10 bg-white shadow-sm transition-transform duration-200 group-hover:-translate-x-1">
                    <i class="ti ti-arrow-left text-lg"></i>
                </span>
                Kembali ke Daftar
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Detail Pengumuman</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full {{ $isGlobal ? 'bg-[#0245EC]' : 'bg-[#D9FF3A]' }}"></span>
                    {{ $scopeText }}
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Baca pengumuman ini selengkapnya.</p>
        </div>

        @if($canEdit)
            <a href="{{ route('announcements.edit', $announcement->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC] sm:w-auto lg:shrink-0">
                <i class="ti ti-edit text-lg"></i> Edit Pengumuman
            </a>
        @endif
    </header>

    <!-- ===================== HERO: PENGUMUMAN ===================== -->
    <section class="relative mb-6 overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)]">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-12 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <i class="ti ti-speakerphone pointer-events-none absolute -bottom-8 -right-4 text-[9rem] leading-none text-white/[0.06]"></i>

        <div class="relative p-6 sm:p-8 lg:p-10">
            <!-- Badge konteks -->
            <div class="mb-5 flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti {{ $scopeIcon }} text-sm {{ $isGlobal ? '' : 'text-[#D9FF3A]' }}"></i>
                    {{ $scopeText }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                    <i class="ti ti-calendar-event text-sm"></i>
                    {{ $announcement->created_at->format('d M Y, H:i') }}
                </span>
                @if($isNew)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14]">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#0B0F14]"></span> Baru
                    </span>
                @endif
            </div>

            <!-- Judul -->
            <h2 class="max-w-3xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2.25rem]">
                {{ $announcement->title }}
            </h2>

            <!-- Pembuat -->
            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-white/15 pt-6">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-base font-black {{ $avatarClass }}">
                    {{ strtoupper(substr($creatorName, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">Diterbitkan Oleh</p>
                    <p class="truncate text-base font-extrabold text-white">{{ $creatorFirst }} <span class="font-semibold text-white/60">&mdash; {{ $creatorRole }}</span></p>
                </div>
                <span class="ml-auto hidden whitespace-nowrap rounded-full border border-white/15 bg-[#0B0F14]/30 px-3.5 py-1.5 text-[11px] font-bold text-white/70 backdrop-blur-sm sm:inline-flex">
                    {{ $announcement->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </section>

    <!-- ===================== ISI PENGUMUMAN ===================== -->
    <section class="mb-8 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <div class="p-6 sm:p-8 lg:p-10">
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]">
                    <i class="ti ti-speakerphone text-lg"></i>
                </span>
                <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Isi Pengumuman</h3>
            </div>

            @if($contentBody)
                <div class="text-base leading-[1.85] text-[#0B0F14]/85 sm:text-lg">
                    {!! nl2br(e($contentBody)) !!}
                </div>
            @else
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-[#0B0F14]/15 bg-[#F7F8FA] py-12 text-center">
                    <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white">
                        <i class="ti ti-file-text text-3xl text-[#0B0F14]/30"></i>
                    </span>
                    <p class="text-sm font-semibold text-[#0B0F14]/50">Pengumuman ini belum memiliki isi.</p>
                </div>
            @endif

            <!-- Meta bawah -->
            <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-[#0B0F14]/10 pt-5">
                <p class="flex items-center text-xs font-semibold text-[#0B0F14]/45">
                    <i class="ti ti-clock mr-1.5"></i>
                    Diterbitkan {{ $announcement->created_at->format('d M Y, H:i') }} ({{ $announcement->created_at->diffForHumans() }})
                </p>
                <a href="{{ route('announcements.index') }}" class="group/btn inline-flex items-center gap-1.5 text-xs font-bold text-[#0245EC] hover:underline">
                    Lihat pengumuman lain <i class="ti ti-arrow-right text-sm transition-transform duration-200 group-hover/btn:translate-x-0.5"></i>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>