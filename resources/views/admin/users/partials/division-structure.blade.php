@if($project)
    <!-- Header Project -->
    <div class="mb-6 p-5 rounded-[20px] bg-[#0B0F14] text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[#D9FF3A]/20 blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#D9FF3A]/30 bg-[#D9FF3A]/10 px-2.5 py-1 text-[10px] font-bold text-[#D9FF3A] mb-2 uppercase tracking-widest">
                Project Aktif
            </span>
            <h2 class="text-2xl font-black text-white">{{ $project->name }}</h2>
            <div class="flex items-center mt-2 gap-3 text-sm text-white/70">
                <span class="flex items-center gap-1.5"><i class="ti ti-star-filled text-[#D9FF3A]"></i> Ketua: <strong class="text-white">{{ $project->leader->name ?? 'Belum ada' }}</strong></span>
                <span>•</span>
                <span class="flex items-center gap-1.5"><i class="ti ti-users-group"></i> Total {{ $project->divisions->count() }} Divisi</span>
            </div>
        </div>
    </div>

    <!-- Struktur Divisi (Tab Navigation style) -->
    <div x-data="{ activeDiv: {{ $project->divisions->count() > 0 ? $project->divisions->first()->id : 'null' }} }">
        @if($project->divisions->count() > 0)
            <!-- Tab Buttons -->
            <div class="flex gap-3 overflow-x-auto pb-4 mb-2 no-scrollbar scroll-smooth">
                @foreach($project->divisions as $division)
                    <button @click="activeDiv = {{ $division->id }}"
                            :class="activeDiv === {{ $division->id }} ? 'bg-[#0B0F14] text-[#D9FF3A] shadow-md border-[#0B0F14]' : 'bg-white text-[#0B0F14]/60 border-[#0B0F14]/10 hover:bg-[#F7F8FA]'"
                            class="px-5 py-2.5 rounded-[14px] border font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                        <i class="ti ti-users-group text-lg"></i>
                        {{ $division->name }}
                        <span :class="activeDiv === {{ $division->id }} ? 'bg-white/20 text-white' : 'bg-[#F7F8FA] text-[#0B0F14]/50'" class="px-2 py-0.5 rounded-lg text-[10px] ml-1 transition-colors">
                            {{ $division->members->count() }}
                        </span>
                    </button>
                @endforeach
            </div>

            <!-- Tab Contents (Daftar Member per Divisi) -->
            <div class="bg-white border border-[#0B0F14]/10 rounded-[24px] shadow-[0_4px_24px_rgba(11,15,20,0.04)] overflow-hidden min-h-[300px]">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

                @foreach($project->divisions as $division)
                    <div x-show="activeDiv === {{ $division->id }}" x-cloak x-transition.opacity.duration.300ms class="p-6">
                        <div class="mb-5 flex items-center gap-3">
                            <h3 class="text-xl font-extrabold text-[#0B0F14]">Anggota Divisi {{ $division->name }}</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @forelse($division->members as $member)
                                @php
                                    $isLeader = $division->leader_user_id == $member->user->id;
                                    $isProjectLeader = $project->project_leader_id == $member->user->id;
                                @endphp

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border transition-all hover:shadow-md relative overflow-hidden {{ $isLeader ? 'border-[#0245EC]/30 bg-[#0245EC]/5 shadow-sm' : 'border-[#0B0F14]/10 bg-white hover:border-[#0245EC]/30' }}">

                                    <div class="flex items-center gap-3 z-10 overflow-hidden">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-black shadow-sm {{ $isLeader ? 'bg-[#0245EC] text-white ring-2 ring-[#0245EC]/20' : 'bg-[#0B0F14] text-[#D9FF3A]' }}">
                                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                        </div>
                                        <div class="overflow-hidden flex-1">
                                            <p class="truncate text-sm font-black text-[#0B0F14] group-hover:text-[#0245EC]">{{ $member->user->name }}</p>
                                            <p class="truncate text-[10px] font-semibold text-[#0B0F14]/50">{{ $member->user->email }}</p>

                                            <!-- Badges -->
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                @if($isProjectLeader)
                                                    <span class="inline-block px-2 py-0.5 rounded border border-[#0B0F14]/20 bg-[#0B0F14] text-[9px] font-extrabold text-[#D9FF3A] uppercase tracking-wider shadow-sm">Ketua Project</span>
                                                @endif
                                                @if($isLeader)
                                                    <span class="inline-block px-2 py-0.5 rounded border border-[#0245EC]/20 bg-[#0245EC] text-[9px] font-extrabold text-white uppercase tracking-wider shadow-sm">Ketua Divisi</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Edit & Hapus Akun Admin -->
                                    <div class="flex sm:flex-col items-center justify-end sm:border-l sm:border-[#0B0F14]/10 sm:pl-3 gap-1 z-10">
                                        <button @click="openEditModal({ id: {{ $member->user->id }}, name: '{{ addslashes($member->user->name) }}', username: '{{ addslashes($member->user->username) }}', email: '{{ addslashes($member->user->email) }}' })" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 hover:bg-[#0245EC]/10 hover:text-[#0245EC] transition-colors" title="Edit Data Akun">
                                            <i class="ti ti-edit text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-10">
                                    <p class="text-sm font-medium text-[#0B0F14]/40">Divisi ini kosong.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center rounded-[24px] border border-[#0B0F14]/10 bg-white">
                <i class="ti ti-sitemap text-4xl text-[#0B0F14]/20 mb-3 block"></i>
                <p class="text-sm font-bold text-[#0B0F14]">Struktur Divisi Kosong</p>
                <p class="text-xs text-[#0B0F14]/50 mt-1">Ketua Project belum membuat divisi untuk project ini.</p>
            </div>
        @endif
    </div>
@else
    <!-- Jika Rombel tersebut belum punya project -->
    <div class="flex flex-col items-center justify-center p-12 text-center rounded-[24px] border border-[#0B0F14]/10 bg-white shadow-sm">
        <div class="w-16 h-16 bg-[#0B0F14]/5 text-[#0B0F14]/30 rounded-2xl flex items-center justify-center mb-4">
            <i class="ti ti-rocket-off text-3xl"></i>
        </div>
        <h3 class="text-xl font-extrabold text-[#0B0F14] mb-2">Belum Ada Project</h3>
        <p class="text-sm text-[#0B0F14]/50 max-w-sm">Rombongan Kelas ini belum diinisiasi sebuah Project. Oleh karena itu, belum ada Struktur Divisi yang bisa ditampilkan.</p>
    </div>
@endif

<!-- Daftar Siswa di Kelas tersebut yang menganggur (Unassigned dari divisi) -->
@if(isset($usersInClass) && $usersInClass->count() > 0)
    <div class="mt-8">
        <h3 class="text-xs font-extrabold uppercase tracking-widest text-[#0B0F14]/50 mb-4 border-b border-[#0B0F14]/10 pb-2">Semua Siswa Terdaftar di Rombel Ini</h3>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($usersInClass as $u)
                <div class="flex items-center justify-between p-3 rounded-xl border border-[#0B0F14]/10 bg-white hover:shadow-sm transition-all group">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#F7F8FA] text-[10px] font-bold text-[#0B0F14]/60 border border-[#0B0F14]/5">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="truncate text-xs font-bold text-[#0B0F14]">{{ $u->name }}</p>
                            <p class="truncate text-[9px] font-medium text-[#0B0F14]/40">{{ $u->username }}</p>
                        </div>
                    </div>
                    <button @click="openEditModal({ id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', username: '{{ addslashes($u->username) }}', email: '{{ addslashes($u->email) }}' })" class="shrink-0 text-[#0B0F14]/20 hover:text-[#0245EC] transition-colors pl-2">
                        <i class="ti ti-edit text-base"></i>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif
