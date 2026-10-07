<x-app-layout>
    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm">
                    <i class="ti ti-rocket text-lg"></i>
                </span>
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-[#0B0F14]">Manajemen Project</h1>
            <p class="mt-1 text-sm font-medium text-[#0B0F14]/50">Inisiasi project baru, tentukan kelas, dan tunjuk Ketua Project.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-semantic-successBg p-4 border border-semantic-success/20 flex items-start gap-3">
            <i class="ti ti-circle-check text-xl text-semantic-success"></i><p class="text-sm font-bold text-semantic-success">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-2xl bg-semantic-dangerBg p-4 border border-semantic-danger/20 flex items-start gap-3">
            <i class="ti ti-alert-triangle text-xl text-semantic-danger"></i><p class="text-sm font-bold text-semantic-danger">{{ session('error') }}</p>
        </div>
    @endif

    <!-- ===================== WRAPPER & ALPINE DATA ===================== -->
    <div x-data="{ 
        showCreateModal: false, 
        showEditModal: false, 
        editProject: {},
        openEdit(data) {
            this.editProject = data;
            this.showEditModal = true;
        }
    }">

        <!-- Toolbar: Pencarian & Tombol Tambah -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('admin.projects.index') }}" method="GET" class="w-full sm:w-96">
                <div class="relative">
                    <i class="ti ti-search absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama project..." class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3 pl-11 pr-4 text-sm font-medium text-[#0B0F14] outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                </div>
            </form>
            <button @click="showCreateModal = true" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0245EC] px-6 py-3 text-sm font-extrabold text-white shadow-[0_10px_20px_-10px_rgba(2,69,236,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[0_15px_25px_-10px_rgba(2,69,236,1)] sm:w-auto">
                <i class="ti ti-folder-plus text-lg"></i> Inisiasi Project
            </button>
        </div>

        <!-- Tabel Data Project -->
        <div class="overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-[#0B0F14]/10 bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                            <th class="px-6 py-5">Nama Project & Kelas</th>
                            <th class="px-6 py-5">Ketua Project</th>
                            <th class="px-6 py-5">Periode & Status</th>
                            <th class="px-6 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0B0F14]/10">
                        @if($projects->count() > 0)
                            @foreach($projects as $p)
                                <tr class="transition-colors hover:bg-[#F7F8FA]/50 group">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-black text-[#0B0F14] mb-1">{{ $p->name }}</p>
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-[#0B0F14]/10 bg-white px-2.5 py-1 text-xs font-bold text-[#0B0F14]/70 shadow-sm">
                                            <i class="ti ti-chalkboard text-[#0245EC]"></i> {{ $p->schoolClass->name ?? 'Tanpa Kelas' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0B0F14] text-xs font-bold text-[#D9FF3A]">
                                                {{ strtoupper(substr($p->leader->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="truncate text-sm font-bold text-[#0B0F14]">{{ $p->leader->name ?? 'Belum Ditunjuk' }}</p>
                                                <p class="text-[10px] font-extrabold uppercase text-[#0B0F14]/40">Ketua Project</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-medium text-[#0B0F14]/60 mb-1.5"><i class="ti ti-calendar mr-1"></i> {{ \Carbon\Carbon::parse($p->actual_start_date)->format('d M y') }} - {{ \Carbon\Carbon::parse($p->end_date)->format('d M y') }}</p>
                                        @if($p->status === 'ACTIVE')
                                            <span class="inline-flex items-center rounded-full bg-[#D9FF3A]/20 px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#0B0F14]"><span class="h-1.5 w-1.5 rounded-full bg-[#0B0F14] mr-1.5 animate-pulse"></span>Aktif</span>
                                        @elseif($p->status === 'PLANNED')
                                            <span class="inline-flex items-center rounded-full bg-[#0245EC]/10 px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#0245EC]">Terencana</span>
                                        @elseif($p->status === 'COMPLETED')
                                            <span class="inline-flex items-center rounded-full bg-semantic-successBg border border-semantic-success/20 px-2.5 py-1 text-[10px] font-extrabold uppercase text-semantic-success">Selesai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#0B0F14]/10 px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#0B0F14]/50">{{ $p->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEdit({ id: {{ $p->id }}, name: '{{ addslashes($p->name) }}', class_id: '{{$p->class_id }}', project_leader_id: '{{ $p->project_leader_id }}', actual_start_date: '{{ \Carbon\Carbon::parse($p->actual_start_date)->format('Y-m-d') }}', week_1_start_date: '{{ \Carbon\Carbon::parse($p->week_1_start_date)->format('Y-m-d') }}', end_date: '{{ \Carbon\Carbon::parse($p->end_date)->format('Y-m-d') }}', status: '{{ $p->status }}', description: '{{ addslashes($p->description ?? '') }}' })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]"><i class="ti ti-edit text-lg"></i></button>
                                            <form action="{{ route('admin.projects.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus project ini secara permanen?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-semantic-dangerBg hover:text-semantic-danger"><i class="ti ti-trash text-lg"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data project yang ditemukan.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if($projects->hasPages()) <div class="border-t border-[#0B0F14]/10 bg-white p-4">{{ $projects->links() }}</div> @endif
        </div>

        <!-- ========================================== -->
        <!-- MODAL CREATE PROJECT -->
        <!-- ========================================== -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showCreateModal" x-transition.scale class="relative w-full max-w-2xl overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form action="{{ route('admin.projects.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4"><h3 class="flex items-center text-lg font-bold text-[#0B0F14]"><i class="ti ti-folder-plus mr-2 text-[#0245EC]"></i> Inisiasi Project Baru</h3><button type="button" @click="showCreateModal = false" class="text-[#0B0F14]/40 hover:text-[#0B0F14]"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="space-y-5 p-6 max-h-[70vh] overflow-y-auto no-scrollbar">
                            <div><label class="mb-2 block text-sm font-bold">Nama Project <span class="text-red-500">*</span></label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm outline-none focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10"></div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Tugaskan ke Kelas <span class="text-red-500">*</span></label>
                                    <select name="class_id" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm outline-none focus:border-[#0245EC]">
                                        <option value="" disabled selected>-- Pilih Kelas --</option>
                                        @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name }} ({{$c->department->code ?? '-' }})</option> @endforeach
                                    </select>
                                </div>
                                <div><label class="mb-2 block text-sm font-bold">Tunjuk Ketua Project <span class="text-red-500">*</span></label>
                                    <select name="project_leader_id" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm outline-none focus:border-[#0245EC]">
                                        <option value="" disabled selected>-- Pilih Pengguna --</option>
                                        @foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Tgl Mulai Aktual <span class="text-red-500">*</span></label><input type="date" name="actual_start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Tgl Mulai Minggu 1 <span class="text-red-500">*</span></label><input type="date" name="week_1_start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Tgl Berakhir <span class="text-red-500">*</span></label><input type="date" name="end_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Status Awal <span class="text-red-500">*</span></label>
                                    <select name="status" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm outline-none focus:border-[#0245EC]">
                                        <option value="PLANNED" selected>PLANNED (Terencana)</option>
                                        <option value="ACTIVE">ACTIVE (Sedang Berjalan)</option>
                                    </select>
                                </div>
                            </div>

                            <div><label class="mb-2 block text-sm font-bold">Deskripsi Singkat (Opsional)</label><textarea name="description" rows="2" class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm outline-none resize-none focus:border-[#0245EC]"></textarea></div>
                        </div>
                        <div class="flex justify-end gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] px-6 py-4"><button type="button" @click="showCreateModal = false" class="rounded-xl border border-[#0B0F14]/10 bg-white px-5 py-2.5 text-sm font-bold text-[#0B0F14]/60">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Project</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL EDIT PROJECT -->
        <!-- ========================================== -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
                <div x-show="showEditModal" x-transition.scale class="relative w-full max-w-2xl overflow-hidden rounded-[24px] bg-white text-left shadow-2xl sm:my-8">
                    <form :action="`/admin/projects/${editProject.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4"><h3 class="flex items-center text-lg font-bold text-[#0B0F14]"><i class="ti ti-edit mr-2 text-[#0245EC]"></i> Edit Data Project</h3><button type="button" @click="showEditModal = false" class="text-[#0B0F14]/40 hover:text-[#0B0F14]"><i class="ti ti-x text-xl"></i></button></div>
                        <div class="space-y-5 p-6 max-h-[70vh] overflow-y-auto no-scrollbar">
                            <div><label class="mb-2 block text-sm font-bold">Nama Project <span class="text-red-500">*</span></label><input type="text" name="name" x-model="editProject.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Kelas <span class="text-red-500">*</span></label><select name="class_id" x-model="editProject.class_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none">@foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach</select></div>
                                <div><label class="mb-2 block text-sm font-bold">Ketua Project <span class="text-red-500">*</span></label><select name="project_leader_id" x-model="editProject.project_leader_id" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none">@foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach</select></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Mulai Aktual <span class="text-red-500">*</span></label><input type="date" name="actual_start_date" x-model="editProject.actual_start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Mulai M1 <span class="text-red-500">*</span></label><input type="date" name="week_1_start_date" x-model="editProject.week_1_start_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                                <div><label class="mb-2 block text-sm font-bold">Berakhir <span class="text-red-500">*</span></label><input type="date" name="end_date" x-model="editProject.end_date" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="mb-2 block text-sm font-bold">Status <span class="text-red-500">*</span></label>
                                    <select name="status" x-model="editProject.status" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none">
                                        <option value="PLANNED">PLANNED</option>
                                        <option value="ACTIVE">ACTIVE</option>
                                        <option value="ON_HOLD">ON HOLD</option>
                                        <option value="COMPLETED">COMPLETED</option>
                                    </select>
                                </div>
                            </div>
                            <div><label class="mb-2 block text-sm font-bold">Deskripsi Singkat</label><textarea name="description" x-model="editProject.description" rows="2" class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm resize-none focus:border-[#0245EC] outline-none"></textarea></div>
                        </div>
                        <div class="flex justify-end gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] px-6 py-4"><button type="button" @click="showEditModal = false" class="rounded-xl border border-[#0B0F14]/10 bg-white px-5 py-2.5 text-sm font-bold text-[#0B0F14]/60">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
