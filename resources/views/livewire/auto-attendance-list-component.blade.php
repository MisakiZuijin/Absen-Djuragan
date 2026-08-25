<div>
    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="py-3 px-6 text-left">No</th>
                <th class="py-3 px-6 text-left">Nama</th>
                <th class="py-3 px-6 text-left">Tanggal</th>
                <th class="py-3 px-6 text-left">Kantor</th>
                <th class="py-3 px-6 text-left">Shift</th>
                <th class="py-3 px-6 text-left">Jam Masuk</th>
            </tr>
        </thead>
        <tbody id="teamTableBody">
            @if (isset($autoAttdData) && count($autoAttdData) > 0)
                @foreach ($autoAttdData as $key => $item)
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-6">{{ $key + 1 }}</td>
                        <td class="py-4 px-6">
                            <a href="{{ route('admin.detail.autoAttd', ['id' => $item['name']]) }}"
                                class="text-blue-500 hover:underline cursor-pointer">
                                {{ $item['name'] }}
                            </a>
                        </td>
                        <td class="py-4 px-6">{{ $item['date'] }}</td>
                        <td class="py-4 px-6">{{ $item['office'] }}</td>
                        <td class="py-4 px-6">{{ $item['shift'] }}</td>
                        <td class="py-4 px-6">{{ $item['start_time'] }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="py-4 px-6 text-center" colspan="6">Data tidak ditemukan</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
