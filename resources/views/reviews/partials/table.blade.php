@if($reportsToReview->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-neutral-bg text-text-secondary border-b border-neutral-border">
                <tr>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-user mr-1"></i> Penulis</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-file-description mr-1"></i> Detail Laporan</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-category mr-1"></i> Divisi & Minggu</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]"><i class="ti ti-activity mr-1"></i> Status</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px] text-center"><i class="ti ti-settings mr-1"></i> Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @foreach($reportsToReview as $report)
                    <!-- Highlight Laporan yang berstatus SUBMITTED (Perlu tindakan) -->
                    @php
                        $isPending = $report->status === 'SUBMITTED';
                        $rowClass = $isPending 
                            ? 'bg-primary/5 border-l-4 border-l-primary hover:bg-primary/10' 
                            : 'hover:bg-neutral-bg border-l-4 border-l-transparent';
                    @endphp
                    
                    <tr class="transition-colors group {{ $rowClass }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-neutral-surfaceSecondary text-text-secondary flex items-center justify-center font-bold text-xs border border-neutral-border shadow-sm">
                                    {{ substr($report->author->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-text-primary">{{ $report->author->name }}</p>
                                    @if($report->type === 'DIVISION')
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 bg-accent text-text-primary rounded-md uppercase tracking-wider">Ketua Divisi</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <p class="font-bold text-text-primary truncate max-w-[200px] text-sm">{{ $report->title }}</p>
                            <p class="text-[11px] text-text-secondary mt-1 flex items-center">
                                <i class="ti ti-clock mr-1 text-text-muted"></i> Dikirim: {{ $report->created_at->format('d M, H:i') }}
                            </p>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1">
                                <span class="font-semibold text-text-primary text-sm">{{ $report->division->name }}</span>
                                <span class="text-xs text-text-secondary flex items-center">
                                    <i class="ti ti-calendar-event mr-1 text-text-muted"></i> Minggu {{ $report->projectWeek->week_number }}
                                </span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = [
                                    'SUBMITTED' => ['color' => 'bg-primary-light text-primary', 'icon' => 'ti-send', 'label' => 'Menunggu Review'],
                                    'REVIEWED' => ['color' => 'bg-neutral-surfaceSecondary text-text-secondary', 'icon' => 'ti-eye', 'label' => 'Sedang Direview'],
                                    'APPROVED' => ['color' => 'bg-semantic-successBg text-semantic-success', 'icon' => 'ti-circle-check', 'label' => 'Disetujui'],
                                    'REVISION_REQUIRED' => ['color' => 'bg-semantic-warningBg text-semantic-warning', 'icon' => 'ti-alert-triangle', 'label' => 'Perlu Revisi'],
                                ];
                                $conf = $statusConfig[$report->status] ?? ['color' => 'bg-neutral-bg text-text-primary', 'icon' => 'ti-file', 'label' => $report->status];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold shadow-sm {{ $conf['color'] }}">
                                <i class="ti {{ $conf['icon'] }} mr-1.5 text-sm"></i> {{ $conf['label'] }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('reviews.show', $report->id) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg font-bold transition-all shadow-sm {{ $isPending ? 'bg-primary text-white hover:bg-primary-dark' : 'bg-white border border-neutral-border text-text-secondary hover:text-primary hover:border-primary/30' }}">
                                <i class="ti {{ $isPending ? 'ti-file-check' : 'ti-eye' }} mr-1.5 text-lg"></i> 
                                {{ $isPending ? 'Periksa' : 'Lihat' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-neutral-border bg-neutral-bg" id="pagination-links">
        {{ $reportsToReview->links() }}
    </div>
@else
    <div class="p-16 text-center flex flex-col items-center justify-center animate-fade-in">
        <div class="w-24 h-24 bg-neutral-bg rounded-full flex items-center justify-center mb-5 border border-dashed border-neutral-border shadow-sm">
            <i class="ti ti-shield-check text-5xl text-text-muted"></i>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">Semua Aman!</h3>
        <p class="text-sm text-text-secondary max-w-md mx-auto mb-8 leading-relaxed">
            Tidak ada laporan anggota yang perlu kamu review saat ini, atau tidak ada yang cocok dengan pencarianmu.
        </p>
    </div>
@endif