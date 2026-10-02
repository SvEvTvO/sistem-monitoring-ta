<x-app-layout>
    <div x-data="{ 
        showDivisionModal: false, 
        showAssignModal: false, 
        selectedDivisionId: null, 
        selectedDivisionName: '',
        activeTab: {{ $divisions->count() > 0 ? $divisions->first()->id : 'null' }},

        // State untuk Modal Hapus Anggota
        showRemoveModal: false,
        removeMemberId: '',
        removeMemberName: '',
        removeMemberIsLeader: false,
        removeDivisionId: '',
        removeDivisionName: '',
        
        // Data seluruh divisi untuk mencari kandidat ketua pengganti
        divisionsData: @js(
            $divisions->keyBy('id')->map(function($div) {
                return $div->members->map(function($m) {
                    return ['user_id' => $m->user->id, 'member_id' => $m->id, 'name' => $m->user->name];
                });
            })
        ),

        // Fungsi Filter Kandidat (Tidak termasuk orang yang sedang dihapus)
        get availableReplacements() {
            if (!this.removeDivisionId || !this.divisionsData[this.removeDivisionId]) return [];
            return this.divisionsData[this.removeDivisionId].filter(m => m.member_id !== this.removeMemberId);
        },

        // Trigger buka modal hapus
        openRemoveModal(memberId, memberName, isLeader, divId, divName) {
            this.removeMemberId = memberId;
            this.removeMemberName = memberName;
            this.removeMemberIsLeader = isLeader;
            this.removeDivisionId = divId;
            this.removeDivisionName = divName;
            this.showRemoveModal = true;
        }
    }">
    
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-text-primary">Kelola Struktur Tim</h1>
                <p class="text-text-secondary mt-1">Atur penempatan anggota, struktur divisi, dan peran masing-masing personal.</p>
            </div>
            
            @if($isProjectLeader)
                <button @click="showDivisionModal = true" class="px-6 py-3 bg-primary text-white text-sm font-bold rounded-2xl hover:bg-primary-dark transition-colors shadow-sm flex items-center">
                    <i class="ti ti-plus mr-2 text-lg"></i> Buat Divisi Baru
                </button>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- AREA KHUSUS KETUA PROJECT: KOLAM UNASSIGNED -->
        <!-- ========================================== -->
        @if($isProjectLeader)
            <div class="bg-white border border-neutral-border rounded-[24px] shadow-sm mb-8 overflow-hidden relative">
                <div class="absolute left-0 top-0 bottom-0 w-2 bg-semantic-warning"></div>
                
                <div class="p-6 lg:p-8 pl-8 lg:pl-10">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-semantic-warningBg text-semantic-warning rounded-2xl flex items-center justify-center shrink-0 shadow-sm border border-semantic-warning/20">
                                <i class="ti ti-user-exclamation text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-extrabold text-text-primary">Anggota Belum Dialokasikan</h2>
                                <p class="text-sm text-text-secondary mt-0.5">Daftar anggota baru yang menunggu penempatan divisi.</p>
                            </div>
                        </div>
                        
                        @if($unassignedUsers->count() > 0)
                            <div class="px-4 py-1.5 bg-semantic-warningBg text-semantic-warning font-bold rounded-xl text-sm border border-semantic-warning/30 shadow-sm flex items-center shrink-0">
                                <i class="ti ti-clock animate-pulse mr-1.5 text-lg"></i> {{ $unassignedUsers->count() }} Menunggu
                            </div>
                        @endif
                    </div>

                    @if($unassignedUsers->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($unassignedUsers as $unassigned)
                                <div class="bg-white border border-semantic-warning/30 p-4 rounded-2xl flex flex-col gap-3 relative hover:shadow-md transition-shadow group">
                                    <div class="absolute inset-0 opacity-[0.02] rounded-2xl pointer-events-none" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 1px, transparent 0, transparent 50%); background-size: 10px 10px;"></div>
                                    
                                    <div class="flex items-center gap-3 z-10">
                                        <div class="w-10 h-10 rounded-full bg-semantic-warningBg text-semantic-warning border border-semantic-warning/30 flex items-center justify-center font-bold text-sm shadow-sm shrink-0 group-hover:scale-105 transition-transform">
                                            {{ substr($unassigned->name, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden flex-1">
                                            <p class="text-sm font-bold text-text-primary truncate" title="{{ $unassigned->name }}">{{ $unassigned->name }}</p>
                                            <p class="text-[10px] font-medium text-text-secondary truncate" title="{{ $unassigned->email }}">{{ $unassigned->email }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="z-10 mt-1 pt-3 border-t border-dashed border-semantic-warning/40 flex items-center justify-between">
                                        <span class="text-[10px] font-extrabold text-semantic-warning uppercase tracking-wider">
                                            Status: Unassigned
                                        </span>
                                        <i class="ti ti-help-hexagon text-semantic-warning/50 text-lg"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-semantic-successBg/30 rounded-2xl border border-semantic-success/20 flex flex-col items-center">
                            <div class="w-16 h-16 bg-semantic-success/10 text-semantic-success rounded-full flex items-center justify-center mb-3">
                                <i class="ti ti-check text-4xl"></i>
                            </div>
                            <h3 class="font-extrabold text-semantic-success text-lg mb-1">Semua Anggota Telah Dialokasikan!</h3>
                            <p class="text-sm text-semantic-success opacity-80">Tidak ada anggota yang menganggur di kolam penampungan.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- AREA DAFTAR DIVISI DENGAN TAB NAVIGATION -->
        <!-- ========================================== -->
        <div>
            <h3 class="text-lg font-bold text-text-primary mb-4 flex items-center">
                <i class="ti ti-sitemap mr-2 text-primary"></i> Struktur Divisi & Anggota
            </h3>

            @if($divisions->count() > 0)
                <div class="flex gap-3 overflow-x-auto pb-4 mb-2 no-scrollbar scroll-smooth">
                    @foreach($divisions as $division)
                        <button @click="activeTab = {{ $division->id }}"
                                :class="activeTab === {{ $division->id }} ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-text-secondary border-neutral-border hover:bg-neutral-bg'"
                                class="px-5 py-2.5 rounded-[14px] border font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                            <i class="ti ti-users-group text-lg"></i>
                            {{ $division->name }}
                            <span :class="activeTab === {{ $division->id }} ? 'bg-white/20 text-white' : 'bg-neutral-surfaceSecondary text-text-secondary'" class="px-2 py-0.5 rounded-lg text-[10px] ml-1 transition-colors">
                                {{ $division->members->count() }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="bg-white border border-neutral-border rounded-[24px] shadow-sm overflow-hidden min-h-[400px]">
                    @foreach($divisions as $division)
                        <div x-show="activeTab === {{ $division->id }}" x-cloak x-transition.opacity.duration.300ms class="h-full flex flex-col">
                            
                            <div class="p-6 lg:px-8 border-b border-neutral-border bg-neutral-surfaceSecondary/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <h3 class="text-2xl font-extrabold text-text-primary">{{ $division->name }}</h3>
                                    <p class="text-sm font-medium text-text-secondary mt-1">
                                        <i class="ti ti-users mr-1"></i> Total {{ $division->members->count() }} Anggota
                                    </p>
                                </div>
                                
                                @if($isProjectLeader && $unassignedUsers->count() > 0)
                                    <button @click="showAssignModal = true; selectedDivisionId = {{ $division->id }}; selectedDivisionName = '{{ $division->name }}'" class="px-5 py-2.5 bg-primary/10 text-primary hover:bg-primary hover:text-white text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center shrink-0">
                                        <i class="ti ti-user-plus mr-1.5 text-lg"></i> Tambah Anggota
                                    </button>
                                @endif
                            </div>

                            <div class="p-6 lg:p-8 flex-1 bg-neutral-bg/30">
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    @forelse($division->members as $member)
                                        @php
                                            $isLeader = $division->leader_user_id == $member->user->id;
                                        @endphp
                                        
                                        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl border transition-all hover:shadow-md relative overflow-hidden {{ $isLeader ? 'border-primary/50 bg-primary/5 shadow-sm shadow-primary/10' : 'border-neutral-border bg-white hover:border-primary/30' }}">
                                            
                                            @if($isLeader)
                                                <div class="absolute left-0 top-0 bottom-0 w-2 bg-accent shadow-[0_0_12px_rgba(204,255,0,0.8)] z-0"></div>
                                            @endif

                                            <div class="flex items-center gap-4 overflow-hidden z-10 {{ $isLeader ? 'pl-2' : '' }}">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-extrabold text-lg shadow-sm shrink-0 relative {{ $isLeader ? 'bg-primary text-accent ring-2 ring-accent/50 shadow-accent/20' : 'bg-neutral-surfaceSecondary text-text-secondary border border-neutral-border' }}">
                                                    {{ substr($member->user->name, 0, 1) }}
                                                    @if($isLeader)
                                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-accent rounded-full flex items-center justify-center shadow-sm text-primary ring-2 ring-white">
                                                            <i class="ti ti-star-filled text-[10px]"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="overflow-hidden">
                                                    <p class="text-sm font-extrabold text-text-primary truncate" title="{{ $member->user->name }}">{{ $member->user->name }}</p>
                                                    <p class="text-xs font-medium text-text-secondary truncate mt-0.5" title="{{ $member->user->email }}">{{ $member->user->email }}</p>
                                                    @if($isLeader)
                                                        <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded-md text-[9px] font-extrabold bg-primary text-accent uppercase tracking-wider border border-accent/30 shadow-sm">Ketua Divisi</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex flex-col gap-2 shrink-0 border-l {{ $isLeader ? 'border-primary/20' : 'border-neutral-border' }} pl-4 z-10">
                                                @if($isProjectLeader && !$isLeader)
                                                    <form action="{{ route('members.setLeader', $division->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $member->user->id }}">
                                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-neutral-bg border border-neutral-border text-text-muted hover:text-accent hover:border-primary hover:bg-primary transition-colors" title="Jadikan Ketua Divisi">
                                                            <i class="ti ti-star text-base"></i>
                                                        </button>
                                                        
                                                    </form>
                                                @endif
                                                
                                                <!-- TOMBOL DETAIL BARU -->
                                                <a href="{{ route('members.show', $member->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary/10 border border-primary/20 text-primary hover:bg-primary hover:text-white transition-colors" title="Lihat Detail Profil & Laporan">
                                                    <i class="ti ti-eye text-base"></i>
                                                </a>

                                                <!-- TOMBOL HAPUS BARU: Trigger Alpine Modal -->
                                                <button type="button" 
                                                        @click="openRemoveModal({{ $member->id }}, '{{ addslashes($member->user->name) }}', {{ $isLeader ? 'true' : 'false' }}, {{ $division->id }}, '{{ addslashes($division->name) }}')"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg {{ $isLeader ? 'bg-white border-semantic-danger/30 text-semantic-danger hover:bg-semantic-danger hover:text-white' : 'bg-neutral-bg border border-neutral-border text-text-muted hover:text-semantic-danger hover:border-semantic-danger hover:bg-semantic-dangerBg' }} transition-colors" 
                                                        title="Keluarkan dari Divisi">
                                                    <i class="ti ti-user-minus text-base"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-full text-center py-12">
                                            <div class="w-20 h-20 bg-neutral-bg rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-neutral-border">
                                                <i class="ti ti-users text-4xl text-text-muted"></i>
                                            </div>
                                            <p class="text-text-primary font-bold text-lg mb-1">Divisi Kosong</p>
                                            <p class="text-sm text-text-secondary">Belum ada anggota yang dialokasikan ke divisi ini.</p>
                                        </div>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-neutral-border rounded-[24px] p-12 text-center shadow-sm">
                    <i class="ti ti-sitemap text-6xl text-text-muted opacity-50 mb-4 block"></i>
                    <h3 class="text-2xl font-bold text-text-primary mb-2">Belum Ada Divisi</h3>
                    <p class="text-sm text-text-secondary mb-6 max-w-md mx-auto">Struktur project Anda masih kosong. Silakan buat divisi pertama Anda untuk mulai mengalokasikan anggota tim.</p>
                    @if($isProjectLeader)
                        <button @click="showDivisionModal = true" class="px-8 py-3 bg-primary text-white text-sm font-bold rounded-xl inline-flex items-center hover:bg-primary-dark transition-colors shadow-sm">
                            <i class="ti ti-plus mr-2 text-lg"></i> Buat Divisi Sekarang
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: BUAT DIVISI BARU -->
        <!-- ========================================== -->
        <div x-show="showDivisionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showDivisionModal" x-transition.opacity class="fixed inset-0 bg-text-primary/40 backdrop-blur-sm transition-opacity"></div>
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Pastikan overflow-visible agar dropdown pencarian tidak terpotong -->
                <div x-show="showDivisionModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-[24px] text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-neutral-border relative">
                    <form action="{{ route('members.storeDivision') }}" method="POST">
                        @csrf
                        <div class="px-6 pt-6 pb-4 border-b border-neutral-border flex justify-between items-center bg-neutral-surfaceSecondary/20 rounded-t-[24px]">
                            <h3 class="text-lg font-bold text-text-primary flex items-center" id="modal-title">
                                <i class="ti ti-sitemap text-primary mr-2 text-xl"></i> Buat Divisi Baru
                            </h3>
                            <button type="button" @click="showDivisionModal = false" class="text-text-muted hover:text-text-primary transition-colors">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Input Nama Divisi -->
                            <div>
                                <label class="block text-sm font-bold text-text-primary mb-2">Nama Divisi <span class="text-semantic-danger">*</span></label>
                                <input type="text" name="name" class="w-full px-4 py-3 rounded-2xl border border-neutral-border focus:border-primary focus:ring-4 focus:ring-primary/10 text-sm transition-all outline-none" placeholder="Contoh: UI/UX Design, Web Development..." required>
                            </div>

                            <!-- INPUT KODE DIVISI BARU -->
                            <div>
                                <label class="block text-sm font-bold text-text-primary mb-2">Kode Divisi <span class="text-semantic-danger">*</span></label>
                                <input type="text" name="code" class="w-full px-4 py-3 rounded-2xl border border-neutral-border focus:border-primary focus:ring-4 focus:ring-primary/10 text-sm transition-all outline-none uppercase" placeholder="Contoh: DES, WEB, MOB..." required maxlength="10">
                                <p class="text-xs text-text-secondary mt-1.5">Gunakan 3-5 huruf singkatan divisi.</p>
                            </div>

                            <!-- Input Pilih Ketua Divisi (Biarkan kode Alpine.js milikmu di bawah sini tidak berubah) -->
                            <div>
                                <label class="block text-sm font-bold text-text-primary mb-2">Pilih Ketua Divisi <span class="text-semantic-danger">*</span></label>
                                
                                <div x-data="{
                                    isOpen: false,
                                    search: '',
                                    selectedId: '',
                                    selectedName: '',
                                    users: @js(isset($unassignedUsers) ? $unassignedUsers->map->only(['id', 'name', 'email']) : []),
                                    get filteredUsers() {
                                        if (this.search === '') return this.users;
                                        return this.users.filter(user => user.name.toLowerCase().includes(this.search.toLowerCase()) || user.email.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    selectUser(user) {
                                        this.selectedId = user.id;
                                        this.selectedName = user.name;
                                        this.isOpen = false;
                                        this.search = '';
                                    }
                                }" class="relative" @click.outside="isOpen = false">
                                    
                                    <!-- Input yang dikirim ke controller -->
                                    <input type="hidden" name="leader_user_id" x-model="selectedId" required>

                                    <!-- Tombol Pemicu -->
                                    <button type="button" @click="isOpen = !isOpen" class="w-full px-4 py-3.5 rounded-2xl border border-neutral-border bg-white text-left flex items-center justify-between focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all shadow-sm group">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <i class="ti ti-star text-text-muted text-lg shrink-0 group-hover:text-primary transition-colors" x-show="!selectedName"></i>
                                            <span x-text="selectedName || '-- Cari & Pilih Ketua Divisi --'" :class="{'text-text-primary font-bold': selectedName, 'text-text-muted font-medium': !selectedName}" class="text-sm truncate"></span>
                                        </div>
                                        <i class="ti ti-chevron-down text-text-muted transition-transform shrink-0" :class="{'rotate-180': isOpen}"></i>
                                    </button>

                                    <!-- Dropdown List -->
                                    <div x-show="isOpen" x-transition.opacity.duration.200ms x-cloak class="absolute z-50 w-full mt-2 bg-white border border-neutral-border rounded-[20px] shadow-xl overflow-hidden flex flex-col left-0">
                                        
                                        <div class="p-3 border-b border-neutral-border bg-neutral-surfaceSecondary/50">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="ti ti-search text-text-muted"></i>
                                                </div>
                                                <input type="text" x-model="search" @click.stop placeholder="Ketik nama atau email..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-neutral-border focus:border-primary focus:ring-2 focus:ring-primary/10 text-sm outline-none transition-all bg-white shadow-inner" @keydown.enter.prevent>
                                            </div>
                                        </div>

                                        <ul class="max-h-56 overflow-y-auto overscroll-contain no-scrollbar">
                                            <template x-for="user in filteredUsers" :key="user.id">
                                                <li @click="selectUser(user)" class="px-4 py-3 hover:bg-primary/5 cursor-pointer border-b border-neutral-border/30 last:border-0 transition-colors flex items-center gap-3 group">
                                                    <div class="w-9 h-9 rounded-full bg-neutral-bg border border-neutral-border text-text-secondary flex items-center justify-center font-extrabold text-xs shrink-0 group-hover:bg-primary/10 group-hover:text-primary group-hover:border-primary/20 transition-colors shadow-sm">
                                                        <span x-text="user.name.substring(0,1)"></span>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <p class="text-sm font-bold text-text-primary truncate group-hover:text-primary transition-colors" x-text="user.name"></p>
                                                        <p class="text-[10px] font-medium text-text-secondary truncate mt-0.5" x-text="user.email"></p>
                                                    </div>
                                                </li>
                                            </template>
                                            
                                            <li x-show="filteredUsers.length === 0" class="px-4 py-8 text-center flex flex-col items-center">
                                                <div class="w-12 h-12 bg-neutral-bg rounded-full flex items-center justify-center mb-3 border border-dashed border-neutral-border">
                                                    <i class="ti ti-user-x text-2xl text-text-muted opacity-50"></i>
                                                </div>
                                                <span class="text-sm font-bold text-text-primary">Anggota tidak ditemukan</span>
                                                <span class="text-xs text-text-secondary mt-1">Coba kata kunci pencarian lain.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-5 bg-neutral-bg flex justify-end gap-3 border-t border-neutral-border rounded-b-[24px]">
                            <button type="button" @click="showDivisionModal = false" class="px-6 py-2.5 bg-white border border-neutral-border text-text-secondary font-bold rounded-xl hover:bg-neutral-surface transition-colors text-sm shadow-sm">Batal</button>
                            <button type="submit" class="px-6 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-colors text-sm shadow-sm flex items-center group">
                                <i class="ti ti-device-floppy mr-2 group-hover:scale-110 transition-transform"></i> Simpan Divisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: ASSIGN MEMBER KE DIVISI (MULTIPLE)-->
        <!-- ========================================== -->
        <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showAssignModal" x-transition.opacity class="fixed inset-0 bg-text-primary/40 backdrop-blur-sm transition-opacity"></div>
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showAssignModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-[24px] text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-neutral-border relative">
                    
                    <!-- PERBAIKAN: x-data dipindahkan ke tag <form> agar mencakup seluruh elemen form -->
                    <form action="{{ route('members.assign') }}" method="POST"
                          x-data="{
                              isOpen: false,
                              search: '',
                              selectedUsers: [],
                              users: @js(isset($unassignedUsers) ? $unassignedUsers->map->only(['id', 'name', 'email']) : []),
                              
                              get filteredUsers() {
                                  // Sembunyikan user yang sudah dipilih dari daftar dropdown
                                  let available = this.users.filter(u => !this.selectedUsers.some(su => su.id === u.id));
                                  if (this.search === '') return available;
                                  return available.filter(user => user.name.toLowerCase().includes(this.search.toLowerCase()) || user.email.toLowerCase().includes(this.search.toLowerCase()));
                              },
                              selectUser(user) {
                                  this.selectedUsers.push(user);
                                  this.search = '';
                                  // Biarkan dropdown tetap buka dan kembalikan fokus ke input pencarian
                                  this.$refs.searchInput.focus();
                              },
                              removeUser(id) {
                                  this.selectedUsers = this.selectedUsers.filter(u => u.id !== id);
                              }
                          }" 
                          x-init="$watch('showAssignModal', value => { if(!value) { selectedUsers = []; search = ''; isOpen = false; } })">
                        
                        @csrf
                        <input type="hidden" name="division_id" x-model="selectedDivisionId">
                        
                        <div class="px-6 pt-6 pb-4 border-b border-neutral-border flex justify-between items-center bg-neutral-surfaceSecondary/20 rounded-t-[24px]">
                            <h3 class="text-lg font-bold text-text-primary flex items-center" id="modal-title">
                                <i class="ti ti-users-plus text-primary mr-2 text-xl"></i> Tambah Anggota Divisi
                            </h3>
                            <button type="button" @click="showAssignModal = false" class="text-text-muted hover:text-text-primary transition-colors">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="p-6">
                            <div class="mb-5 p-4 bg-primary/10 rounded-2xl border border-primary/20 text-sm">
                                Memasukkan anggota ke Divisi: <strong class="text-primary text-base ml-1" x-text="selectedDivisionName"></strong>
                            </div>
                            
                            <label class="block text-sm font-bold text-text-primary mb-2">Pilih Anggota (Bisa lebih dari 1)</label>
                            
                            <!-- Komponen Alpine Multi-Select (x-data telah dihapus dari sini) -->
                            <div class="relative" @click.outside="isOpen = false">
                                
                                <!-- Render array input tersembunyi ke Laravel -->
                                <template x-for="user in selectedUsers" :key="user.id">
                                    <input type="hidden" name="user_ids[]" x-model="user.id">
                                </template>

                                <!-- Kotak Multi-Select & Input Pencarian -->
                                <div class="w-full min-h-[52px] p-2 pr-10 rounded-2xl border border-neutral-border bg-white flex flex-wrap gap-2 items-center cursor-text focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all shadow-sm"
                                     @click="isOpen = true; $nextTick(() => $refs.searchInput.focus())">
                                    
                                    <!-- Daftar Tag/Pills untuk User yang terpilih -->
                                    <template x-for="user in selectedUsers" :key="user.id">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary font-bold text-xs rounded-xl border border-primary/20 animate-fade-in">
                                            <span x-text="user.name"></span>
                                            <div @click.stop="removeUser(user.id)" class="w-4 h-4 rounded-full bg-primary/20 flex items-center justify-center hover:bg-semantic-danger hover:text-white transition-colors cursor-pointer">
                                                <i class="ti ti-x text-[10px]"></i>
                                            </div>
                                        </span>
                                    </template>

                                    <!-- Input Pencarian (Menyatu di dalam kotak) -->
                                    <input type="text" x-model="search" x-ref="searchInput" 
                                           class="flex-1 min-w-[140px] bg-transparent border-none outline-none focus:ring-0 text-sm p-1 text-text-primary placeholder-text-muted font-medium" 
                                           x-bind:placeholder="selectedUsers.length === 0 ? '-- Cari & Pilih Anggota --' : 'Tambah lagi...'"
                                           @keydown.backspace="if(search === '' && selectedUsers.length > 0) { selectedUsers.pop() }"
                                           @keydown.enter.prevent>
                                           
                                    <i class="ti ti-chevron-down text-text-muted absolute right-4 top-1/2 -translate-y-1/2 transition-transform" :class="{'rotate-180': isOpen}"></i>
                                </div>

                                <!-- Dropdown List (Melayang) -->
                                <div x-show="isOpen" x-transition.opacity.duration.200ms x-cloak class="absolute z-50 w-full mt-2 bg-white border border-neutral-border rounded-[20px] shadow-xl overflow-hidden flex flex-col left-0">
                                    <ul class="max-h-56 overflow-y-auto overscroll-contain no-scrollbar py-2">
                                        <template x-for="user in filteredUsers" :key="user.id">
                                            <li @click="selectUser(user)" class="px-4 py-3 hover:bg-primary/5 cursor-pointer border-b border-neutral-border/30 last:border-0 transition-colors flex items-center gap-3 group">
                                                <div class="w-9 h-9 rounded-full bg-neutral-bg border border-neutral-border text-text-secondary flex items-center justify-center font-extrabold text-xs shrink-0 group-hover:bg-primary/10 group-hover:text-primary group-hover:border-primary/20 transition-colors shadow-sm">
                                                    <span x-text="user.name.substring(0,1)"></span>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-sm font-bold text-text-primary truncate group-hover:text-primary transition-colors" x-text="user.name"></p>
                                                    <p class="text-[10px] font-medium text-text-secondary truncate mt-0.5" x-text="user.email"></p>
                                                </div>
                                            </li>
                                        </template>
                                        
                                        <!-- Kondisi Kosong / Semua terpilih -->
                                        <li x-show="filteredUsers.length === 0" class="px-4 py-8 text-center flex flex-col items-center">
                                            <div class="w-12 h-12 bg-neutral-bg rounded-full flex items-center justify-center mb-3 border border-dashed border-neutral-border">
                                                <i class="ti ti-users text-2xl text-text-muted opacity-50"></i>
                                            </div>
                                            <span class="text-sm font-bold text-text-primary" x-text="users.length === selectedUsers.length ? 'Semua anggota Unassigned telah Anda pilih.' : 'Anggota tidak ditemukan.'"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-5 bg-neutral-bg flex justify-end gap-3 border-t border-neutral-border rounded-b-[24px]">
                            <button type="button" @click="showAssignModal = false" class="px-6 py-2.5 bg-white border border-neutral-border text-text-secondary font-bold rounded-xl hover:bg-neutral-surface transition-colors text-sm shadow-sm">Batal</button>
                            <!-- Karena sekarang tombol ini berada di dalam form yang sama dengan x-data, ia bisa membaca length dari selectedUsers -->
                            <button type="submit" x-bind:disabled="selectedUsers.length === 0" class="px-6 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-colors text-sm shadow-sm flex items-center group disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="ti ti-device-floppy mr-2 group-hover:scale-110 transition-transform"></i> Simpan Penempatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 3: KELUARKAN ANGGOTA & RE-ASSIGN LEADER -->
        <!-- ========================================== -->
        <div x-show="showRemoveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showRemoveModal" x-transition.opacity class="fixed inset-0 bg-text-primary/60 backdrop-blur-sm transition-opacity"></div>
            
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showRemoveModal" x-transition.scale.origin.center class="relative bg-white rounded-[24px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-semantic-danger/30">
                    
                    <form method="POST" x-bind:action="'{{ url('members') }}/' + removeMemberId + '/remove'">
                        @csrf
                        @method('DELETE')
                        
                        <div class="p-6 sm:p-8">
                            <!-- Header Ikon -->
                            <div class="w-16 h-16 bg-semantic-dangerBg text-semantic-danger rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-semantic-danger/20">
                                <i class="ti ti-user-minus text-3xl"></i>
                            </div>
                            
                            <h3 class="text-xl font-extrabold text-text-primary mb-2">Keluarkan Anggota</h3>
                            
                            <!-- KONDISI 1: JIKA ANGGOTA BIASA -->
                            <div x-show="!removeMemberIsLeader">
                                <p class="text-sm text-text-secondary leading-relaxed">
                                    Apakah Anda yakin ingin mengeluarkan <strong class="text-text-primary" x-text="removeMemberName"></strong> dari Divisi <strong class="text-text-primary" x-text="removeDivisionName"></strong>? Mereka akan dikembalikan ke Kolam Unassigned.
                                </p>
                            </div>

                            <!-- KONDISI 2: JIKA KETUA DIVISI -->
                            <div x-show="removeMemberIsLeader" class="space-y-4">
                                <div class="p-4 bg-semantic-warningBg/50 border border-semantic-warning/30 rounded-2xl flex gap-3 text-semantic-warning">
                                    <i class="ti ti-alert-triangle text-xl shrink-0 mt-0.5"></i>
                                    <div class="text-sm font-medium leading-relaxed">
                                        <strong class="font-extrabold text-semantic-warning block mb-1" x-text="removeMemberName + ' adalah Ketua Divisi!'"></strong>
                                        Sebelum mengeluarkannya, Anda wajib menunjuk ketua pengganti dari anggota yang tersisa.
                                    </div>
                                </div>

                                <div x-show="availableReplacements.length > 0">
                                    <label class="block text-sm font-bold text-text-primary mb-2">Pilih Ketua Pengganti <span class="text-semantic-danger">*</span></label>
                                    <select name="new_leader_id" class="w-full px-4 py-3 rounded-xl border border-neutral-border focus:border-semantic-warning focus:ring-4 focus:ring-semantic-warning/10 text-sm transition-all outline-none bg-neutral-surfaceSecondary/50" x-bind:required="removeMemberIsLeader">
                                        <option value="" disabled selected>-- Pilih Ketua Baru --</option>
                                        <template x-for="rep in availableReplacements" :key="rep.user_id">
                                            <option x-bind:value="rep.user_id" x-text="rep.name"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <!-- Jika Divisi Kosong (Hanya isi Ketua) -->
                                <div x-show="availableReplacements.length === 0" class="p-4 bg-semantic-dangerBg/30 border border-semantic-danger/20 rounded-2xl text-semantic-danger text-sm font-bold text-center">
                                    <i class="ti ti-users-minus text-2xl block mb-2"></i>
                                    Tidak ada anggota tersisa di divisi ini untuk dijadikan ketua pengganti! <br><br>
                                    <span class="font-medium">Tambahkan anggota lain terlebih dahulu.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Aksi -->
                        <div class="px-6 py-5 bg-neutral-bg flex justify-end gap-3 border-t border-neutral-border">
                            <button type="button" @click="showRemoveModal = false" class="px-5 py-2.5 bg-white border border-neutral-border text-text-secondary font-bold rounded-xl hover:bg-neutral-surface transition-colors text-sm shadow-sm">
                                Batal
                            </button>
                            <!-- Disable tombol jika ketua dihapus tapi tidak ada penggantinya -->
                            <button type="submit" x-bind:disabled="removeMemberIsLeader && availableReplacements.length === 0" class="px-5 py-2.5 bg-semantic-danger text-white font-bold rounded-xl hover:bg-semantic-danger/90 transition-colors text-sm shadow-sm flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="ti ti-trash mr-1.5"></i> Ya, Keluarkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>