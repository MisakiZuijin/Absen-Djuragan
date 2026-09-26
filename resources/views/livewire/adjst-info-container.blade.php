@if (count($adjstData) > 0)
<div class="w-full bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 sm:p-4 mt-2">
    <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200/60">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <span class="text-xs font-bold text-slate-800">Sesi Ganti Jam ({{ $currentIndex + 1 }} dari {{ count($adjstData) }})</span>
        </div>
        @if (count($adjstData) > 1)
        <div class="flex items-center gap-1.5">
            <button type="button" wire:click="moveDataPos(false)"
                class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs shadow-2xs transition"
                title="Sesi Sebelumnya">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
            <button type="button" wire:click="moveDataPos(true)"
                class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs shadow-2xs transition"
                title="Sesi Selanjutnya">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        @include('users.component.attd_status_component', [
            'message' => $singleAdjstData->start_time_message ?? null,
            'type' => 'Masuk Ganti Jam',
            'absence_category' => $singleAdjstData->start_time ?? null,
            'isAdjustable' => true,
        ])
        @include('users.component.attd_status_component', [
            'message' => $singleAdjstData->break_time_message ?? null,
            'type' => 'Istirahat',
            'absence_category' => $singleAdjstData->break_time ?? null,
            'isAdjustable' => true,
        ])
        @include('users.component.attd_status_component', [
            'message' => $singleAdjstData->back_time_message ?? null,
            'type' => 'Kembali',
            'absence_category' => $singleAdjstData->back_time ?? null,
            'isAdjustable' => true,
        ])
        @include('users.component.attd_status_component', [
            'message' => $singleAdjstData->end_time_message ?? null,
            'type' => 'Pulang Ganti Jam',
            'absence_category' => $singleAdjstData->end_time ?? null,
            'isAdjustable' => true,
        ])
    </div>
</div>
@endif
