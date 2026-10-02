<x-app-layout>

    @php
        /* ---------- Setup UI ---------- */
        $currentStatus   = request('status', '');
        $remainingTarget = max(0, $totalTargets - $completedTargets);

        $statusChips = [
            ''            => 'Semua',
            'IN_PROGRESS' => 'Berjalan',
            'NOT_STARTED' => 'Belum Mulai',
            'COMPLETED'   => 'Selesai',
            'OVERDUE'     => 'Terlewat',
        ];

        $inputClass  = 'w-full rounded-xl border border-[#0B0F14]/10 bg-white px-3.5 py-2.5 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all duration-200 focus:border-[#0245EC] focus:ring-2 focus:ring-[#0245EC]/20';
        $selectClass = $inputClass . ' appearance-none pr-9 cursor-pointer';
        $labelClass  = 'mb-2 block text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Target & Timeline Project</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#0245EC]"></span>
                    {{ $totalTargets }} Total Target
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Pantau jadwal, tenggat waktu, dan pencapaian target kerja.</p>
        </div>

        @if($canCreate)
            <a href="{{ route('targets.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto lg:shrink-0">
                <i class="ti ti-plus text-xl"></i> Buat Target Baru
            </a>
        @endif
    </header>

    <!-- ===================== PAPAN STATISTIK (BENTO) ===================== -->
    <section class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-4 md:gap-6">

        <!-- Panel Penyelesaian (Hero Biru) -->
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0245EC] via-[#0245EC] to-[#0245EC]/70 p-6 shadow-[0_28px_60px_-24px_rgba(2,69,236,0.55)] md:col-span-2 md:p-7">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#D9FF3A]/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -left-12 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <i class="ti ti-target pointer-events-none absolute -bottom-7 -right-5 text-[9rem] leading-none text-white/[0.06]"></i>

            <div class="relative">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/50">Penyelesaian Keseluruhan</p>

                <div class="mt-4">
                    <p class="text-4xl font-black leading-none text-white md:text-5xl">
                        {{ $progressPercentage }}<span class="text-2xl text-[#D9FF3A]">%</span>
                    </p>
                    <p class="mt-2 text-sm font-semibold text-white/70">{{ $completedTargets }} dari {{ $totalTargets }} target selesai</p>
                </div>

                <div class="mt-6">
                    <div class="h-3.5 overflow-hidden rounded-full bg-white/20">
                        <div class="relative h-full overflow-hidden rounded-full bg-[#D9FF3A] transition-all duration-1000 ease-out" style="width: {{ $progressPercentage }}%">
                            <div class="absolute inset-0 opacity-15" style="background-image: linear-gradient(45deg, #0B0F14 25%, transparent 25%, transparent 50%, #0B0F14 50%, #0B0F14 75%, transparent 75%, transparent); background-size: 14px 14px;"></div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-[11px] font-semibold text-white/50">
                            <i class="ti ti-hourglass mr-1"></i> {{ $remainingTarget }} target belum selesai
                        </p>
                        <p class="text-[11px] font-semibold text-white/50">Menuju 100%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Sedang Berjalan -->
        <div class="flex flex-col justify-between gap-5 rounded-[24px] border border-[#0B0F14]/10 bg-white p-6 shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0245EC]/10 text-[#0245EC]">
                    <i class="ti ti-clock-play text-xl"></i>
                </span>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Sedang Berjalan</p>
            </div>
            <p class="text-4xl font-black leading-none text-[#0B0F14] md:text-5xl">{{ $activeTargets }}</p>
        </div>

        <!-- Kartu Overdue (Hitam = urgensi) -->
        <div class="relative flex flex-col justify-between gap-5 overflow-hidden rounded-[24px] bg-[#0B0F14] p-6 text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)]">
            <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#D9FF3A]/15 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D9FF3A] text-[#0B0F14]">
                    <i class="ti ti-alert-triangle text-xl"></i>
                </span>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-white/50">Terlewat (Overdue)</p>
            </div>

            <div class="relative flex items-end justify-between gap-3">
                <p class="text-4xl font-black leading-none md:text-5xl {{ $overdueTargets > 0 ? 'text-[#D9FF3A]' : 'text-white' }}">{{ $overdueTargets }}</p>
                @if($overdueTargets > 0)
                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-[#D9FF3A]/30 bg-[#D9FF3A]/10 px-2.5 py-1 text-[10px] font-bold text-[#D9FF3A]">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#D9FF3A]"></span> Perlu perhatian
                    </span>
                @endif
            </div>
        </div>
    </section>

    <!-- ===================== FILTER & PENCARIAN ===================== -->
    <section class="relative mb-6 rounded-[24px] border border-[#0B0F14]/10 bg-white p-5 shadow-[0_4px_24px_rgba(11,15,20,0.04)] sm:p-6">
        <div id="filterLoading" class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[24px] bg-[#F7F8FA]/70 opacity-0 backdrop-blur-sm transition-opacity duration-300">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-lg">
                <i class="ti ti-loader animate-spin text-2xl text-[#0245EC]"></i>
            </span>
        </div>

        <form id="filterForm" action="{{ route('targets.index') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <!-- Cari -->
            <div class="flex-1">
                <label for="searchInput" class="{{ $labelClass }}">Cari Target</label>
                <div class="relative">
                    <i class="ti ti-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-[#0B0F14]/35"></i>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Ketik nama target..." class="{{ $inputClass }} pl-10">
                </div>
            </div>

            <!-- Filter Status -->
            <div class="w-full lg:w-44">
                <label for="statusSelect" class="{{ $labelClass }}">Status Target</label>
                <div class="relative">
                    <select name="status" id="statusSelect" class="{{ $selectClass }}">
                        <option value="">Semua Status</option>
                        <option value="NOT_STARTED" {{ request('status') == 'NOT_STARTED' ? 'selected' : '' }}>Belum Mulai</option>
                        <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                        <option value="OVERDUE" {{ request('status') == 'OVERDUE' ? 'selected' : '' }}>Terlewat</option>
                    </select>
                    <i class="ti ti-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#0B0F14]/40"></i>
                </div>
            </div>

            <!-- Filter Scope -->
            <div class="w-full lg:w-44">
                <label for="scopeSelect" class="{{ $labelClass }}">Cakupan</label>
                <div class="relative">
                    <select name="scope" id="scopeSelect" class="{{ $selectClass }}">
                        <option value="">Semua Cakupan</option>
                        <option value="GLOBAL" {{ request('scope') == 'GLOBAL' ? 'selected' : '' }}>Global Project</option>
                        <option value="DIVISION" {{ request('scope') == 'DIVISION' ? 'selected' : '' }}>Hanya Divisi</option>
                    </select>
                    <i class="ti ti-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#0B0F14]/40"></i>
                </div>
            </div>

            <!-- Reset -->
            <div class="flex w-full gap-2 lg:w-auto">
                <button type="button" id="resetFilterBtn" title="Reset Filter" class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#D9FF3A] px-4 py-2.5 text-sm font-bold text-[#0B0F14] transition-all hover:opacity-90 lg:w-auto {{ request()->anyFilled(['search', 'status', 'scope']) ? '' : 'hidden' }}">
                    <i class="ti ti-refresh"></i> Reset
                </button>
            </div>
        </form>
    </section>

    <!-- ===================== TABEL TIMELINE ===================== -->
    <section id="tableContainer" class="overflow-hidden rounded-[24px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="flex flex-col gap-4 border-b border-[#0B0F14]/10 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-center gap-2.5">
                <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                <h2 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Rincian Timeline Target</h2>
            </div>

            <!-- Quick Filter Chips (sinkron dengan select status) -->
            <div class="flex flex-wrap items-center gap-2">
                @foreach($statusChips as $chipValue => $chipLabel)
                    @php $chipActive = ($currentStatus === $chipValue); @endphp
                    <button type="button" data-status-chip="{{ $chipValue }}" class="inline-flex items-center rounded-full border px-3.5 py-1.5 text-xs font-bold transition-all duration-200 {{ $chipActive ? 'border-[#0245EC] bg-[#0245EC] text-white shadow-[0_8px_20px_-8px_rgba(2,69,236,0.6)]' : 'border-[#0B0F14]/10 bg-white text-[#0B0F14]/60 hover:border-[#0245EC]/40 hover:text-[#0245EC]' }}">
                        {{ $chipLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        <div id="tableContent" class="transition-opacity duration-300">
            @include('targets.partials.table')
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm    = document.getElementById('filterForm');
            const tableContent  = document.getElementById('tableContent');
            const filterLoading = document.getElementById('filterLoading');
            const resetBtn      = document.getElementById('resetFilterBtn');
            const searchInput   = document.getElementById('searchInput');
            const statusSelect  = document.getElementById('statusSelect');
            const scopeSelect   = document.getElementById('scopeSelect');
            const statusChips   = document.querySelectorAll('[data-status-chip]');
            let typingTimer;

            /* Sinkronkan tampilan chip status yang aktif */
            function syncStatusChips(status) {
                statusChips.forEach(function (chip) {
                    const isActive = (chip.dataset.statusChip || '') === status;
                    chip.classList.toggle('border-[#0245EC]', isActive);
                    chip.classList.toggle('bg-[#0245EC]', isActive);
                    chip.classList.toggle('text-white', isActive);
                    chip.classList.toggle('shadow-[0_8px_20px_-8px_rgba(2,69,236,0.6)]', isActive);
                    chip.classList.toggle('border-[#0B0F14]/10', !isActive);
                    chip.classList.toggle('bg-white', !isActive);
                    chip.classList.toggle('text-[#0B0F14]/60', !isActive);
                });
            }

            function setLoading(isLoading) {
                tableContent.style.opacity = isLoading ? '0.3' : '1';
                filterLoading.classList.toggle('opacity-0', !isLoading);
                filterLoading.classList.toggle('pointer-events-none', !isLoading);
            }

            function fetchTableData(url) {
                setLoading(true);
                window.history.pushState({}, '', url);

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContent.innerHTML = html;
                        setLoading(false);

                        const searchParams = new URL(url, window.location.origin).searchParams;
                        syncStatusChips(searchParams.get('status') || '');

                        if (searchParams.toString() !== "") resetBtn.classList.remove('hidden');
                        else resetBtn.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        setLoading(false);
                    });
            }

            function triggerAjaxFetch() {
                const formData = new FormData(filterForm);
                const queryString = new URLSearchParams(formData).toString();
                fetchTableData(`{{ route('targets.index') }}?${queryString}`);
            }

            /* Auto-fetch: pencarian (debounce) & perubahan select */
            searchInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(triggerAjaxFetch, 500);
            });
            statusSelect.addEventListener('change', triggerAjaxFetch);
            scopeSelect.addEventListener('change', triggerAjaxFetch);

            /* Chip status = shortcut select status */
            statusChips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    statusSelect.value = this.dataset.statusChip;
                    triggerAjaxFetch();
                });
            });

            /* Pagination via AJAX */
            document.addEventListener('click', function (e) {
                const paginationLink = e.target.closest('#pagination-links a');
                if (paginationLink) {
                    e.preventDefault();
                    fetchTableData(paginationLink.href);
                }
            });

            /* Reset semua filter */
            resetBtn.addEventListener('click', function () {
                searchInput.value = '';
                statusSelect.value = '';
                scopeSelect.value = '';
                fetchTableData(`{{ route('targets.index') }}`);
            });
        });
    </script>
</x-app-layout>