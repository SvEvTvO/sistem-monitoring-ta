{{-- ================================================================
     PARTIAL AJAX — dirender UserController@index (request AJAX).
     TIDAK ada direktif Alpine di sini (konten x-html tidak di-init
     ulang oleh Alpine). Interaksi memakai data-* + delegation induk.
================================================================ --}}

@if ($isUnassigned ?? false)
    {{-- ============ MODE "BELUM PUNYA KELAS": blok project DISEMBUNYIKAN ============ --}}
    <section class="overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

        <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0B0F14] text-[#D9FF3A]">
                    <i class="ti ti-user-question text-xl"></i>
                </span>
                <div class="min-w-0">
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Anggota Belum Punya Kelas</h3>
                    <p class="mt-0.5 text-xs font-medium text-[#0B0F14]/45">Akun terdaftar yang belum dimasukkan ke Rombel manapun.</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-[#D9FF3A] px-3.5 py-1.5 text-[11px] font-extrabold text-[#0B0F14]">
                <i class="ti ti-users text-xs"></i> {{ $usersInClass->count() }} Pengguna
            </span>
        </div>

        @if ($usersInClass->count() > 0)
            <div class="grid grid-cols-1 gap-3 border-t border-[#0B0F14]/10 bg-[#F7F8FA]/50 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($usersInClass as $u)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-[#0B0F14]/10 bg-white p-4 transition-all hover:border-[#0245EC]/30 hover:shadow-sm">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#0B0F14]/10 bg-[#F7F8FA] text-xs font-black text-[#0B0F14]/60">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-[#0B0F14]">{{ $u->name }}</p>
                                <p class="truncate text-[11px] font-medium text-[#0B0F14]/45">{{ $u->username }} &bull; {{ $u->email }}</p>
                            </div>
                        </div>
                        <button type="button" data-edit-user data-user="{{ json_encode(['id' => $u->id, 'name' => $u->name, 'username' => $u->username, 'email' => $u->email]) }}" title="Edit Data Akun" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]">
                            <i class="ti ti-edit text-lg"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center border-t border-[#0B0F14]/10 px-6 py-14 text-center">
                <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#D9FF3A]">
                    <i class="ti ti-circle-check text-4xl text-[#0B0F14]"></i>
                </span>
                <h4 class="text-lg font-black text-[#0B0F14]">Semua Anggota Sudah Berkelas</h4>
                <p class="mt-1.5 max-w-md text-sm font-medium text-[#0B0F14]/50">Tidak ada akun yang menganggur tanpa Rombel saat ini.</p>
            </div>
        @endif
    </section>

@else
    {{-- ============ MODE ROMBEL BIASA ============ --}}
    @if($project)
        <!-- Header Project -->
        <div class="mb-6 flex flex-col items-start justify-between gap-4 overflow-hidden rounded-[24px] bg-[#0B0F14] p-5 text-white shadow-[0_20px_45px_-18px_rgba(11,15,20,0.6)] md:flex-row md:items-center relative">
            <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-[#D9FF3A]/15 blur-2xl"></div>
            <div class="relative z-10 min-w-0">
                <span class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-[#D9FF3A]/30 bg-[#D9FF3A]/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-[#D9FF3A]">
                    Project Aktif
                </span>
                <h2 class="text-2xl font-black text-white">{{ $project->name }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-white/70">
                    <span class="flex items-center gap-1.5"><i class="ti ti-star-filled text-[#D9FF3A]"></i> Ketua: <strong class="text-white">{{ $project->leader->name ?? 'Belum ada' }}</strong></span>
                    <span class="text-white/25">&bull;</span>
                    <span class="flex items-center gap-1.5"><i class="ti ti-users-group"></i> Total {{ $project->divisions->count() }} Divisi</span>
                </div>
            </div>
        </div>

        @if($project->divisions->count() > 0)
            <!-- Tab Divisi (data-div-tab, ditangani delegation di halaman induk) -->
            <div class="div-tabs">
                @foreach($project->divisions as $i => $division)
                    <button type="button" data-div-tab="{{ $division->id }}" class="div-tab {{ $i === 0 ? 'is-active' : '' }}">
                        <i class="ti ti-users-group text-lg"></i>
                        {{ $division->name }}
                        <span class="div-tab-count">{{ $division->members->count() }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Panel per divisi -->
            <div class="min-h-[300px] overflow-hidden rounded-[24px] border border-[#0B0F14]/10 bg-white shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>

                @foreach($project->divisions as $i => $division)
                    <div data-div-panel="{{ $division->id }}" class="div-panel {{ $i === 0 ? 'is-active' : '' }} p-6">
                        <div class="mb-5 flex items-center gap-2.5">
                            <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                            <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Anggota Divisi {{ $division->name }}</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($division->members as $member)
                                @php
                                    $isLeader = $division->leader_user_id == $member->user->id;
                                    $isProjectLeader = $project->project_leader_id == $member->user->id;
                                @endphp
                                <div class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border p-4 transition-all sm:flex-row sm:items-center {{ $isLeader ? 'border-[#0245EC]/30 bg-[#0245EC]/5 shadow-sm' : 'border-[#0B0F14]/10 bg-white hover:border-[#0245EC]/30 hover:shadow-md' }}">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-black shadow-sm {{ $isLeader ? 'bg-[#0245EC] text-white ring-2 ring-[#0245EC]/20' : 'bg-[#0B0F14] text-[#D9FF3A]' }}">
                                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-black text-[#0B0F14]">{{ $member->user->name }}</p>
                                            <p class="truncate text-[10px] font-semibold text-[#0B0F14]/50">{{ $member->user->email }}</p>
                                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                @if($isProjectLeader)
                                                    <span class="rounded border border-[#0B0F14]/20 bg-[#0B0F14] px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#D9FF3A] shadow-sm">Ketua Project</span>
                                                @endif
                                                @if($isLeader)
                                                    <span class="rounded border border-[#0245EC]/20 bg-[#0245EC] px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-white shadow-sm">Ketua Divisi</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center justify-end gap-1.5 sm:border-l sm:border-[#0B0F14]/10 sm:pl-3">
                                        <!-- TOMBOL DETAIL -->
                                        <a href="{{ route('admin.users.show', $member->user->id) }}" title="Lihat Detail Profil" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]">
                                            <i class="ti ti-eye text-lg"></i>
                                        </a>
                                        <!-- TOMBOL EDIT -->
                                        <button type="button" data-edit-user data-user="{{ json_encode(['id' => $member->user->id, 'name' => $member->user->name, 'username' => $member->user->username, 'email' => $member->user->email]) }}" title="Edit Data Akun" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]">
                                            <i class="ti ti-edit text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full rounded-2xl border border-dashed border-[#0B0F14]/15 bg-[#F7F8FA] py-10 text-center">
                                    <p class="text-sm font-semibold text-[#0B0F14]/50">Divisi ini kosong.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-[24px] border border-[#0B0F14]/10 bg-white p-8 text-center shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
                <span class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA]">
                    <i class="ti ti-sitemap text-4xl text-[#0B0F14]/30"></i>
                </span>
                <p class="text-sm font-black text-[#0B0F14]">Struktur Divisi Kosong</p>
                <p class="mt-1 text-xs font-medium text-[#0B0F14]/50">Ketua Project belum membuat divisi untuk project ini.</p>
            </div>
        @endif
    @else
        <!-- Rombel dipilih tapi belum punya project -->
        <div class="flex flex-col items-center justify-center rounded-[28px] border border-[#0B0F14]/10 bg-white p-12 text-center shadow-[0_4px_24px_rgba(11,15,20,0.04)]">
            <span class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0B0F14]/10 bg-[#F7F8FA]">
                <i class="ti ti-rocket-off text-4xl text-[#0B0F14]/30"></i>
            </span>
            <h3 class="text-xl font-black text-[#0B0F14] mb-2">Belum Ada Project</h3>
            <p class="max-w-sm text-sm font-medium text-[#0B0F14]/50">Rombongan Kelas ini belum diinisiasi sebuah Project, sehingga belum ada Struktur Divisi yang bisa ditampilkan.</p>
        </div>
    @endif

    {{-- Semua siswa terdaftar di rombel ini --}}
    @if(isset($usersInClass) && $usersInClass->count() > 0)
        <div class="mt-8">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="h-4 w-1 rounded-full bg-[#D9FF3A]"></span>
                <h3 class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#0B0F14]/50">Semua Siswa Terdaftar di Rombel Ini</h3>
                <span class="rounded-full bg-[#0B0F14]/5 px-2 py-0.5 text-[10px] font-bold text-[#0B0F14]/45">{{ $usersInClass->count() }}</span>
                <span class="h-px flex-1 bg-[#0B0F14]/10"></span>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach($usersInClass as $u)
                    <div class="group flex items-center justify-between rounded-2xl border border-[#0B0F14]/10 bg-white p-3.5 transition-all hover:border-[#0245EC]/30 hover:shadow-sm">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#0B0F14]/10 bg-[#F7F8FA] text-[10px] font-black text-[#0B0F14]/60">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-[#0B0F14]">{{ $u->name }}</p>
                                <p class="truncate text-[10px] font-medium text-[#0B0F14]/40">{{ $u->username }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- TOMBOL DETAIL -->
                            <a href="{{ route('admin.users.show', $u->id) }}" title="Lihat Detail Profil" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]">
                                <i class="ti ti-eye text-lg"></i>
                            </a>
                            <!-- TOMBOL EDIT -->
                            <button type="button" data-edit-user data-user="{{ json_encode(['id' => $u->id, 'name' => $u->name, 'username' => $u->username, 'email' => $u->email]) }}" title="Edit Data Akun" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#0B0F14]/40 transition-colors hover:bg-[#0245EC]/10 hover:text-[#0245EC]">
                                <i class="ti ti-edit text-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif