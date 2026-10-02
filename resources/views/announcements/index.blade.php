<x-app-layout>

    @php
        $now = \Carbon\Carbon::now();

        /* Total pengumuman (aman untuk paginator & collection) */
        $totalAnnouncements = $announcements instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $announcements->total()
            : $announcements->count();

        $isSearching = request()->filled('search');

        /* ---------- Kelompokkan berdasarkan waktu (memudahkan pemindaian) ---------- */
        $groupIcons = [
            'Hari Ini'   => 'ti-bolt',
            'Minggu Ini' => 'ti-calendar-week',
            'Bulan Ini'  => 'ti-calendar-month',
            'Lebih Lama' => 'ti-archive',
        ];
        $grouped = [
            'Hari Ini'   => collect(),
            'Minggu Ini' => collect(),
            'Bulan Ini'  => collect(),
            'Lebih Lama' => collect(),
        ];
        foreach ($announcements as $item) {
            if ($item->created_at->isToday()) {
                $grouped['Hari Ini']->push($item);
            } elseif ($item->created_at->diffInDays($now) < 7) {
                $grouped['Minggu Ini']->push($item);
            } elseif ($item->created_at->diffInDays($now) < 30) {
                $grouped['Bulan Ini']->push($item);
            } else {
                $grouped['Lebih Lama']->push($item);
            }
        }
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Pusat Pengumuman</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#D9FF3A]"></span>
                    {{ $totalAnnouncements }} Pengumuman
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Informasi penting terkait project dan divisimu — <span class="font-bold text-[#0245EC]">klik pengumuman</span> untuk membacanya.</p>
        </div>

        @if($canCreate)
            <a href="{{ route('announcements.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto lg:shrink-0">
                <i class="ti ti-plus text-xl"></i> Buat Pengumuman
            </a>
        @endif
    </header>

    <!-- ===================== PENCARIAN ===================== -->
    <section class="mb-8 rounded-[20px] border border-[#0B0F14]/10 bg-white p-4 shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <form action="{{ route('announcements.index') }}" method="GET" class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i class="ti ti-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/35"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau isi pengumuman..." aria-label="Cari pengumuman" class="w-full rounded-xl border border-[#0B0F14]/10 bg-white py-3 pl-12 pr-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
            </div>

            <div class="flex gap-2 sm:shrink-0">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0B0F14] px-6 py-3 text-sm font-extrabold text-white transition-opacity hover:opacity-90 sm:flex-none">
                    <i class="ti ti-search text-base"></i> Cari
                </button>
                @if($isSearching)
                    <a href="{{ route('announcements.index') }}" title="Hapus pencarian" class="inline-flex items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm font-extrabold text-[#0B0F14]/60 transition-colors hover:border-[#0B0F14] hover:bg-[#0B0F14] hover:text-white">
                        <i class="ti ti-x text-base"></i>
                    </a>
                @endif
            </div>
        </form>

        @if($isSearching)
            <p class="mt-2.5 flex flex-wrap items-center text-xs font-semibold text-[#0B0F14]/45">
                <i class="ti ti-filter mr-1.5"></i>
                Hasil pencarian untuk "<span class="text-[#0245EC]">{{ request('search') }}</span>"
            </p>
        @endif
    </section>

    <!-- ===================== FEED PENGUMUMAN ===================== -->
    @if($announcements->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-[28px] border border-dashed border-[#0B0F14]/15 bg-white px-6 py-16 text-center shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            @if($isSearching)
                <span class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA]">
                    <i class="ti ti-search text-4xl text-[#0B0F14]/35"></i>
                </span>
                <h3 class="text-lg font-black text-[#0B0F14]">Tidak Ada Hasil Ditemukan</h3>
                <p class="mt-1.5 max-w-md text-sm font-medium text-[#0B0F14]/50">
                    Tidak ada pengumuman yang cocok dengan kata kunci "{{ request('search') }}". Coba gunakan kata kunci yang lain.
                </p>
                <a href="{{ route('announcements.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl border border-[#0B0F14]/10 bg-white px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC]">
                    <i class="ti ti-refresh text-lg"></i> Reset Pencarian
                </a>
            @else
                <span class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#D9FF3A]">
                    <i class="ti ti-speakerphone text-4xl text-[#0B0F14]"></i>
                </span>
                <h3 class="text-lg font-black text-[#0B0F14]">Belum Ada Pengumuman</h3>
                <p class="mt-1.5 max-w-md text-sm font-medium text-[#0B0F14]/50">Pengumuman dari Ketua Project atau Ketua Divisi akan tampil di sini.</p>
                @if($canCreate)
                    <a href="{{ route('announcements.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5">
                        <i class="ti ti-plus text-lg"></i> Buat Pengumuman Pertama
                    </a>
                @endif
            @endif
        </div>
    @else
        <div class="space-y-8">
            @foreach($grouped as $label => $items)
                @continue($items->isEmpty())

                <section>
                    <!-- Header kelompok waktu -->
                    <div class="mb-3 flex items-center gap-2.5">
                        <i class="ti {{ $groupIcons[$label] }} text-sm text-[#0B0F14]/35"></i>
                        <h2 class="text-[11px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">{{ $label }}</h2>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $label === 'Hari Ini' ? 'bg-[#D9FF3A] text-[#0B0F14]' : 'bg-[#0B0F14]/5 text-[#0B0F14]/45' }}">{{ $items->count() }}</span>
                        <span class="ml-1 h-px flex-1 bg-[#0B0F14]/10"></span>
                    </div>

                    <div class="space-y-3">
                        @foreach($items as $announcement)
                            @php
                                $isGlobal = $announcement->audience_type === 'ALL_PROJECT';

                                /* Hak edit: Ketua Project (semua) atau Ketua Divisi pemilik pengumuman */
                                $canEditAnnouncement = false;
                                if ($isProjectLeader) {
                                    $canEditAnnouncement = true;
                                } elseif ($ledDivision && $announcement->division_id == $ledDivision->id) {
                                    $canEditAnnouncement = true;
                                }

                                $creatorName = $announcement->creator->name ?? 'Sistem';
                                $isNew       = $announcement->created_at->gt($now->copy()->subDays(3));
                            @endphp

                            <article class="group relative rounded-2xl border border-[#0B0F14]/10 bg-white p-5 transition-all duration-200 hover:border-[#0245EC]/30 hover:bg-[#F7F8FA]/50 hover:shadow-[0_10px_28px_rgba(11,15,20,0.07)]">
                                <div class="flex items-start gap-4">
                                    <!-- Ikon cakupan (pembawa kode warna) -->
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $isGlobal ? 'bg-[#0245EC]/10 text-[#0245EC]' : 'bg-[#D9FF3A] text-[#0B0F14]' }}">
                                        <i class="ti {{ $isGlobal ? 'ti-building' : 'ti-users-group' }} text-xl"></i>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <!-- Meta ringkas: cakupan · pembuat · waktu -->
                                        <div class="mb-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                            <span class="font-extrabold {{ $isGlobal ? 'text-[#0245EC]' : 'text-[#0B0F14]/70' }}">{{ $isGlobal ? 'Semua Project' : 'Divisi ' . $announcement->division->name }}</span>
                                            <span class="text-[#0B0F14]/25">&bull;</span>
                                            <span class="font-semibold text-[#0B0F14]/45">oleh {{ explode(' ', $creatorName)[0] }}</span>
                                            <span class="text-[#0B0F14]/25">&bull;</span>
                                            <span class="font-semibold text-[#0B0F14]/45" title="{{ $announcement->created_at->format('d M Y, H:i') }}">{{ $announcement->created_at->diffForHumans() }}</span>
                                            @if($isNew)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9FF3A] px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-[#0B0F14]">
                                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#0B0F14]"></span> Baru
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-base font-extrabold leading-snug tracking-tight text-[#0B0F14] transition-colors duration-200 group-hover:text-[#0245EC] sm:text-lg">
                                            {{ $announcement->title }}
                                        </h3>

                                        <p class="mt-1 line-clamp-2 overflow-hidden text-sm font-medium leading-relaxed text-[#0B0F14]/55">
                                            {{ \Illuminate\Support\Str::limit($announcement->content, 180, '...') }}
                                        </p>
                                    </div>

                                    <!-- Aksi samping: edit (khusus berhak) + indikator klik -->
                                    <div class="flex shrink-0 flex-col items-center justify-center gap-2 self-stretch">
                                        @if($canEditAnnouncement)
                                            <a href="{{ route('announcements.edit', $announcement->id) }}" title="Edit pengumuman ini" class="relative z-20 flex h-9 w-9 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-white text-[#0B0F14]/45 shadow-sm transition-colors hover:border-[#0245EC]/40 hover:text-[#0245EC]">
                                                <i class="ti ti-edit text-base"></i>
                                            </a>
                                        @endif
                                        <i class="ti ti-chevron-right text-lg text-[#0B0F14]/25 transition-all duration-200 group-hover:translate-x-0.5 group-hover:text-[#0245EC]"></i>
                                    </div>
                                </div>

                                <!-- Link melebar: SELURUH kartu bisa diklik menuju detail -->
                                <a href="{{ route('announcements.show', $announcement->id) }}" class="absolute inset-0 z-10 rounded-2xl focus-visible:ring-2 focus-visible:ring-[#0245EC]/40" aria-label="Baca pengumuman: {{ $announcement->title }}"></a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    @if($announcements->hasPages())
        <div class="mt-8">
            {{ $announcements->links() }}
        </div>
    @endif
</x-app-layout>