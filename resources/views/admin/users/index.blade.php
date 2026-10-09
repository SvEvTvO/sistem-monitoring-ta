<x-app-layout>

    @php
        $sectionLbl = 'text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50';
    @endphp

    <!-- ===================== PAGE HEADER ===================== -->
    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm"><i class="ti ti-users text-lg"></i></span>
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-[#0B0F14] sm:text-3xl">Manajemen Pengguna</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#0B0F14]/10 bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#0B0F14] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#D9FF3A]"></span>
                    {{ $classes->count() }} Rombel
                </span>
            </div>
            <p class="mt-1.5 text-sm font-medium text-[#0B0F14]/50">Pilih Rombongan Belajar (Rombel) untuk melihat struktur tim dan anggotanya.</p>
        </div>

        <button x-data @click="$dispatch('open-create')" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-3 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_36px_-12px_rgba(217,255,58,1)] active:translate-y-0 sm:w-auto lg:shrink-0">
            <i class="ti ti-user-plus text-lg"></i> Tambah Pengguna
        </button>
    </header>

    <!-- Catatan: flash success/error kini dirender oleh app layout (global) -->

    <!-- ===================== ALPINE COMPONENT: FILTER + AJAX ===================== -->
    <div x-data="userManagement()" x-init="fetchData()" class="space-y-6">

        <!-- Filter Toolbar -->
        <section class="rounded-[24px] border border-[#0B0F14]/10 bg-white p-5 shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-end">

                <!-- Dropdown Rombel -->
                <div class="w-full md:w-1/3">
                    <label class="mb-2 ml-1 block text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Pilih Rombel Kelas</label>
                    <div class="relative">
                        <i class="ti ti-chalkboard pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/35"></i>
                        <select x-model="selectedClass" @change="fetchData()" class="w-full cursor-pointer appearance-none rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-10 text-sm font-bold text-[#0B0F14] outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                            <option value="unassigned">-- Anggota Belum Punya Kelas --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->academicYear->name ?? '' }})</option>
                            @endforeach
                        </select>
                        <i class="ti ti-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/40"></i>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative w-full md:flex-1">
                    <label class="mb-2 ml-1 block text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">Cari Anggota</label>
                    <div class="relative">
                        <i class="ti ti-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/35"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="fetchData()" placeholder="Cari nama, username, atau email..." class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm font-medium text-[#0B0F14] placeholder:text-[#0B0F14]/35 outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                    </div>
                </div>
            </div>
        </section>

        <!-- Area Hasil AJAX -->
        <div class="relative min-h-[300px]">
            <!-- Loading -->
            <div x-show="isLoading" x-cloak class="absolute inset-0 z-10 flex items-center justify-center rounded-[28px] bg-white/70 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-lg">
                        <i class="ti ti-loader animate-spin text-2xl text-[#0245EC]"></i>
                    </span>
                    <p class="text-sm font-bold text-[#0B0F14]/50 animate-pulse">Memuat data...</p>
                </div>
            </div>

            <!-- Kontainer HTML dari controller. SEMUA interaksi di dalamnya ditangani
                 event delegation (data-div-tab / data-edit-user) — lihat script di bawah. -->
            <div id="ajaxContent" x-html="htmlContent" class="transition-opacity duration-300" :class="{'opacity-50 pointer-events-none': isLoading}"></div>
        </div>
    </div>

    <!-- ===================== MODALS ===================== -->
    <div x-data="{
            showCreateModal: false,
            showEditModal: false,
            editUser: {},
            openEditModal(user) { this.editUser = user; this.showEditModal = true; }
        }"
         @open-create.window="showCreateModal = true"
         @open-edit-user.window="openEditModal($event.detail)"
         @keydown.escape.window="showCreateModal = false; showEditModal = false">

        <!-- MODAL CREATE -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="showCreateModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>
                        <div class="flex items-center justify-between px-6 py-4">
                            <h3 class="flex items-center gap-2.5 text-lg font-black text-[#0B0F14]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#D9FF3A] text-[#0B0F14]"><i class="ti ti-user-plus text-lg"></i></span>
                                Tambah Pengguna Baru
                            </h3>
                            <button type="button" @click="showCreateModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#F7F8FA] hover:text-[#0B0F14]">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4 border-t border-[#0B0F14]/10 p-6">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Nama Lengkap <span class="font-black">*</span></label>
                                <div class="relative">
                                    <i class="ti ti-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                    <input type="text" name="name" required placeholder="Nama lengkap pengguna" class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Username <span class="font-black">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-at pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                        <input type="text" name="username" required placeholder="username" class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Email <span class="font-black">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-mail pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                        <input type="email" name="email" required placeholder="nama@email.com" class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                    </div>
                                </div>
                            </div>

                            <!-- Penempatan Rombel -->
                            <div class="rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-4">
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Penempatan Rombel</label>
                                <div class="relative">
                                    <i class="ti ti-chalkboard pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                    <select name="class_id" class="w-full cursor-pointer appearance-none rounded-2xl border border-[#0B0F14]/10 bg-white py-3 pl-12 pr-10 text-sm font-medium outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                        <option value="" selected>-- Biarkan Kosong (Belum Punya Kelas) --</option>
                                        @foreach ($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->academicYear->name ?? '' }})</option>
                                        @endforeach
                                    </select>
                                    <i class="ti ti-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#0B0F14]/40"></i>
                                </div>
                                <p class="mt-2 text-xs font-medium text-[#0B0F14]/40">Pengguna tanpa rombel akan tampil di daftar "Anggota Belum Punya Kelas".</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Password Default <span class="font-black">*</span></label>
                                <div class="relative">
                                    <i class="ti ti-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                    <input type="password" name="password" minlength="8" required placeholder="Minimal 8 karakter" class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col-reverse gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] px-6 py-4 sm:flex-row sm:justify-end">
                            <button type="button" @click="showCreateModal = false" class="inline-flex items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white px-5 py-2.5 text-sm font-bold text-[#0B0F14]/60 transition-colors hover:bg-[#F7F8FA] sm:w-auto">Batal</button>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-2.5 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all hover:-translate-y-0.5 sm:w-auto">
                                <i class="ti ti-user-plus text-lg"></i> Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-sm"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="showEditModal" x-transition.scale class="relative w-full max-w-lg overflow-hidden rounded-[24px] bg-white text-left shadow-2xl">
                    <form :action="`/admin/users/${editUser.id}`" method="POST">
                        @csrf @method('PUT')
                        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>
                        <div class="flex items-center justify-between px-6 py-4">
                            <h3 class="flex items-center gap-2.5 text-lg font-black text-[#0B0F14]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0245EC]/10 text-[#0245EC]"><i class="ti ti-user-edit text-lg"></i></span>
                                Edit Akun
                            </h3>
                            <button type="button" @click="showEditModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#F7F8FA] hover:text-[#0B0F14]">
                                <i class="ti ti-x text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4 border-t border-[#0B0F14]/10 p-6">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Nama Lengkap <span class="font-black">*</span></label>
                                <input type="text" name="name" x-model="editUser.name" required class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white px-4 py-3.5 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Username <span class="font-black">*</span></label>
                                    <input type="text" name="username" x-model="editUser.username" required class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white px-4 py-3.5 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Email <span class="font-black">*</span></label>
                                    <input type="email" name="email" x-model="editUser.email" required class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white px-4 py-3.5 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                </div>
                            </div>
                            <div class="rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA] p-4">
                                <label class="mb-2 block text-sm font-bold text-[#0B0F14]">Reset Password <span class="ml-1 text-[10px] font-bold uppercase tracking-wide text-[#0B0F14]/40">Opsional</span></label>
                                <div class="relative">
                                    <i class="ti ti-key pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-[#0B0F14]/30"></i>
                                    <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah sandi" class="w-full rounded-2xl border border-[#0B0F14]/10 bg-white py-3.5 pl-12 pr-4 text-sm outline-none transition-all focus:border-[#0245EC] focus:ring-4 focus:ring-[#0245EC]/10">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col-reverse gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA] px-6 py-4 sm:flex-row sm:justify-end">
                            <button type="button" @click="showEditModal = false" class="inline-flex items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-white px-5 py-2.5 text-sm font-bold text-[#0B0F14]/60 transition-colors hover:bg-[#F7F8FA] sm:w-auto">Batal</button>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#D9FF3A] px-6 py-2.5 text-sm font-extrabold text-[#0B0F14] shadow-[0_14px_30px_-12px_rgba(217,255,58,0.9)] transition-all hover:-translate-y-0.5 sm:w-auto">
                                <i class="ti ti-device-floppy text-lg"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== STYLE: TAB DIVISI (CSS murni — JS hanya toggle .is-active) ===================== -->
    <style>
        .div-tabs { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 14px; margin-bottom: 8px; }
        .div-tabs::-webkit-scrollbar { height: 0; }

        .div-tab {
            display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
            border: 1px solid rgba(11,15,20,.10); background: #FFFFFF; color: rgba(11,15,20,.6);
            padding: 10px 18px; border-radius: 14px; font-size: 14px; font-weight: 700;
            cursor: pointer; font-family: inherit; text-align: left;
            transition: background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .div-tab:hover { background: #F7F8FA; color: #0B0F14; }
        .div-tab.is-active { background: #0B0F14; border-color: #0B0F14; color: #D9FF3A; box-shadow: 0 8px 20px -8px rgba(11,15,20,.45); }

        .div-tab-count { padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: 800; background: #F7F8FA; color: rgba(11,15,20,.5); }
        .div-tab.is-active .div-tab-count { background: rgba(255,255,255,.15); color: #FFFFFF; }

        .div-panel { display: none; }
        .div-panel.is-active { display: block; }
    </style>

    <!-- ===================== SCRIPT ===================== -->
    <script>
        /* 1. Logika filter + AJAX (Alpine — DOM statis, aman) */
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManagement', () => ({
                selectedClass: '{{ $classes->count() > 0 ? $classes->first()->id : "unassigned" }}',
                searchQuery: '',
                isLoading: false,
                htmlContent: '',

                async fetchData() {
                    if (!this.selectedClass) return;
                    this.isLoading = true;
                    try {
                        const params = new URLSearchParams({ class_id: this.selectedClass, search: this.searchQuery });
                        const response = await fetch(`{{ route('admin.users.index') }}?${params.toString()}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        this.htmlContent = await response.text();
                    } catch (error) {
                        console.error('Gagal menarik data:', error);
                        this.htmlContent = '<div class="rounded-[24px] bg-[#0B0F14] p-6 text-center text-sm font-bold text-white">Gagal memuat data. Silakan coba lagi.</div>';
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });

        /* 2. Event delegation untuk konten AJAX (tab divisi + tombol edit).
              Listener menempel pada elemen statis → tetap bekerja walau
              isi container diganti-ganti hasil fetch. */
        document.addEventListener('DOMContentLoaded', function () {
            var container = document.getElementById('ajaxContent');
            if (!container) return;

            container.addEventListener('click', function (e) {
                /* Tab divisi */
                var tab = e.target.closest('[data-div-tab]');
                if (tab) {
                    var id = tab.dataset.divTab;
                    container.querySelectorAll('[data-div-tab]').forEach(function (t) {
                        t.classList.toggle('is-active', t.dataset.divTab === id);
                    });
                    container.querySelectorAll('[data-div-panel]').forEach(function (p) {
                        p.classList.toggle('is-active', p.dataset.divPanel === id);
                    });
                    return;
                }

                /* Tombol edit user → buka modal via CustomEvent (aman lintas x-html) */
                var editBtn = e.target.closest('[data-edit-user]');
                if (editBtn) {
                    try {
                        var user = JSON.parse(editBtn.dataset.user);
                        window.dispatchEvent(new CustomEvent('open-edit-user', { detail: user }));
                    } catch (err) {
                        console.error('Data user tidak valid:', err);
                    }
                }
            });
        });
    </script>
</x-app-layout>