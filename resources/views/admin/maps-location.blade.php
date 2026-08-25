<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maps Kantor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body class="h-screen flex">
    <!-- Map Container -->
    <div id="map" class="w-2/3 h-full"></div>

    <!-- Information Section -->
    <div id="info" class="w-1/3 h-full p-6 bg-gray-100 shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Halaman Tambah Kantor | Admin</h2>
        <form id="addOfficeForm" action="{{ route('offices.store') }}" method="POST">
            @csrf
            <div class="mb-2">
                <label for="namaKantor" class="block text-gray-700">Nama Kantor<span
                    class="text-red-500">*</span></label>
                <input type="text" id="namaKantor" name="namaKantor"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="mb-2">
                <label for="alamatKantor" class="block text-gray-700">Alamat<span
                    class="text-red-500">*</span></label>
                <textarea id="alamatKantor" name="alamatKantor"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" required></textarea>
            </div>
            <div class="mb-2">
                <label for="kapasitasKantor" class="block text-gray-700">Kapasitas<span
                    class="text-red-500">*</span></label>
                <input type="number" id="kapasitasKantor" name="kapasitasKantor"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="mb-2">
                <label for="radius" class="block text-gray-700">Radius Presensi (m<sup>2)<span
                    class="text-red-500">*</span></label>
                <input type="number" id="radius" name="radius" value="20"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <div class="mb-2">
                    <label for="latitudeoffice" class="block text-gray-700">Kantor Latitude<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="latitudeoffice" name="latitudeoffice"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-2">
                    <label for="longitudeoffice" class="block text-gray-700">Kantor Longitude<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longitudeoffice" name="longitudeoffice"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-2">
                    <label for="latitulefttop" class="block text-gray-700">Latitude Kiri Atas<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="latitulefttop" name="latitulefttop"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-2">
                    <label for="longitudelefttop" class="block text-gray-700">Longitude Kiri Atas<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longitudelefttop" name="longitudelefttop"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-2">
                    <label for="latiturightbottom" class="block text-gray-700">Latitude Kanan Bawah<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="latiturightbottom" name="latiturightbottom"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-2">
                    <label for="longituderightbottom" class="block text-gray-700">Longitude Kanan Bawah<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longituderightbottom" name="longituderightbottom"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
            </div>
            <div class="flex justify-end">
                <a type="button" href="{{ Route('admin.pengaturan.kantor') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>

    </div>

    <script src="{{ asset('js/admin/maps-location.js') }}"></script>
</body>

</html>
