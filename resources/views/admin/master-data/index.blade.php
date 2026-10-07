<x-app-layout>
    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm">
                    <i class="ti ti-database text-lg"></i>
                </span>
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-[#0B0F14]">Pusat Data Master</h1>
            <p class="mt-1 text-sm font-medium text-[#0B0F14]/50">Kelola entitas fundamental seperti Tahun Ajaran, Jurusan, Kelas, dan Rombel.</p>
        </div>
    </header>

    <!-- Notifikasi Flash Message -->
    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-semantic-successBg p-4 border border-semantic-success/20 flex items-start gap-3">
            <i class="ti ti-circle-check text-xl text-semantic-success"></i>
            <p class="text-sm font-bold text-semantic-success">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-2xl bg-semantic-dangerBg p-4 border border-semantic-danger/20 flex items-start gap-3">
            <i class="ti ti-alert-triangle text-xl text-semantic-danger"></i>
            <p class="text-sm font-bold text-semantic-danger">{{ session('error') }}</p>
        </div>
    @endif

    <!-- ===================== TAB NAVIGATION & CONTENT ===================== -->
    <div x-data="{ 
        activeTab: 'academic_years',
        
        // Modal Create States
        showYearModal: false, showDeptModal: false, showLevelModal: false, showClassModal: false,
        
        // Modal Edit States & Data
        showEditYearModal: false, editYear: {},
        showEditDeptModal: false, editDept: {},
        showEditLevelModal: false, editLevel: {},
        showEditClassModal: false, editClass: {},

        // Helper open edit modals
        openEditYear(data) { this.editYear = data; this.showEditYearModal = true; },
        openEditDept(data) { this.editDept = data; this.showEditDeptModal = true; },
        openEditLevel(data) { this.editLevel = data; this.showEditLevelModal = true; },
        openEditClass(data) { this.editClass = data; this.showEditClassModal = true; }
    }" class="flex flex-col gap-6 lg:flex-row lg:items-start">

        <!-- Sidebar Tabs -->
        <div class="w-full shrink-0 space-y-2 lg:w-64 lg:sticky lg:top-6">
            <button @click="activeTab = 'academic_years'" :class="activeTab === 'academic_years' ? 'bg-[#0B0F14] text-[#D9FF3A] shadow-md' : 'bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] border border-[#0B0F14]/10'" class="flex w-full items-center justify-between rounded-2xl px-5 py-4 text-left font-extrabold transition-all duration-200">
                <span class="flex items-center gap-3"><i class="ti ti-calendar-event text-xl"></i> Tahun Ajaran</span>
                <i class="ti ti-chevron-right" x-show="activeTab === 'academic_years'"></i>
            </button>
            <button @click="activeTab = 'departments'" :class="activeTab === 'departments' ? 'bg-[#0B0F14] text-[#D9FF3A] shadow-md' : 'bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] border border-[#0B0F14]/10'" class="flex w-full items-center justify-between rounded-2xl px-5 py-4 text-left font-extrabold transition-all duration-200">
                <span class="flex items-center gap-3"><i class="ti ti-books text-xl"></i> Jurusan (Dept)</span>
                <i class="ti ti-chevron-right" x-show="activeTab === 'departments'"></i>
            </button>
            <!-- Label LEVEL diganti jadi KELAS -->
            <button @click="activeTab = 'levels'" :class="activeTab === 'levels' ? 'bg-[#0B0F14] text-[#D9FF3A] shadow-md' : 'bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] border border-[#0B0F14]/10'" class="flex w-full items-center justify-between rounded-2xl px-5 py-4 text-left font-extrabold transition-all duration-200">
                <span class="flex items-center gap-3"><i class="ti ti-stairs-up text-xl"></i> Kelas (X, XI, XII)</span>
                <i class="ti ti-chevron-right" x-show="activeTab === 'levels'"></i>
            </button>
            <!-- Label KELAS diganti jadi ROMBEL -->
            <button @click="activeTab = 'classes'" :class="activeTab === 'classes' ? 'bg-[#0B0F14] text-[#D9FF3A] shadow-md' : 'bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] border border-[#0B0F14]/10'" class="flex w-full items-center justify-between rounded-2xl px-5 py-4 text-left font-extrabold transition-all duration-200">
                <span class="flex items-center gap-3"><i class="ti ti-chalkboard text-xl"></i> Rombongan Belajar</span>
                <i class="ti ti-chevron-right" x-show="activeTab === 'classes'"></i>
            </button>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)] min-h-[500px]">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

            <!-- TAB 1: TAHUN AJARAN -->
            <div x-show="activeTab === 'academic_years'" x-transition.opacity.duration.300ms>
                <div class="flex items-center justify-between border-b border-[#0B0F14]/10 p-6 sm:p-8">
                    <div>
                        <h2 class="text-lg font-black text-[#0B0F14]">Tahun Ajaran</h2>
                        <p class="text-sm font-medium text-[#0B0F14]/50">Kelola periode aktif kegiatan belajar mengajar.</p>
                    </div>
                    <button @click="showYearModal = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0245EC] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition-all hover:bg-[#0245EC]/90">
                        <i class="ti ti-plus text-base"></i> Tambah
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                                <th class="px-6 py-4">Nama Tahun Ajaran</th>
                                <th class="px-6 py-4">Periode Tanggal</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0B0F14]/10">
                            @if($academicYears->count() > 0)
                                @foreach($academicYears as $year)
                                    <tr class="transition-colors hover:bg-[#F7F8FA]/50 group">
                                        <td class="px-6 py-4 text-sm font-black text-[#0B0F14]">{{ $year->name }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-[#0B0F14]/60">
                                            {{ \Carbon\Carbon::parse($year->start_date)->format('M Y') }} — {{ \Carbon\Carbon::parse($year->end_date)->format('M Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($year->is_active)
                                                <span class="inline-flex items-center rounded-full bg-[#D9FF3A]/20 px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#0B0F14]">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-[#0B0F14]/10 px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#0B0F14]/50">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditYear({ id: {{ $year->id }}, name: '{{ $year->name }}', start_date: '{{ \Carbon\Carbon::parse($year->start_date)->format('Y-m-d') }}', end_date: '{{ \Carbon\Carbon::parse($year->end_date)->format('Y-m-d') }}', is_active: {{ $year->is_active ? 'true' : 'false' }} })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC] transition-colors"><i class="ti ti-edit text-lg"></i></button>
                                                <form action="{{ route('admin.master.academic-years.destroy', $year->id) }}" method="POST" onsubmit="return confirm('Hapus Tahun Ajaran ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-semantic-dangerBg hover:text-semantic-danger transition-colors"><i class="ti ti-trash text-lg"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="p-8 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data Tahun Ajaran.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: JURUSAN -->
            <div x-show="activeTab === 'departments'" x-cloak x-transition.opacity.duration.300ms>
                <div class="flex items-center justify-between border-b border-[#0B0F14]/10 p-6 sm:p-8">
                    <div>
                        <h2 class="text-lg font-black text-[#0B0F14]">Jurusan / Departemen</h2>
                    </div>
                    <button @click="showDeptModal = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0245EC] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition-all hover:bg-[#0245EC]/90">
                        <i class="ti ti-plus text-base"></i> Tambah
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                                <th class="px-6 py-4">Kode Jurusan</th>
                                <th class="px-6 py-4">Nama Lengkap Jurusan</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0B0F14]/10">
                            @if($departments->count() > 0)
                                @foreach($departments as $dept)
                                    <tr class="transition-colors hover:bg-[#F7F8FA]/50 group">
                                        <td class="px-6 py-4"><span class="inline-flex rounded-lg bg-[#0B0F14]/5 px-3 py-1 text-xs font-black text-[#0B0F14] border border-[#0B0F14]/10">{{ $dept->code }}</span></td>
                                        <td class="px-6 py-4 text-sm font-bold text-[#0B0F14]">{{ $dept->name }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditDept({ id: {{ $dept->id }}, code: '{{ $dept->code }}', name: '{{ $dept->name }}' })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC] transition-colors"><i class="ti ti-edit text-lg"></i></button>
                                                <form action="{{ route('admin.master.departments.destroy', $dept->id) }}" method="POST" onsubmit="return confirm('Hapus Jurusan ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-semantic-dangerBg hover:text-semantic-danger transition-colors"><i class="ti ti-trash text-lg"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="3" class="p-8 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data Jurusan.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: KELAS (Dulu Tingkat/Level) -->
            <div x-show="activeTab === 'levels'" x-cloak x-transition.opacity.duration.300ms>
                <div class="flex items-center justify-between border-b border-[#0B0F14]/10 p-6 sm:p-8">
                    <div>
                        <h2 class="text-lg font-black text-[#0B0F14]">Kelas Dasar</h2>
                    </div>
                    <button @click="showLevelModal = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0245EC] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition-all hover:bg-[#0245EC]/90">
                        <i class="ti ti-plus text-base"></i> Tambah Kelas
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                                <th class="px-6 py-4">Urutan (Sort Order)</th>
                                <th class="px-6 py-4">Nama Kelas</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0B0F14]/10">
                            @if($levels->count() > 0)
                                @foreach($levels as $level)
                                    <tr class="transition-colors hover:bg-[#F7F8FA]/50 group">
                                        <td class="px-6 py-4 text-sm font-black text-[#0B0F14]">{{ $level->sort_order }}</td>
                                        <td class="px-6 py-4 text-sm font-bold text-[#0B0F14]">Kelas {{ $level->name }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditLevel({ id: {{ $level->id }}, sort_order: '{{ $level->sort_order }}', name: '{{ $level->name }}' })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC] transition-colors"><i class="ti ti-edit text-lg"></i></button>
                                                <form action="{{ route('admin.master.levels.destroy', $level->id) }}" method="POST" onsubmit="return confirm('Hapus data Kelas ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-semantic-dangerBg hover:text-semantic-danger transition-colors"><i class="ti ti-trash text-lg"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="3" class="p-8 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data Kelas.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: ROMBEL (Dulu Kelas) -->
            <div x-show="activeTab === 'classes'" x-cloak x-transition.opacity.duration.300ms>
                <div class="flex items-center justify-between border-b border-[#0B0F14]/10 p-6 sm:p-8">
                    <div>
                        <h2 class="text-lg font-black text-[#0B0F14]">Rombongan Belajar (Rombel)</h2>
                    </div>
                    <button @click="showClassModal = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0245EC] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition-all hover:bg-[#0245EC]/90">
                        <i class="ti ti-plus text-base"></i> Tambah Rombel
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                                <th class="px-6 py-4">Nama Rombel</th>
                                <th class="px-6 py-4">Jurusan & Kelas</th>
                                <th class="px-6 py-4">Tahun Ajaran</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0B0F14]/10">
                            @if($classes->count() > 0)
                                @foreach($classes as $c)
                                    <tr class="transition-colors hover:bg-[#F7F8FA]/50 group">
                                        <td class="px-6 py-4 text-sm font-black text-[#0B0F14]">{{ $c->name }}</td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-[#0B0F14]">{{ $c->department->name ?? '-' }}</p>
                                            <p class="text-xs font-semibold text-[#0B0F14]/50">Kelas {{ $c->level->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-[#0B0F14]/60">{{ $c->academicYear->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditClass({ id: {{ $c->id }}, name: '{{ $c->name }}', level_id: '{{$c->level_id }}', department_id: '{{ $c->department_id }}', academic_year_id: '{{$c->academic_year_id }}' })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC] transition-colors"><i class="ti ti-edit text-lg"></i></button>
                                                <form action="{{ route('admin.master.classes.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus Rombel ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-semantic-dangerBg hover:text-semantic-danger transition-colors"><i class="ti ti-trash text-lg"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="p-8 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data Rombel.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- KUMPULAN MODAL CREATE & EDIT (ALPINE JS) -->
        <!-- ========================================== -->

        <!-- CREATE MODALS -->
        <!-- Modal 1: Tambah Tahun Ajaran -->
        <div x-show="showYearModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showYearModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showYearModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form action="{{ route('admin.master.academic-years.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-calendar-event mr-2 text-[#0245EC]"></i> Tambah Tahun Ajaran</h3><button type="button" @click="showYearModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Nama Tahun Ajaran</label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Tanggal Mulai</label><input type="date" name="start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Tanggal Selesai</label><input type="date" name="end_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            </div>
                            <label class="mt-4 flex items-center gap-3 cursor-pointer"><input type="checkbox" name="is_active" value="1" checked class="h-5 w-5 rounded border-[#0B0F14]/20 text-[#0245EC]"><span class="text-sm font-bold">Tahun Ajaran Aktif</span></label>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showYearModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 2: Tambah Jurusan -->
        <div x-show="showDeptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showDeptModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showDeptModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form action="{{ route('admin.master.departments.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-books mr-2 text-[#0245EC]"></i> Tambah Jurusan</h3><button type="button" @click="showDeptModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Kode Jurusan</label><input type="text" name="code" required class="w-full uppercase rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div><label class="mb-2 block text-sm font-bold">Nama Lengkap</label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showDeptModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 3: Tambah Level (Sekarang Kelas) -->
        <div x-show="showLevelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showLevelModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showLevelModal" x-transition.scale class="relative w-full max-w-sm overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form action="{{ route('admin.master.levels.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-stairs-up mr-2 text-[#0245EC]"></i> Tambah Kelas</h3><button type="button" @click="showLevelModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Urutan (Sort Order)</label><input type="number" name="sort_order" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div><label class="mb-2 block text-sm font-bold">Nama Kelas Dasar</label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showLevelModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 4: Tambah Kelas (Sekarang Rombel) -->
        <div x-show="showClassModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showClassModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showClassModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form action="{{ route('admin.master.classes.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-chalkboard mr-2 text-[#0245EC]"></i> Tambah Rombel</h3><button type="button" @click="showClassModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Nama Rombel</label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Kelas Dasar</label>
                                    <select name="level_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        @foreach($levels as $l) <option value="{{ $l->id }}">Kelas {{ $l->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div><label class="mb-2 block text-sm font-bold">Jurusan</label>
                                    <select name="department_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        @foreach($departments as $d) <option value="{{ $d->id }}">{{ $d->code }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                            <div><label class="mb-2 block text-sm font-bold">Tahun Ajaran</label>
                                <select name="academic_year_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                    <option value="" disabled selected>-- Pilih --</option>
                                    @foreach($academicYears as $y) <option value="{{ $y->id }}">{{ $y->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showClassModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT MODALS (Dynamic Binding) -->
        <!-- Edit Tahun Ajaran -->
        <div x-show="showEditYearModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditYearModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showEditYearModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form :action="`/admin/master-data/academic-years/${editYear.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-edit mr-2 text-[#0245EC]"></i> Edit Tahun Ajaran</h3><button type="button" @click="showEditYearModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Nama Tahun Ajaran</label><input type="text" name="name" x-model="editYear.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Tanggal Mulai</label><input type="date" name="start_date" x-model="editYear.start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Tanggal Selesai</label><input type="date" name="end_date" x-model="editYear.end_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            </div>
                            <label class="mt-4 flex items-center gap-3 cursor-pointer"><input type="checkbox" name="is_active" value="1" x-model="editYear.is_active" class="h-5 w-5 rounded border-[#0B0F14]/20 text-[#0245EC]"><span class="text-sm font-bold">Tahun Ajaran Aktif</span></label>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showEditYearModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Jurusan -->
        <div x-show="showEditDeptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditDeptModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showEditDeptModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form :action="`/admin/master-data/departments/${editDept.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-edit mr-2 text-[#0245EC]"></i> Edit Jurusan</h3><button type="button" @click="showEditDeptModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Kode Jurusan</label><input type="text" name="code" x-model="editDept.code" required class="w-full uppercase rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div><label class="mb-2 block text-sm font-bold">Nama Lengkap</label><input type="text" name="name" x-model="editDept.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showEditDeptModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Level (Sekarang Kelas) -->
        <div x-show="showEditLevelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditLevelModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showEditLevelModal" x-transition.scale class="relative w-full max-w-sm overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form :action="`/admin/master-data/levels/${editLevel.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-edit mr-2 text-[#0245EC]"></i> Edit Kelas</h3><button type="button" @click="showEditLevelModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Urutan</label><input type="number" name="sort_order" x-model="editLevel.sort_order" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div><label class="mb-2 block text-sm font-bold">Nama Kelas</label><input type="text" name="name" x-model="editLevel.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showEditLevelModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Kelas (Sekarang Rombel) -->
        <div x-show="showEditClassModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditClassModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showEditClassModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form :action="`/admin/master-data/classes/${editClass.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between bg-[#F7F8FA]/50 px-6 py-4 border-b border-[#0B0F14]/10"><h3 class="text-lg font-bold"><i class="ti ti-edit mr-2 text-[#0245EC]"></i> Edit Rombel</h3><button type="button" @click="showEditClassModal = false"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="p-6 space-y-4">
                            <div><label class="mb-2 block text-sm font-bold">Nama Rombel</label><input type="text" name="name" x-model="editClass.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Kelas Dasar</label>
                                    <select name="level_id" x-model="editClass.level_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                        @foreach($levels as $l) <option value="{{ $l->id }}">Kelas {{ $l->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div><label class="mb-2 block text-sm font-bold">Jurusan</label>
                                    <select name="department_id" x-model="editClass.department_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                        @foreach($departments as $d) <option value="{{ $d->id }}">{{ $d->code }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                            <div><label class="mb-2 block text-sm font-bold">Tahun Ajaran</label>
                                <select name="academic_year_id" x-model="editClass.academic_year_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm outline-none">
                                    @foreach($academicYears as $y) <option value="{{ $y->id }}">{{ $y->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showEditClassModal = false" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold border border-[#0B0F14]/10">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- Penutup <div x-data> Induk -->
</x-app-layout>
