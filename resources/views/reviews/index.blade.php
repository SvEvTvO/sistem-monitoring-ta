<x-app-layout>
    <!-- Header Halaman -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-text-primary">Tugas Review</h1>
            <p class="text-text-secondary mt-1">Daftar laporan dari anggota tim atau divisi di bawah pimpinanmu yang perlu diperiksa.</p>
        </div>
    </div>

    <!-- Kartu Filter & Search -->
    <div class="bg-neutral-surface border border-neutral-border rounded-[16px] p-5 shadow-sm mb-6 relative">
        
        <!-- Loading Overlay -->
        <div id="filterLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-10 rounded-[16px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <i class="ti ti-loader text-3xl text-primary animate-spin"></i>
        </div>

        <form id="filterForm" action="{{ route('reviews.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 lg:items-end">
            <!-- Cari Laporan -->
            <div class="flex-1">
                <label class="block text-sm font-semibold text-text-primary mb-1.5">Cari Laporan / Penulis</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ti ti-search text-text-muted"></i>
                    </div>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Ketik nama atau judul..." class="w-full pl-10 px-3 py-2 rounded-lg border-neutral-border focus:border-primary focus:ring-1 focus:ring-primary/20 text-sm transition-colors">
                </div>
            </div>
            
            <!-- Filter Status (Custom Dropdown) -->
            <div class="w-full lg:w-48 relative">
                <label class="block text-sm font-semibold text-text-primary mb-1.5">Status Laporan</label>
                <input type="hidden" name="status" id="statusInput" value="{{ request('status') }}">
                
                <button type="button" id="dropdownButton" class="w-full flex items-center justify-between bg-white px-3 py-2 text-sm rounded-lg border border-neutral-border focus:outline-none transition-colors shadow-sm text-left">
                    <span id="dropdownSelectedText" class="text-text-primary truncate pr-2">
                        @php
                            $statusLabels = [
                                '' => 'Semua Status',
                                'SUBMITTED' => 'Menunggu Review',
                                'REVIEWED' => 'Sedang Direview',
                                'APPROVED' => 'Telah Disetujui',
                                'REVISION_REQUIRED' => 'Perlu Revisi'
                            ];
                            echo $statusLabels[request('status')] ?? 'Semua Status';
                        @endphp
                    </span>
                    <i class="ti ti-chevron-down text-text-muted transition-transform duration-200" id="dropdownIcon"></i>
                </button>

                <div id="dropdownMenu" class="absolute z-20 w-full mt-1 bg-white border border-neutral-border rounded-lg shadow-lg opacity-0 invisible translate-y-1 transition-all duration-200 overflow-hidden">
                    <ul class="py-1 text-sm text-text-primary">
                        @foreach($statusLabels as $val =>$label)
                            <li>
                                <button type="button" onclick="selectStatus('{{ $val }}', '{{$label }}', this)" class="dropdown-item w-full text-left px-4 py-2 hover:bg-primary hover:text-white transition-colors {{ request('status') == $val ? 'bg-primary/10 text-primary font-bold' : '' }}">
                                    {{ $label }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Dari Tanggal -->
            <div class="w-full lg:w-36">
                <label class="block text-sm font-semibold text-text-primary mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" id="startDateInput" value="{{ request('start_date') }}" class="w-full px-3 py-2 rounded-lg border-neutral-border focus:border-primary focus:ring-1 focus:ring-primary/20 text-sm transition-colors">
            </div>

            <!-- Sampai Tanggal -->
            <div class="w-full lg:w-36">
                <label class="block text-sm font-semibold text-text-primary mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" id="endDateInput" value="{{ request('end_date') }}" class="w-full px-3 py-2 rounded-lg border-neutral-border focus:border-primary focus:ring-1 focus:ring-primary/20 text-sm transition-colors">
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="w-full lg:w-auto flex gap-2">
                <button type="submit" class="flex-1 lg:flex-none px-6 py-2 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-colors shadow-sm flex items-center justify-center">
                    <i class="ti ti-filter mr-2"></i> Filter
                </button>
                <button type="button" id="resetFilterBtn" class="px-4 py-2 bg-semantic-dangerBg text-semantic-danger font-bold rounded-xl hover:bg-semantic-danger/20 transition-colors flex items-center justify-center {{ request()->anyFilled(['search', 'status', 'start_date', 'end_date']) ? '' : 'hidden' }}" title="Reset Filter">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Kontainer Tabel (Ditarget oleh AJAX) -->
    <div id="tableContainer" class="bg-neutral-surface border border-neutral-border rounded-[16px] overflow-hidden shadow-sm transition-opacity duration-300">
        @include('reviews.partials.table')
    </div>

    <!-- Script AJAX dan Dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // LOGIKA DROPDOWN STATUS
            const dropdownBtn = document.getElementById('dropdownButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const dropdownIcon = document.getElementById('dropdownIcon');
            const statusInput = document.getElementById('statusInput');
            const selectedText = document.getElementById('dropdownSelectedText');

            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function (e) {
                    e.preventDefault(); e.stopPropagation();
                    dropdownMenu.classList.toggle('invisible');
                    dropdownMenu.classList.toggle('opacity-0');
                    dropdownMenu.classList.toggle('translate-y-1');
                    dropdownIcon.classList.toggle('rotate-180');
                    dropdownBtn.classList.toggle('border-primary');
                    dropdownBtn.classList.toggle('ring-1');
                    dropdownBtn.classList.toggle('ring-primary/20');
                });

                document.addEventListener('click', function (e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) closeDropdown();
                });
                
                function closeDropdown() {
                    dropdownMenu.classList.add('invisible', 'opacity-0', 'translate-y-1');
                    dropdownIcon.classList.remove('rotate-180');
                    dropdownBtn.classList.remove('border-primary', 'ring-1', 'ring-primary/20');
                }

                window.selectStatus = function (value, text, element) {
                    statusInput.value = value;
                    selectedText.innerText = text;
                    closeDropdown();
                    
                    document.querySelectorAll('.dropdown-item').forEach(btn => {
                        btn.classList.remove('bg-primary/10', 'text-primary', 'font-bold');
                    });
                    if(element) element.classList.add('bg-primary/10', 'text-primary', 'font-bold');
                    
                    triggerAjaxFetch(); // Auto filter saat dipilih
                };
            }

            // LOGIKA AJAX
            const filterForm = document.getElementById('filterForm');
            const tableContainer = document.getElementById('tableContainer');
            const filterLoading = document.getElementById('filterLoading');
            const resetBtn = document.getElementById('resetFilterBtn');
            let typingTimer;

            function fetchTableData(url) {
                tableContainer.style.opacity = '0.5';
                filterLoading.classList.remove('opacity-0', 'pointer-events-none');
                window.history.pushState({}, '', url);

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    tableContainer.style.opacity = '1';
                    filterLoading.classList.add('opacity-0', 'pointer-events-none');
                    
                    const searchParams = new URL(url, window.location.origin).searchParams;
                    if(searchParams.toString() !== "") resetBtn.classList.remove('hidden');
                    else resetBtn.classList.add('hidden');
                })
                .catch(error => console.error('Error fetching data:', error));
            }

            function triggerAjaxFetch() {
                const formData = new FormData(filterForm);
                const queryString = new URLSearchParams(formData).toString();
                fetchTableData(`{{ route('reviews.index') }}?${queryString}`);
            }

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                triggerAjaxFetch();
            });

            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(triggerAjaxFetch, 500);
            });

            document.getElementById('startDateInput').addEventListener('change', triggerAjaxFetch);
            document.getElementById('endDateInput').addEventListener('change', triggerAjaxFetch);

            document.addEventListener('click', function (e) {
                const paginationLink = e.target.closest('#pagination-links a');
                if (paginationLink) {
                    e.preventDefault();
                    fetchTableData(paginationLink.href);
                }
            });

            resetBtn.addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                document.getElementById('startDateInput').value = '';
                document.getElementById('endDateInput').value = '';
                
                statusInput.value = '';
                selectedText.innerText = 'Semua Status';
                document.querySelectorAll('.dropdown-item').forEach(btn => btn.classList.remove('bg-primary/10', 'text-primary', 'font-bold'));
                document.querySelector('.dropdown-item').classList.add('bg-primary/10', 'text-primary', 'font-bold');

                fetchTableData(`{{ route('reviews.index') }}`);
            });
        });
    </script>
</x-app-layout>