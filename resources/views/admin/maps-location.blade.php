<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Lokasi Kantor | Admin</title>
    <!-- Vite Tailwind CSS & FontAwesome -->
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* Custom Scrollbar for Form */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        #map {
            z-index: 1;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans h-screen flex flex-col md:flex-row overflow-hidden">

    <!-- LEFT SIDE: INTERACTIVE MAP CONTAINER (60% Width on Desktop) -->
    <div class="relative w-full md:w-7/12 lg:w-3/5 h-64 md:h-full flex-shrink-0">
        <!-- Leaflet Map Element -->
        <div id="map" class="w-full h-full"></div>

        <!-- Floating Map Search Box (Nominatim) -->
        <div class="absolute top-4 left-4 right-4 md:right-auto md:w-80 z-[1000]">
            <div class="relative bg-white/95 backdrop-blur-md rounded-2xl shadow-lg border border-slate-200/80 p-1.5 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs ml-2.5"></i>
                <input type="text" id="mapSearchInput" placeholder="Cari nama lokasi / alamat..."
                    class="w-full text-xs bg-transparent py-1.5 focus:outline-none text-slate-700 placeholder:text-slate-400">
                <button type="button" id="mapSearchBtn" onclick="searchLocation()"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-1">
                    <span>Cari</span>
                </button>
            </div>
            <!-- Search Suggestions Dropdown -->
            <div id="searchResultsList" class="hidden mt-1.5 bg-white/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200 max-h-48 overflow-y-auto divide-y divide-slate-100 text-xs"></div>
        </div>

        <!-- Floating Map Action Buttons (Locate GPS & Center) -->
        <div class="absolute bottom-6 left-4 z-[1000] flex flex-col gap-2">
            <button type="button" onclick="locateUserGPS()" 
                class="px-3.5 py-2 bg-white/95 hover:bg-white text-slate-700 hover:text-blue-600 rounded-xl shadow-md border border-slate-200 text-xs font-semibold flex items-center gap-2 transition backdrop-blur-sm group">
                <i class="fa-solid fa-crosshairs text-blue-600 group-hover:scale-110 transition-transform"></i>
                <span class="hidden sm:inline">Gunakan Lokasi Saya (GPS)</span>
            </button>
        </div>

        <!-- Floating Coordinates Indicator Badge -->
        <div class="absolute bottom-6 right-4 z-[1000] bg-slate-900/80 backdrop-blur-md text-white px-3 py-1.5 rounded-xl text-[11px] font-mono shadow-md flex items-center gap-2 border border-white/10">
            <i class="fa-solid fa-location-dot text-rose-400"></i>
            <span id="mapCoordsDisplay">Klik peta untuk memilih titik kantor</span>
        </div>
    </div>

    <!-- RIGHT SIDE: OFFICE CONFIGURATION FORM (40% Width on Desktop) -->
    <div class="w-full md:w-5/12 lg:w-2/5 h-full bg-white border-l border-slate-200 flex flex-col shadow-2xl z-10">
        
        <!-- Header Section -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60 flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pengaturan.kantor') }}" 
                    class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center transition shadow-2xs"
                    title="Kembali ke Daftar Kantor">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </a>
                <div>
                    <h1 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-building text-blue-600 text-sm"></i>
                        <span>Tambah Lokasi Kantor</span>
                    </h1>
                    <p class="text-[11px] text-slate-500">Tentukan titik koordinat dan lengkapi data kantor.</p>
                </div>
            </div>
        </div>

        <!-- Form Body (Scrollable Container) -->
        <form id="addOfficeForm" action="{{ route('offices.store') }}" method="POST" class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar text-xs">
            @csrf

            <!-- Card 1: Data Utama Kantor -->
            <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200/60">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">1</span>
                    <h2 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Data Utama Kantor</h2>
                </div>

                <!-- Nama Kantor -->
                <div>
                    <label for="namaKantor" class="block font-bold text-slate-700 mb-1">
                        Nama Kantor / Brand <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="namaKantor" name="namaKantor" required value="{{ old('namaKantor') }}"
                        placeholder="Contoh: Kantor Pusat Djuragan"
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                </div>

                <!-- Alamat Lengkap -->
                <div>
                    <label for="alamatKantor" class="block font-bold text-slate-700 mb-1">
                        Alamat Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="alamatKantor" name="alamatKantor" rows="2" required placeholder="Tuliskan alamat lengkap lokasi kantor..."
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition resize-none">{{ old('alamatKantor') }}</textarea>
                </div>

                <!-- Kapasitas Pemagang -->
                <div>
                    <label for="kapasitasKantor" class="block font-bold text-slate-700 mb-1">
                        Kapasitas Maksimal Pemagang (Orang) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-users absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="number" id="kapasitasKantor" name="kapasitasKantor" required min="1" value="{{ old('kapasitasKantor', 15) }}"
                            class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                    </div>
                </div>
            </div>

            <!-- Card 2: Titik Koordinat & Radius Presensi (Geofence) -->
            <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">2</span>
                        <h2 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Koordinat & Radius Presensi</h2>
                    </div>
                    <span id="coordStatusBadge" class="text-[10px] font-semibold bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-clock text-[9px]"></i> Belum Ditentukan
                    </span>
                </div>

                <!-- Radius Presensi (Meter) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="radius" class="font-bold text-slate-700">
                            Radius Toleransi Presensi (Meter) <span class="text-rose-500">*</span>
                        </label>
                        <span id="radiusValueLabel" class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">25 Meter</span>
                    </div>
                    <input type="number" id="radius" name="radius" required min="5" max="1000" value="{{ old('radius', 25) }}"
                        oninput="handleRadiusChange(this.value)"
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                    
                    <!-- Preset Radius Quick Buttons -->
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400 font-medium">Pilihan Cepat:</span>
                        <button type="button" onclick="setRadiusPreset(15)" class="px-2 py-0.5 bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 rounded-md text-[10px] font-medium transition">15m</button>
                        <button type="button" onclick="setRadiusPreset(25)" class="px-2 py-0.5 bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 rounded-md text-[10px] font-medium transition">25m</button>
                        <button type="button" onclick="setRadiusPreset(50)" class="px-2 py-0.5 bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 rounded-md text-[10px] font-medium transition">50m</button>
                        <button type="button" onclick="setRadiusPreset(100)" class="px-2 py-0.5 bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 rounded-md text-[10px] font-medium transition">100m</button>
                    </div>
                </div>

                <!-- Titik Pusat Koordinat (Latitude & Longitude) -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <div>
                        <label for="latitudeoffice" class="block font-semibold text-slate-600 text-[11px] mb-1">
                            Latitude Kantor <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="latitudeoffice" name="latitudeoffice" required readonly
                            class="w-full px-3 py-1.5 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 font-mono text-[11px] focus:outline-none"
                            placeholder="-7.790366">
                    </div>
                    <div>
                        <label for="longitudeoffice" class="block font-semibold text-slate-600 text-[11px] mb-1">
                            Longitude Kantor <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="longitudeoffice" name="longitudeoffice" required readonly
                            class="w-full px-3 py-1.5 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 font-mono text-[11px] focus:outline-none"
                            placeholder="110.409364">
                    </div>
                </div>

                <!-- Boundary Box Koordinat (Auto-Calculated) -->
                <div class="pt-2 border-t border-slate-200/60">
                    <p class="text-[10px] text-slate-400 mb-1.5 flex items-center gap-1 font-medium">
                        <i class="fa-solid fa-draw-polygon text-slate-400"></i>
                        <span>Batas Kotak Geofence (Otomatis dihitung):</span>
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                        <div>
                            <input type="text" id="latitulefttop" name="latitulefttop" required readonly
                                class="w-full px-2 py-1 bg-slate-100 border border-slate-200 rounded text-slate-500 font-mono" placeholder="Lat Kiri Atas">
                        </div>
                        <div>
                            <input type="text" id="longitudelefttop" name="longitudelefttop" required readonly
                                class="w-full px-2 py-1 bg-slate-100 border border-slate-200 rounded text-slate-500 font-mono" placeholder="Lng Kiri Atas">
                        </div>
                        <div>
                            <input type="text" id="latiturightbottom" name="latiturightbottom" required readonly
                                class="w-full px-2 py-1 bg-slate-100 border border-slate-200 rounded text-slate-500 font-mono" placeholder="Lat Kanan Bawah">
                        </div>
                        <div>
                            <input type="text" id="longituderightbottom" name="longituderightbottom" required readonly
                                class="w-full px-2 py-1 bg-slate-100 border border-slate-200 rounded text-slate-500 font-mono" placeholder="Lng Kanan Bawah">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: SOP, Peraturan & Piket (Opsional) -->
            <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200/60">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">3</span>
                    <h2 class="font-bold text-slate-800 text-xs uppercase tracking-wider">SOP & Peraturan Kantor (Opsional)</h2>
                </div>

                <!-- Link SOP Magang -->
                <div>
                    <label for="sop_url" class="block font-semibold text-slate-700 mb-1">
                        <i class="fa-solid fa-file-shield text-indigo-600 mr-1"></i> Link Dokumen SOP Magang (URL)
                    </label>
                    <input type="url" id="sop_url" name="sop_url" value="{{ old('sop_url') }}"
                        placeholder="https://docs.google.com/..."
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                </div>

                <!-- Link Peraturan & Poin Peraturan -->
                <div class="space-y-1.5">
                    <label for="rules_url" class="block font-semibold text-slate-700">
                        <i class="fa-solid fa-gavel text-amber-600 mr-1"></i> Peraturan Kantor
                    </label>
                    <input type="url" id="rules_url" name="rules_url" value="{{ old('rules_url') }}"
                        placeholder="Link URL Dokumen Peraturan (opsional)"
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                    <textarea id="rules_description" name="rules_description" rows="2" 
                        placeholder="Poin-poin tata tertib kantor (opsional)..."
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition resize-none">{{ old('rules_description') }}</textarea>
                </div>

                <!-- Link Piket & Poin Tugas Piket -->
                <div class="space-y-1.5">
                    <label for="piket_url" class="block font-semibold text-slate-700">
                        <i class="fa-solid fa-broom text-emerald-600 mr-1"></i> Jadwal & Ketentuan Piket
                    </label>
                    <input type="url" id="piket_url" name="piket_url" value="{{ old('piket_url') }}"
                        placeholder="Link Spreadsheet Jadwal Piket (opsional)"
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition">
                    <textarea id="piket_description" name="piket_description" rows="2" 
                        placeholder="Ketentuan / pembagian tugas piket harian..."
                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs transition resize-none">{{ old('piket_description') }}</textarea>
                </div>
            </div>

            <!-- Sticky Footer Action Buttons -->
            <div class="pt-2 pb-3 flex items-center justify-end gap-2.5">
                <a href="{{ route('admin.pengaturan.kantor') }}"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Simpan Data Kantor</span>
                </button>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT MAPS LOGIC -->
    <script>
        // Inisialisasi Peta Leaflet
        const defaultLat = -7.790366860887182;
        const defaultLng = 110.40936457008371;
        const map = L.map('map', {
            zoomControl: false
        }).setView([defaultLat, defaultLng], 17);

        // Zoom control di posisi kanan atas
        L.control.zoom({
            position: 'topright'
        }).addTo(map);

        // Tile Layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let mainMarker = null;
        let boundaryRectangle = null;
        let boundaryCircle = null;

        // Fungsi menggambar visual geofence (Circle & Rectangle)
        function drawBoundary(lat, lng, radiusMeters, leftTopLat, leftTopLng, rightBottomLat, rightBottomLng) {
            // Hapus layer lama jika ada
            if (boundaryRectangle) map.removeLayer(boundaryRectangle);
            if (boundaryCircle) map.removeLayer(boundaryCircle);

            // Gambar lingkaran radius presensi
            boundaryCircle = L.circle([lat, lng], {
                radius: radiusMeters,
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.15,
                weight: 2,
                dashArray: '4, 6'
            }).addTo(map);

            // Gambar kotak boundary koordinat
            const bounds = [
                [leftTopLat, leftTopLng],
                [rightBottomLat, rightBottomLng]
            ];
            boundaryRectangle = L.rectangle(bounds, {
                color: '#ea580c',
                weight: 1.5,
                fillColor: '#f97316',
                fillOpacity: 0.08
            }).addTo(map);
        }

        // Fungsi memperbarui titik lokasi kantor
        function updateOfficeLocation(lat, lng) {
            const officeName = document.getElementById("namaKantor").value.trim() || "Titik Lokasi Kantor";
            const radiusMeters = parseFloat(document.getElementById("radius").value) || 25;

            // Hapus marker lama
            if (mainMarker) {
                map.removeLayer(mainMarker);
            }

            // Buat marker baru yang bisa di-drag
            mainMarker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            mainMarker.bindPopup(`<b class="text-xs">${escapeHtml(officeName)}</b><br><span class="text-[10px] text-gray-500">Titik Pusat Presensi</span>`).openPopup();

            // Hitung jarak boundary
            const distance = calculateDistance(lat, radiusMeters);
            const leftTopLat = lat + distance.latitude;
            const leftTopLng = lng - distance.longitude;
            const rightBottomLat = lat - distance.latitude;
            const rightBottomLng = lng + distance.longitude;

            // Isi nilai form input
            document.getElementById('latitudeoffice').value = lat.toFixed(7);
            document.getElementById('longitudeoffice').value = lng.toFixed(7);
            document.getElementById('latitulefttop').value = leftTopLat.toFixed(7);
            document.getElementById('longitudelefttop').value = leftTopLng.toFixed(7);
            document.getElementById('latiturightbottom').value = rightBottomLat.toFixed(7);
            document.getElementById('longituderightbottom').value = rightBottomLng.toFixed(7);

            // Update badge status
            const badge = document.getElementById('coordStatusBadge');
            if (badge) {
                badge.className = 'text-[10px] font-semibold bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full flex items-center gap-1';
                badge.innerHTML = '<i class="fa-solid fa-circle-check text-[9px]"></i> Titik Terpilih';
            }

            // Update display teks koordinat
            const coordsDisplay = document.getElementById('mapCoordsDisplay');
            if (coordsDisplay) {
                coordsDisplay.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            }

            // Gambar visual area pada peta
            drawBoundary(lat, lng, radiusMeters, leftTopLat, leftTopLng, rightBottomLat, rightBottomLng);

            // Event listener saat marker selesai digeser (dragend)
            mainMarker.on('dragend', function(e) {
                const pos = mainMarker.getLatLng();
                updateOfficeLocation(pos.lat, pos.lng);
            });
        }

        // Klik pada peta untuk memilih titik lokasi
        map.on('click', function(e) {
            updateOfficeLocation(e.latlng.lat, e.latlng.lng);
        });

        // Hitung jarak derajat berdasarkan radius meter
        function calculateDistance(lat, distanceInMeters) {
            const earthRadius = 6371000;
            const latitudeInDegrees = (distanceInMeters / earthRadius) * (180 / Math.PI);
            const longitudeInDegrees = (distanceInMeters / (earthRadius * Math.cos(Math.PI * lat / 180))) * (180 / Math.PI);
            return {
                latitude: latitudeInDegrees,
                longitude: longitudeInDegrees
            };
        }

        // Handler saat radius diubah
        function handleRadiusChange(value) {
            const radius = parseFloat(value) || 25;
            const label = document.getElementById('radiusValueLabel');
            if (label) label.textContent = `${radius} Meter`;

            const currentLat = parseFloat(document.getElementById('latitudeoffice').value);
            const currentLng = parseFloat(document.getElementById('longitudeoffice').value);

            if (!isNaN(currentLat) && !isNaN(currentLng)) {
                updateOfficeLocation(currentLat, currentLng);
            }
        }

        function setRadiusPreset(meters) {
            const input = document.getElementById('radius');
            if (input) {
                input.value = meters;
                handleRadiusChange(meters);
            }
        }

        // Gunakan Lokasi GPS Saya
        function locateUserGPS() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation GPS.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 18);
                    updateOfficeLocation(lat, lng);
                },
                function(err) {
                    alert('Gagal mengambil lokasi GPS: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // Pencarian Alamat / Lokasi via Nominatim OSM
        function searchLocation() {
            const query = document.getElementById('mapSearchInput').value.trim();
            if (!query) return;

            const resultsContainer = document.getElementById('searchResultsList');
            resultsContainer.innerHTML = '<div class="p-3 text-center text-slate-400">Mencari lokasi...</div>';
            resultsContainer.classList.remove('hidden');

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        resultsContainer.innerHTML = '<div class="p-3 text-center text-slate-400">Lokasi tidak ditemukan.</div>';
                        return;
                    }

                    resultsContainer.innerHTML = '';
                    data.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left p-2.5 hover:bg-blue-50 transition text-slate-700 flex items-start gap-2';
                        btn.innerHTML = `
                            <i class="fa-solid fa-map-pin text-blue-600 text-xs mt-0.5 shrink-0"></i>
                            <span class="truncate block">${escapeHtml(item.display_name)}</span>
                        `;
                        btn.onclick = () => {
                            const lat = parseFloat(item.lat);
                            const lng = parseFloat(item.lon);
                            map.setView([lat, lng], 18);
                            updateOfficeLocation(lat, lng);
                            resultsContainer.classList.add('hidden');
                        };
                        resultsContainer.appendChild(btn);
                    });
                })
                .catch(err => {
                    resultsContainer.innerHTML = '<div class="p-3 text-center text-rose-500">Gagal mencari lokasi.</div>';
                });
        }

        // Enter key di search input
        document.getElementById('mapSearchInput')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchLocation();
            }
        });

        // Tutup search dropdown jika klik di luar
        document.addEventListener('click', function(e) {
            const searchContainer = document.getElementById('mapSearchInput')?.parentElement;
            const resultsContainer = document.getElementById('searchResultsList');
            if (resultsContainer && !searchContainer?.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>
