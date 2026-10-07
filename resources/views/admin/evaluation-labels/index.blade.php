<x-app-layout>
    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm"><i class="ti ti-tags text-lg"></i></span>
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-[#0B0F14]">Label Evaluasi Laporan</h1>
            <p class="mt-1 text-sm font-medium text-[#0B0F14]/50">Atur indikator penilaian untuk laporan progres mingguan anggota.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-semantic-successBg p-4 border border-semantic-success/20 flex items-start gap-3"><i class="ti ti-circle-check text-xl text-semantic-success"></i><p class="text-sm font-bold text-semantic-success">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-2xl bg-semantic-dangerBg p-4 border border-semantic-danger/20 flex items-start gap-3"><i class="ti ti-alert-triangle text-xl text-semantic-danger"></i><p class="text-sm font-bold text-semantic-danger">{{ session('error') }}</p></div>
    @endif

    <div x-data="{ showCreateModal: false, showEditModal: false, editLabel: {} }">
        <div class="mb-6 flex justify-end">
            <button @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-2xl bg-[#0245EC] px-6 py-3 text-sm font-extrabold text-white shadow-[0_10px_20px_-10px_rgba(2,69,236,0.8)] hover:-translate-y-0.5"><i class="ti ti-plus text-lg"></i> Tambah Label</button>
        </div>

        <div class="overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-sm">
            <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-[#0B0F14]/10 bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                            <th class="px-6 py-5">Urutan (Sort)</th>
                            <th class="px-6 py-5">Nama Label Evaluasi</th>
                            <th class="px-6 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0B0F14]/10">
                        @if($labels->count() > 0)
                            @foreach($labels as $l)
                                <tr class="hover:bg-[#F7F8FA]/50 group">
                                    <td class="px-6 py-4 text-sm font-black text-[#0B0F14]">{{ $l->sort_order }}</td>
                                    <td class="px-6 py-4"><span class="inline-flex rounded-lg border border-[#0B0F14]/10 bg-[#F7F8FA] px-3 py-1.5 text-xs font-bold text-[#0B0F14] shadow-sm">{{ $l->name }}</span></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="editLabel = { id: {{ $l->id }}, name: '{{ addslashes($l->name) }}', sort_order: '{{$l->sort_order }}' }; showEditModal = true" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC]"><i class="ti ti-edit text-lg"></i></button>
                                            <form action="{{ route('admin.evaluation-labels.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus label ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-semantic-dangerBg hover:text-semantic-danger"><i class="ti ti-trash text-lg"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm font-medium text-[#0B0F14]/40">Tidak ada data.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"><div x-show="showCreateModal" class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div><div class="flex min-h-screen items-center justify-center p-4"><div x-show="showCreateModal" class="relative w-full max-w-sm rounded-[24px] bg-white text-left shadow-2xl"><form action="{{ route('admin.evaluation-labels.store') }}" method="POST">@csrf <div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4"><h3 class="text-lg font-bold">Tambah Label</h3><button type="button" @click="showCreateModal = false"><i class="ti ti-x"></i></button></div><div class="space-y-4 p-6"><div><label class="mb-2 block text-sm font-bold">Nama Label</label><input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div><div><label class="mb-2 block text-sm font-bold">Urutan (Sort Order)</label><input type="number" name="sort_order" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div></div><div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showCreateModal = false" class="rounded-xl px-5 py-2.5 text-sm font-bold border">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div></form></div></div></div>

        <!-- Modal Edit -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"><div x-show="showEditModal" class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div><div class="flex min-h-screen items-center justify-center p-4"><div x-show="showEditModal" class="relative w-full max-w-sm rounded-[24px] bg-white text-left shadow-2xl"><form :action="`/admin/evaluation-labels/${editLabel.id}`" method="POST">@csrf @method('PUT')<div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4"><h3 class="text-lg font-bold">Edit Label</h3><button type="button" @click="showEditModal = false"><i class="ti ti-x"></i></button></div><div class="space-y-4 p-6"><div><label class="mb-2 block text-sm font-bold">Nama Label</label><input type="text" name="name" x-model="editLabel.name" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div><div><label class="mb-2 block text-sm font-bold">Urutan</label><input type="number" name="sort_order" x-model="editLabel.sort_order" required class="w-full rounded-xl border border-[#0B0F14]/10 px-4 py-3 text-sm focus:border-[#0245EC] outline-none"></div></div><div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10"><button type="button" @click="showEditModal = false" class="rounded-xl px-5 py-2.5 text-sm font-bold border">Batal</button><button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white">Simpan</button></div></form></div></div></div>
    </div>
</x-app-layout>
