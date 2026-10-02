@if($reports->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-neutral-bg text-text-secondary border-b border-neutral-border">
                <tr>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-calendar-event mr-1"></i> Minggu</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-file-description mr-1"></i> Detail Laporan</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-category mr-1"></i> Divisi & Jenis</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-activity mr-1"></i> Status</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px] text-center"><i class="ti ti-settings mr-1"></i> Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @foreach($reports as $report)
                    @php
                        $isRevision = $report->status === 'REVISION_REQUIRED';
                        $rowClass = $isRevision 
                            ? 'bg-semantic-warningBg/40 border-l-4 border-l-semantic-warning hover:bg-semantic-warningBg/60' 
                            : 'hover:bg-neutral-bg border-l-4 border-l-transparent';
                    @endphp
                    
                    <tr class="transition-colors group {{ $rowClass }}">
                        <td class="px-6 py-4">
                            <div class="w-12 h-12 rounded-xl bg-primary-light text-primary flex flex-col items-center justify-center shadow-sm border border-primary/10">
                                <span class="text-[10px] font-bold uppercase tracking-wider -mb-1 opacity-70">Wk</span>
                                <span class="text-xl font-extrabold">{{ $report->projectWeek->week_number }}</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <p class="font-bold text-text-primary truncate max-w-[250px] text-base">{{ $report->title }}</p>
                            <p class="text-xs text-text-secondary mt-1 flex items-center">
                                <i class="ti ti-clock mr-1 text-text-muted"></i> {{ $report->created_at->format('d M Y, H:i') }}
                            </p>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1.5">
                                <span class="font-bold text-text-primary text-sm">{{ $report->division->name }}</span>
                                @if($report->type === 'PERSONAL')
                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-neutral-surfaceSecondary text-text-secondary rounded-md border border-neutral-border tracking-wide">LAPORAN PERSONAL</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-accent/20 text-primary rounded-md border border-accent/50 tracking-wide">LAPORAN DIVISI</span>
                                @endif
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = [
                                    'SUBMITTED' => ['color' => 'bg-primary-light text-primary', 'icon' => 'ti-send', 'label' => 'Menunggu Review'],
                                    'REVIEWED' => ['color' => 'bg-neutral-surfaceSecondary text-text-secondary', 'icon' => 'ti-eye', 'label' => 'Sedang Direview'],
                                    'APPROVED' => ['color' => 'bg-semantic-successBg text-semantic-success', 'icon' => 'ti-circle-check', 'label' => 'Disetujui'],
                                    'REVISION_REQUIRED' => ['color' => 'bg-semantic-warning text-white', 'icon' => 'ti-alert-triangle', 'label' => 'Perlu Revisi'],
                                ];
                                $conf = $statusConfig[$report->status] ?? ['color' => 'bg-neutral-bg text-text-primary', 'icon' => 'ti-file', 'label' => $report->status];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold shadow-sm {{ $conf['color'] }}">
                                <i class="ti {{ $conf['icon'] }} mr-1.5 text-sm"></i> {{ $conf['label'] }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('reports.show', $report->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-neutral-border text-text-secondary hover:text-primary hover:bg-primary-light hover:border-primary/30 transition-all bg-white shadow-sm" title="Lihat Detail">
                                    <i class="ti ti-eye text-lg"></i>
                                </a>
                                
                                @if($report->status !== 'APPROVED')
                                    <a href="{{ route('reports.edit', $report->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-neutral-border text-text-secondary hover:text-semantic-warning hover:bg-semantic-warningBg hover:border-semantic-warning/30 transition-all bg-white shadow-sm" title="{{ $isRevision ? 'Buat Ulang Laporan' : 'Edit Laporan' }}">
                                        <i class="ti ti-edit text-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Area Pagination -->
    <div class="p-4 border-t border-neutral-border bg-neutral-bg" id="pagination-links">
        {{ $reports->links() }}
    </div>
    
@else
    <!-- Tampilan Jika Kosong -->
    <div class="p-16 text-center flex flex-col items-center justify-center animate-fade-in">
        <div class="w-24 h-24 bg-neutral-bg rounded-full flex items-center justify-center mb-5 border border-dashed border-neutral-border shadow-sm">
            <i class="ti ti-file-off text-5xl text-text-muted"></i>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">Laporan Tidak Ditemukan</h3>
        <p class="text-sm text-text-secondary max-w-md mx-auto mb-8 leading-relaxed">
            Tidak ada laporan yang sesuai dengan pencarianmu saat ini, atau kamu belum pernah membuat laporan.
        </p>
    </div>
@endif