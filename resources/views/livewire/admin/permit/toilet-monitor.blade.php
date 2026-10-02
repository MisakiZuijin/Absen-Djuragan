<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s="loadInterns" @endif>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Daftar Intern</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timer</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($allInterns as $intern)
                                @php
                                    // Cari izin yang sedang aktif
                                    $activePermit = $intern->activePermitLog;
                                    $isToilet = $activePermit && $activePermit->type === 'toilet';
                                @endphp
                                <tr class="intern-row hover:bg-gray-50 transition-all duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- ... kode nama intern ... --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $intern->school->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $intern->detailProject->first()?->project?->nameProject?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($activePermit)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-{{ $activePermit->type === 'toilet' ? 'toilet' : 'walking' }} mr-1"></i> Sedang Izin {{ ucfirst($activePermit->type) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-check mr-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($activePermit)
                                            <div class="text-sm text-gray-900" 
                                                 x-data="timer('{{ \Carbon\Carbon::parse($activePermit->start_time)->toIso8601String() }}')" 
                                                 x-init="init()"
                                                 wire:key="timer-{{ $intern->id }}-{{ $activePermit->id }}">
                                                <span class="font-mono">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <span x-text="time"></span>
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500">-</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                {{-- ... kode jika tidak ada intern ... --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // AlpineJS ini sudah benar. Tidak perlu diubah.
        document.addEventListener('alpine:init', () => {
            Alpine.data('timer', (startTime) => ({
                time: '00:00:00',
                interval: null,
                init() {
                    let start = new Date(startTime);
                    if (this.interval) clearInterval(this.interval);
                    this.interval = setInterval(() => {
                        let diff = new Date() - start;
                        if(diff < 0) diff = 0;
                        let hours = String(Math.floor(diff / 3600000)).padStart(2, '0');
                        let minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                        let seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                        this.time = `${hours}:${minutes}:${seconds}`;
                    }, 1000);
                },
                destroy() {
                    clearInterval(this.interval);
                }
            }))
        })
    </script>
    @endpush
</div>