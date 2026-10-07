<x-app-layout>
    <header class="mb-8">
        <div class="mb-2 flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#D9FF3A] shadow-sm"><i class="ti ti-history text-lg"></i></span>
            <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#0B0F14]/50">Administrator Panel</span>
        </div>
        <h1 class="text-3xl font-black tracking-tight text-[#0B0F14]">Catatan Audit Sistem</h1>
        <p class="mt-1 text-sm font-medium text-[#0B0F14]/50">Pantau seluruh riwayat aktivitas dan perubahan data secara real-time.</p>
    </header>

    <div class="overflow-hidden rounded-[28px] border border-[#0B0F14]/10 bg-white shadow-sm">
        <div class="h-2 w-full bg-gradient-to-r from-[#0245EC] to-[#D9FF3A]"></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#0B0F14]/10 bg-[#F7F8FA] text-[10px] font-extrabold uppercase tracking-[0.15em] text-[#0B0F14]/45">
                        <th class="px-6 py-5">Waktu Kejadian</th>
                        <th class="px-6 py-5">Pengguna (Aktor)</th>
                        <th class="px-6 py-5">Aktivitas & Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#0B0F14]/10">
                    @if($logs->count() > 0)
                        @foreach($logs as $log)
                            <tr class="hover:bg-[#F7F8FA]/50">
                                <td class="px-6 py-4 text-xs font-bold text-[#0B0F14]">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 text-sm font-black text-[#0245EC]">{{ $log->user->name ?? 'Sistem' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-block rounded bg-[#0B0F14]/10 px-2 py-0.5 text-[10px] font-extrabold uppercase">{{ $log->action ?? 'LOG' }}</span>
                                    <p class="mt-1 text-sm font-medium text-[#0B0F14]/70">{{ $log->description ?? '-' }}</p>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="3" class="px-6 py-12 text-center text-sm font-medium text-[#0B0F14]/40">Belum ada catatan aktivitas sistem.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($logs->hasPages()) <div class="border-t border-[#0B0F14]/10 bg-white p-4">{{ $logs->links() }}</div> @endif
    </div>
</x-app-layout>
