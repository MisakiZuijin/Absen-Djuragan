<div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
        <table class="min-w-full bg-white text-xs sm:text-sm divide-y divide-slate-200">
            <thead class="bg-slate-100 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="py-3 px-4 text-left">No</th>
                    <th class="py-3 px-4 text-left">Pemagang</th>
                    <th class="py-3 px-4 text-left">Tanggal</th>
                    <th class="py-3 px-4 text-left">Kantor & Shift</th>
                    <th class="py-3 px-4 text-left">Jam Masuk</th>
                    <th class="py-3 px-4 text-left">Jam Pulang</th>
                    <th class="py-3 px-4 text-left">Catatan Admin</th>
                    <th class="py-3 px-4 text-center">Status Notif</th>
                </tr>
            </thead>
            <tbody id="teamTableBody" class="divide-y divide-slate-100">
                @if (isset($autoAttdData) && count($autoAttdData) > 0)
                    @php
                        $currentPage = $meta['current_page'] ?? 1;
                        $startIndex = ($currentPage - 1) * 10;
                    @endphp
                    @foreach ($autoAttdData as $key => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-medium text-slate-500">{{ $startIndex + $key + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div>
                                    @if(!empty($item['intern_id']))
                                        <a href="{{ route('admin.presence.detail', ['intern_id' => $item['intern_id']]) }}"
                                            class="text-blue-600 hover:text-blue-800 font-bold hover:underline leading-tight block">
                                            {{ $item['name'] }}
                                        </a>
                                    @else
                                        <span class="text-blue-600 font-bold leading-tight block">
                                            {{ $item['name'] }}
                                        </span>
                                    @endif
                                    <div class="flex items-center gap-1.5 text-[11px] text-gray-500 mt-1">
                                        <span class="font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">
                                            {{ $item['division'] ?? 'Tanpa Divisi' }}
                                        </span>
                                        @if(!empty($item['school']))
                                            <span class="text-gray-300">•</span>
                                            <span class="text-gray-400 truncate max-w-[150px]">{{ $item['school'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">{{ $item['date'] }}</td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <div class="font-medium">{{ $item['shift'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item['office'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium whitespace-nowrap">{{ $item['start_time'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-red-600 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2 py-0.5 rounded-md border border-red-200 text-xs">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                                    {{ $item['end_time'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                @if (!empty($item['auto_end_note']))
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs italic border border-slate-200">
                                        "{{ $item['auto_end_note'] }}"
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if (!empty($item['auto_end_notified']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-check text-[10px]"></i> Sudah Dilihat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Belum Dilihat
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="py-8 px-6 text-center text-slate-500" colspan="8">
                            <div class="flex flex-col items-center justify-center py-4">
                                <i class="fa-solid fa-inbox text-3xl text-slate-300 mb-2"></i>
                                <p class="text-sm font-medium">Tidak ada data riwayat pulang otomatis.</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
