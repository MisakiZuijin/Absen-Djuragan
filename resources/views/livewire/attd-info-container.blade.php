@php
// Tentukan kelas grid secara dinamis di sini
$gridClass = $isWithoutBreak
? 'sm:grid-cols-2 md:grid-cols-4' // Jika tanpa istirahat, grid memiliki 4 kolom di layar besar
: 'sm:grid-cols-3 md:grid-cols-6'; // Jika ada istirahat, grid memiliki 6 kolom di layar besar

// Fungsi helper untuk format waktu dengan penanganan null
$formatTime = function ($time) {
return $time ? \Carbon\Carbon::parse($time)->format('H:i:s') : null;
}
@endphp

<div class="grid grid-cols-2 gap-2 w-full mt-4 {{ $gridClass }}">

    @if ($isWithoutBreak)
    @include('users.component.attd_status_component', [
    'message' => $attdData->start_time_message ?? null,
    'type' => 'Masuk',
    'absence_category' => isset($attdData->start_time) ? $formatTime($attdData->start_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->permit_start_message ?? null,
    'type' => 'Izin',
    'absence_category' => isset($attdData->permit_start) ? $formatTime($attdData->permit_start) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->permit_back_message ?? null,
    'type' => 'Izin Kembali',
    'absence_category' => isset($attdData->permit_back) ? $formatTime($attdData->permit_back) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->end_time_message ?? null,
    'type' => 'Pulang',
    'absence_category' => isset($attdData->end_time) ? $formatTime($attdData->end_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @else
    @include('users.component.attd_status_component', [
    'message' => $attdData->start_time_message ?? null,
    'type' => 'Masuk',
    'absence_category' => isset($attdData->start_time) ? $formatTime($attdData->start_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->permit_start_message ?? null,
    'type' => 'Izin',
    'absence_category' => isset($attdData->permit_start) ? $formatTime($attdData->permit_start) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->permit_back_message ?? null,
    'type' => 'Izin Kembali',
    'absence_category' => isset($attdData->permit_back) ? $formatTime($attdData->permit_back) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->break_time_message ?? null,
    'type' => 'Istirahat',
    'absence_category' => isset($attdData->break_time) ? $formatTime($attdData->break_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->back_time_message ?? null,
    'type' => 'Kembali',
    'absence_category' => isset($attdData->back_time) ? $formatTime($attdData->back_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @include('users.component.attd_status_component', [
    'message' => $attdData->end_time_message ?? null,
    'type' => 'Pulang',
    'absence_category' => isset($attdData->end_time) ? $formatTime($attdData->end_time) : null,
    'isAdjustable' => $isAdjustable,
    ])
    @endif

</div>