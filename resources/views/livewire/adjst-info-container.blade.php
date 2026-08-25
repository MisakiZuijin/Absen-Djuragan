<div class="flex flex-wrap gap-2">

    @if (count($adjstData) > 1)
        <div wire:click="moveDataPos(false)"
            class="relative group container cursor-pointer rounded-lg text-center flex w-14 justify-center items-center hover:bg-gray-200">
            <div class="w-0 h-0 border-t-8 border-r-8 border-b-8 border-transparent border-r-black" onclick="moveLeft()">
            </div>
        </div>
    @endif

    @include('users.component.attd_status_component', [
        'message' => $singleAdjstData->start_time_message ?? null,
        'type' => 'Ganti Jam ' . $currentIndex + 1,
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


    @if (count($adjstData) > 1)
        <div wire:click="moveDataPos(true)"
            class="relative group container cursor-pointer rounded-lg text-center flex w-14 justify-center items-center hover:bg-gray-200">
            <div class="w-0 h-0 border-t-8 border-l-8 border-b-8 border-transparent border-l-black"></div>
        </div>
    @endif

</div>
