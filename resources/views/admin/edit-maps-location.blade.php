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
        <h2 class="text-2xl font-bold mb-4">Halaman Edit Kantor | Admin</h2>
        <form id="addOfficeForm" action="{{ route('offices.update', ['id' => $office->id]) }}" method="POST">
            @csrf
            <div class="mb-2">
                <label for="namaKantor" class="block text-gray-700">Nama Kantor<span
                    class="text-red-500">*</span></label>
                <input type="text" id="namaKantor" name="namaKantor"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    value="{{ $office->name }}" required>
            </div>
            <div class="mb-2">
                <label for="alamatKantor" class="block text-gray-700">Alamat<span
                    class="text-red-500">*</span></label>
                <textarea id="alamatKantor" name="alamatKantor"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" required>{{ $office->address }}</textarea>
            </div>
            <div class="mb-2">
                <label for="kapasitasKantor" class="block text-gray-700">Kapasitas<span
                    class="text-red-500">*</span></label>
                <input type="number" id="kapasitasKantor" value="{{ $office->capacity }}" name="kapasitasKantor"
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
                        value="{{ $coordinates->get(0)->latitude ?? '' }}" required>
                </div>
                <div class="mb-2">
                    <label for="longitudeoffice" class="block text-gray-700">Kantor Longitude<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longitudeoffice" name="longitudeoffice"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        value="{{ $coordinates->get(0)->longitude ?? '' }}" required>
                </div>
                <div class="mb-2">
                    <label for="latitulefttop" class="block text-gray-700">Latitude Kiri Atas<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="latitulefttop" name="latitulefttop"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        value="{{ $coordinates->get(1)->latitude ?? '' }}" required>
                </div>
                <div class="mb-2">
                    <label for="longitudelefttop" class="block text-gray-700">Longitude Kiri Atas<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longitudelefttop" name="longitudelefttop"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        value="{{ $coordinates->get(1)->longitude ?? '' }}" required>
                </div>
                <div class="mb-2">
                    <label for="latiturightbottom" class="block text-gray-700">Latitude Kanan Bawah<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="latiturightbottom" name="latiturightbottom"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        value="{{ $coordinates->get(2)->latitude ?? '' }}" required>
                </div>
                <div class="mb-2">
                    <label for="longituderightbottom" class="block text-gray-700">Longitude Kanan Bawah<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="longituderightbottom" name="longituderightbottom"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        value="{{ $coordinates->get(2)->longitude ?? '' }}" required>
                </div>
            </div>
            <div class="flex justify-end">
                <a href="{{ Route('admin.pengaturan.kantor') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>

    </div>

    <script>
        // Inisialisasi peta
        const map = L.map('map').setView([-7.790366860887182, 110.40936457008371], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 50
        }).addTo(map);

        var mainMarker = null;
        var boundary = null;
        var officeName = document.getElementById("namaKantor").value;


        // Fungsi untuk menggambar boundary
        function drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng) {
            if (boundary) {
                map.removeLayer(boundary); // Hapus boundary sebelumnya jika ada
            }

            var bounds = [
                [leftTopLat, leftTopLng], // Kiri Atas
                [rightBottomLat, rightBottomLng] // Kanan Bawah
            ];

            boundary = L.rectangle(bounds, {
                color: "#ff7800",
                weight: 1
            }).addTo(map);
        }

        // Fungsi untuk memuat data saat tombol edit diklik
        function loadCoordinates(officeLat, officeLng, leftTopLat, leftTopLng, rightBottomLat, rightBottomLng) {
            // Hapus marker sebelumnya jika ada
            if (mainMarker) {
                map.removeLayer(mainMarker);
            }

            // Tambahkan marker kantor
            mainMarker = L.marker([officeLat, officeLng], {
                    draggable: true
                }).addTo(map)
                .bindPopup(officeName)
                .openPopup();

            // Tampilkan boundary berdasarkan koordinat yang di-load dari database
            drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng);

            // Isi input koordinat di form
            document.getElementById('latitudeoffice').value = officeLat;
            document.getElementById('longitudeoffice').value = officeLng;
            document.getElementById('latitulefttop').value = leftTopLat;
            document.getElementById('longitudelefttop').value = leftTopLng;
            document.getElementById('latiturightbottom').value = rightBottomLat;
            document.getElementById('longituderightbottom').value = rightBottomLng;

            // Event listener untuk dragend marker kantor
            mainMarker.on('dragend', function(e) {
                var latLng = mainMarker.getLatLng();
                document.getElementById('latitudeoffice').value = latLng.lat;
                document.getElementById('longitudeoffice').value = latLng.lng;
            });
        }

        // Event listener untuk klik di peta
        map.on('click', function(e) {
            if (mainMarker) {
                map.removeLayer(mainMarker); // Hapus marker sebelumnya jika ada
            }

            mainMarker = L.marker(e.latlng, {
                    draggable: true
                }).addTo(map)
                .bindPopup(officeName).openPopup();

            var distanceInMeters = document.getElementById("radius").value ?? 25;
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            // Isi input field dengan koordinat
            document.getElementById('latitudeoffice').value = lat;
            document.getElementById('longitudeoffice').value = lng;

            var distance = calculateDistance(lat, distanceInMeters);

            var leftTopLat = lat + distance.latitude;
            var leftTopLng = lng - distance.longitude;

            var rightBottomLat = lat - distance.latitude;
            var rightBottomLng = lng + distance.longitude;

            // Isi input field dengan koordinat yang dihitung
            document.getElementById('latitulefttop').value = leftTopLat;
            document.getElementById('longitudelefttop').value = leftTopLng;
            document.getElementById('latiturightbottom').value = rightBottomLat;
            document.getElementById('longituderightbottom').value = rightBottomLng;

            // Gambar boundary di peta berdasarkan koordinat
            drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng);
        });

        // Fungsi untuk menghitung jarak
        function calculateDistance(lat, distanceInMeters) {
            const earthRadius = 6371000;

            const latitudeInDegrees = (distanceInMeters / earthRadius) * (180 / Math.PI);
            const longitudeInDegrees = (distanceInMeters / (earthRadius * Math.cos(Math.PI * lat / 180))) * (180 / Math.PI);

            return {
                latitude: latitudeInDegrees,
                longitude: longitudeInDegrees
            };
        }

        // Saat halaman dimuat, load koordinat dari database (contoh)
        window.onload = function() {
            // Contoh data yang diambil dari database (ubah sesuai dengan data dari backend)
            var officeLatitude = {{ $coordinates->get(0)->latitude ?? 0 }};
            var officeLongitude = {{ $coordinates->get(0)->longitude ?? 0 }};
            var leftTopLatitude = {{ $coordinates->get(1)->latitude ?? 0 }};
            var leftTopLongitude = {{ $coordinates->get(1)->longitude ?? 0 }};
            var rightBottomLatitude = {{ $coordinates->get(2)->latitude ?? 0 }};
            var rightBottomLongitude = {{ $coordinates->get(2)->longitude ?? 0 }};

            // Load koordinat ke peta
            loadCoordinates(officeLatitude, officeLongitude, leftTopLatitude, leftTopLongitude, rightBottomLatitude,
                rightBottomLongitude);
        };
    </script>
</body>

</html>
