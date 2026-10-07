<x-app-layout>
    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm"><i class="ti ti-users text-lg"></i></span>
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-[#0B0F14]">Manajemen Pengguna</h1>
            <p class="mt-1 text-sm font-medium text-[#0B0F14]/50">Pilih Rombongan Belajar (Rombel) untuk melihat struktur tim dan anggotanya.</p>
        </div>

        <!-- PERBAIKAN: Menambahkan atribut x-data agar fungsi $dispatch dikenali Alpine.js -->
        <button x-data @click="$dispatch('open-create')" class="inline-flex items-center gap-2 rounded-2xl bg-[#0245EC] px-6 py-3 text-sm font-extrabold text-white shadow-[0_10px_20px_-10px_rgba(2,69,236,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[0_15px_25px_-10px_rgba(2,69,236,1)]">
            <i class="ti ti-user-plus text-lg"></i> Tambah Pengguna
        </button>
    </header>

    @if(session('success')) <div class="mb-6 rounded-2xl bg-semantic-successBg p-4 border border-semantic-success/20 flex items-center gap-3"><i class="ti ti-circle-check text-xl text-semantic-success"></i><p class="text-sm font-bold text-semantic-success">{{ session('success') }}</p></div> @endif
    @if(session('error')) <div class="mb-6 rounded-2xl bg-semantic-dangerBg p-4 border border-semantic-danger/20 flex items-center gap-3"><i class="ti ti-alert-triangle text-xl text-semantic-danger"></i><p class="text-sm font-bold text-semantic-danger">{{ session('error') }}</p></div> @endif

    <!-- ALPINE COMPONENT: AJAX Loader -->
    <div x-data="userManagement()" x-init="fetchData()" class="space-y-6">

        <!-- Filter Toolbar -->
        <div class="rounded-[24px] border border-[#0B0F14]/10 bg-white p-4 shadow-sm flex flex-col md:flex-row items-center gap-4">

            <!-- Dropdown Rombel -->
            <div class="w-full md:w-1/3">
                <label class="block text-[10px] font-extrabold uppercase tracking-widest text-[#0B0F14]/50 mb-1.5 ml-1">Pilih Rombel Kelas</label>
                <div class="relative">
                    <i class="ti ti-chalkboard absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/40"></i>
                    <select x-model="selectedClass" @change="fetchData" class="w-full appearance-none rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] py-3 pl-11 pr-10 text-sm font-bold text-[#0B0F14] outline-none focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 transition-all cursor-pointer">
                        <option value="unassigned" class="text-semantic-danger font-extrabold">-- Anggota Belum Punya Kelas --</option>
                        @if($classes->count() > 0)
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->academicYear->name ?? '' }})</option>
                            @endforeach
                        @endif
                    </select>
                    <i class="ti ti-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/40 pointer-events-none"></i>
                </div>
            </div>

            <!-- Search Input -->
            <div class="w-full md:flex-1 relative mt-1 md:mt-5">
                <i class="ti ti-search absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/40"></i>
                <input type="text" x-model="searchQuery" @input.debounce.500ms="fetchData" placeholder="Cari nama atau username siswa di kelas ini..." class="w-full rounded-xl border border-[#0B0F14]/10 bg-white py-3 pl-11 pr-4 text-sm font-medium text-[#0B0F14] outline-none focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 transition-all">
            </div>

        </div>

        <!-- Area Hasil AJAX -->
        <div class="relative min-h-[300px]">
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="absolute inset-0 z-10 flex items-center justify-center rounded-[28px] bg-white/60 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3">
                    <i class="ti ti-loader text-4xl text-[#0245EC] animate-spin"></i>
                    <p class="text-sm font-bold text-[#0B0F14]/50 animate-pulse">Memuat Struktur Divisi...</p>
                </div>
            </div>

            <!-- Kontainer HTML yang disuntik dari Controller -->
            <div x-html="htmlContent" class="transition-opacity duration-300" :class="{'opacity-50 pointer-events-none': isLoading}"></div>
        </div>

    </div>

    <!-- Mendaftarkan Logika JS Alpine -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManagement', () => ({
                selectedClass: '{{ $classes->count() > 0 ? $classes->first()->id : "" }}',
                searchQuery: '',
                isLoading: false,
                htmlContent: '',

                async fetchData() {
                    if(!this.selectedClass) return;

                    this.isLoading = true;
                    try {
                        const response = await fetch(`{{ route('admin.users.index') }}?class_id=${this.selectedClass}&search=${this.searchQuery}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.text();
                        this.htmlContent = data;
                    } catch (error) {
                        console.error("Gagal menarik data:", error);
                        this.htmlContent = '<div class="p-8 text-center bg-semantic-dangerBg text-semantic-danger rounded-2xl">Gagal memuat data. Silakan coba lagi.</div>';
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>

    <!-- ========================================== -->
    <!-- ALPINE COMPONENT: MODALS (Dibungkus terpisah agar global) -->
    <!-- ========================================== -->
    <!-- ========================================== -->
    <!-- ALPINE COMPONENT: MODALS (Dibungkus terpisah agar global) -->
    <!-- ========================================== -->
    <div x-data="{ 
        showCreateModal: false, 
        showEditModal: false, 
        editUser: {},
        
        openEditModal(user) {
            this.editUser = user;
            this.showEditModal = true;
        }
    }" @open-create.window="showCreateModal = true">

        <!-- Karena elemen Tombol Edit ada di dalam x-html yang di-render AJAX, kita harus menangkap event klik secara dinamis. -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.bind('openEditModalBind', () => ({
                    // Agar fungsi openEditModal bisa dipanggil dari dalam partial AJAX
                    '@click'() {
                        const userData = eval('(' + this.$el.getAttribute('data-user') + ')');
                        this.openEditModal(userData);
                    }
                }));
            });
            // Sedikit trik: definisikan fungsi global yang akan dipanggil dari dalam partial
            window.openEditModal = function(userData) {
                document.querySelector('[x-data*="showEditModal"]').__x.$data.openEditModal(userData);
            }
        </script>

        <!-- MODAL CREATE -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="showCreateModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4">
                            <h3 class="flex items-center text-lg font-bold text-[#0B0F14]">
                                <i class="ti ti-user-plus mr-2 text-[#0245EC]"></i> Tambah Pengguna Baru
                            </h3>
                            <button type="button" @click="showCreateModal = false" class="text-[#0B0F14]/40 hover:text-[#0B0F14] transition-colors">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Nama Lengkap <span class="text-semantic-danger">*</span></label>
                                <input type="text" name="name" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Username <span class="text-semantic-danger">*</span></label>
                                    <input type="text" name="username" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Email <span class="text-semantic-danger">*</span></label>
                                    <input type="email" name="email" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                                </div>
                            </div>

                            <!-- TAMBAHAN: FORM PILIH KELAS (ROMBEL) -->
                            <div class="rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-4">
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Penempatan Rombel (Opsional)</label>
                                <select name="class_id" class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all cursor-pointer">
                                    <option value="" selected>-- Biarkan Kosong (Kolam Unassigned) --</option>
                                    @if($classes->count() > 0)
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->academicYear->name ?? '' }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Password Default <span class="text-semantic-danger">*</span></label>
                                <input type="password" name="password" minlength="8" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10">
                            <button type="button" @click="showCreateModal = false" class="rounded-xl border border-[#0B0F14]/10 px-5 py-2.5 text-sm font-bold bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] transition-colors">Batal</button>
                            <button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0245EC]/90 transition-colors">Simpan Pengguna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- PERBAIKAN: Menambahkan overflow-hidden di sini juga -->
                <div x-show="showEditModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl">
                    <form :action="`/admin/users/${editUser.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between border-b border-[#0B0F14]/10 bg-[#F7F8FA]/50 px-6 py-4">
                            <h3 class="flex items-center text-lg font-bold text-[#0B0F14]">
                                <i class="ti ti-user-edit mr-2 text-[#0245EC]"></i> Edit Akun
                            </h3>
                            <button type="button" @click="showEditModal = false" class="text-[#0B0F14]/40 hover:text-[#0B0F14] transition-colors">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Nama Lengkap</label>
                                <input type="text" name="name" x-model="editUser.name" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Username</label>
                                    <input type="text" name="username" x-model="editUser.username" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Email</label>
                                    <input type="email" name="email" x-model="editUser.email" required class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                                </div>
                            </div>
                            <div class="rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-4">
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Reset Password (Opsional)</label>
                                <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah sandi" class="w-full rounded-xl border border-[#0B0F14]/10 bg-white px-4 py-3 text-sm focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10 outline-none transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 bg-[#F7F8FA] px-6 py-4 border-t border-[#0B0F14]/10">
                            <button type="button" @click="showEditModal = false" class="rounded-xl border border-[#0B0F14]/10 px-5 py-2.5 text-sm font-bold bg-white text-[#0B0F14]/60 hover:bg-[#F7F8FA] transition-colors">Batal</button>
                            <button type="submit" class="rounded-xl bg-[#0245EC] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0245EC]/90 transition-colors">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

