<div>
    @if (count($adjstData) > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 w-full mt-2">
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
    @endif
</div>
