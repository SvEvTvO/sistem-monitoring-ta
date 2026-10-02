@forelse($targets as $target)
    @php
        $now = \Carbon\Carbon::now();
        $deadline = \Carbon\Carbon::parse($target->deadline)->endOfDay();
        $start = \Carbon\Carbon::parse($target->start_date)->startOfDay();
        
        $isCompleted = !is_null($target->completed_at);
        $isUpcoming = $start->isFuture();
        
        if ($isCompleted) {
            $daysStatus = 'Selesai ' . \Carbon\Carbon::parse($target->completed_at)->diffForHumans();
            $badgeColor = 'bg-semantic-successBg text-semantic-success border-semantic-success/20';
            $icon = 'ti-circle-check text-semantic-success bg-semantic-successBg';
            $statusText = 'COMPLETED';
        } elseif ($isUpcoming) {
            $daysStatus = 'Mulai ' . $start->diffForHumans();
            $badgeColor = 'bg-neutral-surfaceSecondary text-text-secondary border-neutral-border';
            $icon = 'ti-calendar-time text-text-muted bg-neutral-bg border border-neutral-border';
            $statusText = 'NOT STARTED';
        } else {
            $daysLeft = $now->diffInDays($deadline, false);
            if ($daysLeft < 0) {
                $daysStatus = 'Terlewat ' . abs(ceil($daysLeft)) . ' Hari';
                $badgeColor = 'bg-semantic-dangerBg text-semantic-danger border-semantic-danger/20';
                $icon = 'ti-alert-circle text-semantic-danger bg-semantic-dangerBg';
                $statusText = 'OVERDUE';
            } elseif ($daysLeft == 0) {
                $daysStatus = 'Deadline Hari Ini!';
                $badgeColor = 'bg-semantic-warningBg text-semantic-warning border-semantic-warning/20';
                $icon = 'ti-clock-exclamation text-semantic-warning bg-semantic-warningBg';
                $statusText = 'DUE TODAY';
            } else {
                $daysStatus = 'Sisa ' . ceil($daysLeft) . ' Hari';
                $badgeColor = 'bg-primary-light text-primary border-primary/20';
                $icon = 'ti-clock-play text-primary bg-primary-light';
                $statusText = 'IN PROGRESS';
            }
        }

        $canEdit = false;
        if ($isProjectLeader) {
            $canEdit = true;
        } elseif (isset($isDivisionLeader) && $isDivisionLeader && $target->division_id == $ledDivision->id) {
            $canEdit = true;
        }
    @endphp

    <div class="p-5 hover:bg-neutral-bg transition-colors flex flex-col md:flex-row md:items-center gap-4 group border-b border-neutral-border last:border-0">
        <div class="hidden md:flex w-12 h-12 rounded-full items-center justify-center shrink-0 {{ $icon }}"></div>

        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-text-primary {{ $isCompleted ? 'line-through opacity-70' : '' }}">{{ $target->title }}</h3>
                <span class="md:hidden text-[10px] font-bold px-2 py-0.5 rounded border {{ $badgeColor }}">{{ $statusText }}</span>
            </div>
            
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-text-secondary mt-1.5">
                @if($target->division_id)
                    <span class="flex items-center text-accent-dark font-bold"><i class="ti ti-users-group mr-1"></i> Divisi</span>
                @else
                    <span class="flex items-center text-primary font-bold"><i class="ti ti-building mr-1"></i> Global Project</span>
                @endif
                <span class="flex items-center border-l border-neutral-border pl-4">
                    <i class="ti ti-calendar-event mr-1.5 opacity-70"></i> Mulai: {{ $start->format('d M Y') }}
                </span>
                <span class="flex items-center border-l border-neutral-border pl-4">
                    <i class="ti ti-flag mr-1.5 opacity-70"></i> Deadline: <span class="{{ !$isCompleted && $now->diffInDays($deadline, false) <= 0 ? 'text-semantic-danger font-bold ml-1' : 'ml-1' }}">{{ $deadline->format('d M Y') }}</span>
                </span>
            </div>
        </div>

        <div class="flex items-center justify-between md:justify-end gap-4 mt-3 md:mt-0">
            <div class="text-left md:text-right mr-2">
                <span class="hidden md:inline-block text-[10px] font-bold px-2 py-0.5 rounded border mb-1 {{ $badgeColor }}">{{ $statusText }}</span>
                <p class="text-sm font-bold {{ str_contains($daysStatus, 'Terlewat') ? 'text-semantic-danger' : (str_contains($daysStatus, 'Sisa') || str_contains($daysStatus, 'Hari Ini') ? 'text-primary' : 'text-text-secondary') }}">
                    {{ $daysStatus }}
                </p>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="flex items-center gap-2">
                <a href="{{ route('targets.show', $target->id) }}" class="w-9 h-9 rounded-lg border border-neutral-border text-text-secondary hover:text-primary hover:bg-primary-light hover:border-primary/30 transition-all bg-white shadow-sm flex items-center justify-center" title="Lihat Detail">
                    <i class="ti ti-eye text-lg"></i>
                </a>
                
                @if($canEdit && $isUpcoming && !$isCompleted)
                    <!-- Tombol Hapus (Hanya muncul jika belum mulai) -->
                    <form action="{{ route('targets.destroy', $target->id) }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini secara permanen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-9 h-9 rounded-lg border border-neutral-border text-text-secondary hover:text-semantic-danger hover:bg-semantic-dangerBg hover:border-semantic-danger/30 transition-all bg-white shadow-sm flex items-center justify-center" title="Hapus Target">
                            <i class="ti ti-trash text-lg"></i>
                        </button>
                    </form>
                @endif
                
                @if($canEdit && !$isCompleted)
                    <a href="{{ route('targets.edit', $target->id) }}" class="w-9 h-9 rounded-lg border border-neutral-border text-text-secondary hover:text-semantic-warning hover:bg-semantic-warningBg hover:border-semantic-warning/30 transition-all bg-white shadow-sm flex items-center justify-center" title="Edit Target">
                        <i class="ti ti-edit text-lg"></i>
                    </a>
                    <form action="{{ route('targets.complete', $target) }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Tandai target ini sebagai selesai?')">
                        @csrf
                        <button type="submit" class="w-9 h-9 rounded-lg bg-white border border-neutral-border text-text-muted hover:text-semantic-success hover:border-semantic-success hover:bg-semantic-successBg transition-all flex items-center justify-center shadow-sm" title="Tandai Selesai">
                            <i class="ti ti-check text-lg"></i>
                        </button>
                    </form>
                @elseif($canEdit && $isCompleted)
                    <div class="w-9 h-9 rounded-lg bg-semantic-successBg text-semantic-success flex items-center justify-center shadow-inner">
                        <i class="ti ti-check text-lg"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="p-16 text-center animate-fade-in">
        <div class="w-24 h-24 bg-neutral-bg rounded-full flex items-center justify-center mx-auto mb-5 border border-dashed border-neutral-border shadow-sm">
            <i class="ti ti-target-off text-5xl text-text-muted"></i>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">Target Tidak Ditemukan</h3>
        <p class="text-sm text-text-secondary max-w-md mx-auto leading-relaxed">Belum ada target, atau tidak ada yang sesuai dengan pencarian.</p>
    </div>
@endforelse

<!-- Pagination -->
@if($targets->hasPages())
    <div class="p-4 bg-neutral-bg" id="pagination-links">
        {{ $targets->links() }}
    </div>
@endif