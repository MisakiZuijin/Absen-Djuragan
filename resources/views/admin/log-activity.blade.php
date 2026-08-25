<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Activity {{ $profileName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Log Activity ({{ $profileName }})</h1>
        
        <!-- Table -->
        <div class="bg-white shadow-md rounded-lg mb-6 p-4">
            <!-- Table -->
            <table class="min-w-full table-auto bg-white text-sm text-gray-700">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Log Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logActivities as $index => $log)
                        <tr class="border-b hover:bg-gray-100">
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">{{ $log->date->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                            <td class="px-4 py-3 max-w-xs break-words">{{ $log->activity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
