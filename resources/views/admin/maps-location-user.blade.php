<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengecekan Lokasi & Radius Presensi | {{ $user_name ?? 'Admin' }}</title>
    <!-- Vite Tailwind CSS & FontAwesome -->
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
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

        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 2px;
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 font-sans h-screen w-screen overflow-hidden relative select-none">

    @php
    $officesData = $offices->map(function($o) {
    $main = $o->coordinates->firstWhere('is_main', 1);
    $boundaries = $o->coordinates->where('is_main', 0)->values();

    $radiusMeters = 25; // default fallback
    if ($main && $boundaries->count() >= 2) {
    $latDiff = abs((float)$main->latitude - (float)$boundaries[0]->latitude);
    $calcRadius = round($latDiff * (M_PI / 180) * 6378137);
    if ($calcRadius >= 5) {
    $radiusMeters = $calcRadius;
    }
    }

    return [
    'id' => $o->id,
    'name' => $o->name,
    'address' => $o->address,
    'capacity' => $o->capacity,
    'main_latitude' => $main ? (float)$main->latitude : null,
    'main_longitude' => $main ? (float)$main->longitude : null,
    'radius' => $radiusMeters,
    'boundary_top_left' => $boundaries->count() >= 1 ? ['lat' => (float)$boundaries[0]->latitude, 'lng' => (float)$boundaries[0]->longitude] : null,
    'boundary_bottom_right' => $boundaries->count() >= 2 ? ['lat' => (float)$boundaries[1]->latitude, 'lng' => (float)$boundaries[1]->longitude] : null,
    ];
    });

    $backUrl = $intern_id
    ? route('admin.presence.detail', ['intern_id' => $intern_id]) . ($page ? '?page=' . $page : '')
    : url()->previous();
    @endphp

    <!-- Fullscreen Leaflet Map Container -->
    <div id="map" class="w-full h-full absolute inset-0"></div>

    <!-- FLOATING TOP BAR -->
    <div class="absolute top-2.5 left-2.5 right-2.5 sm:top-4 sm:left-4 sm:right-4 z-[1000] pointer-events-none">
        <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl shadow-lg border border-slate-200/80 px-3 sm:px-3.5 py-2 sm:py-2.5 flex items-center justify-between gap-2 sm:gap-3">

            <!-- Left: Back Button, Title & Breadcrumb -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 font-bold text-xs rounded-xl transition cursor-pointer shadow-2xs shrink-0">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span class="hidden xs:inline">Kembali</span>
                </a>

                <div class="h-5 w-px bg-slate-200 hidden sm:block shrink-0"></div>

                <div class="min-w-0">
                    <div class="flex items-center gap-1 text-[10px] sm:text-[11px] text-slate-500 truncate">
                        <span>Lokasi Presensi</span>
                        @if($date)
                        <span>•</span>
                        <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D MMM Y') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <h1 class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                            {{ $user_name ?? 'Siswa' }}
                        </h1>
                        @if(isset($intern) && $intern && $intern->school)
                        <span class="hidden md:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 truncate">
                            <i class="fa-solid fa-school text-[9px]"></i>
                            <span>{{ $intern->school->name }}</span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Desktop: Quick Map Controls & Toggle Panel -->
            <div class="hidden sm:flex items-center gap-1.5 shrink-0">
                @if($lat_start && $long_start)
                <button type="button" onclick="focusToStart()"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                    title="Fokus Presensi Masuk">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Masuk</span>
                </button>
                @endif

                @if($lat_end && $long_end)
                <button type="button" onclick="focusToEnd()"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                    title="Fokus Presensi Pulang">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Pulang</span>
                </button>
                @endif

                <button type="button" onclick="fitAllPoints()"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                    title="Tampilkan Semua Titik">
                    <i class="fa-solid fa-expand text-[10px]"></i>
                    <span class="hidden md:inline">Semua Titik</span>
                </button>

                <!-- Toggle Floating Info Panel -->
                <button type="button" onclick="toggleInfoPanel()" id="togglePanelBtn"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                    title="Buka/Tutup Panel Informasi">
                    <i class="fa-solid fa-layer-group text-blue-600 text-xs"></i>
                    <span>Info & Legend</span>
                </button>
            </div>

            <!-- Right Mobile: Quick Info Button -->
            <div class="sm:hidden flex items-center gap-1 shrink-0">
                <button type="button" onclick="toggleInfoPanel()" id="mobileTopToggleBtn"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold shadow-2xs transition">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span>Info</span>
                </button>
            </div>

        </div>
    </div>

    <!-- FLOATING INFO & GEOFENCE PANEL -->
    <div id="floatingInfoPanel" class="absolute top-16 sm:top-20 right-2.5 sm:right-4 left-2.5 sm:left-auto z-[1000] sm:w-80 max-w-[calc(100vw-1.25rem)] max-h-[calc(100vh-8.5rem)] sm:max-h-[calc(100vh-5.5rem)] flex flex-col pointer-events-none transition-all duration-300 transform opacity-0 translate-y-4 sm:opacity-100 sm:translate-y-0">
        <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden flex flex-col max-h-full">

            <!-- Panel Header -->
            <div class="px-3.5 py-2.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </div>
                    <h3 class="font-bold text-xs text-slate-900">Detail Jangkauan & Radius</h3>
                </div>
                <button type="button" onclick="toggleInfoPanel()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Panel Scrollable Body -->
            <div class="overflow-y-auto custom-scrollbar p-3.5 space-y-3">

                <!-- 1. Status Presensi Siswa -->
                <div class="space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Presensi Siswa</span>

                    <!-- Presensi Masuk Card -->
                    <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                                <span class="font-bold text-slate-800 text-xs">Presensi Masuk</span>
                            </div>
                            @if($lat_start && $long_start)
                            <button type="button" onclick="focusToStart()" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition cursor-pointer">
                                Fokus <i class="fa-solid fa-arrow-right text-[8px]"></i>
                            </button>
                            @endif
                        </div>

                        @if($lat_start && $long_start)
                        <div class="text-[11px] space-y-1">
                            <div id="start-status-badge">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700">
                                    <i class="fa-solid fa-spinner fa-spin text-[8px]"></i> Menghitung...
                                </span>
                            </div>
                            <div id="start-distance-text" class="text-[10px] text-slate-600 font-medium"></div>
                            <div class="text-[9px] font-mono text-slate-400">
                                {{ $lat_start }}, {{ $long_start }}
                            </div>
                        </div>
                        @else
                        <div class="text-[10px] text-slate-400 italic">Tidak ada koordinat presensi masuk.</div>
                        @endif
                    </div>

                    <!-- Presensi Pulang Card -->
                    <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                                <span class="font-bold text-slate-800 text-xs">Presensi Pulang</span>
                            </div>
                            @if($lat_end && $long_end)
                            <button type="button" onclick="focusToEnd()" class="text-[10px] font-bold text-amber-600 hover:text-amber-800 transition cursor-pointer">
                                Fokus <i class="fa-solid fa-arrow-right text-[8px]"></i>
                            </button>
                            @endif
                        </div>

                        @if($lat_end && $long_end)
                        <div class="text-[11px] space-y-1">
                            <div id="end-status-badge">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700">
                                    <i class="fa-solid fa-spinner fa-spin text-[8px]"></i> Menghitung...
                                </span>
                            </div>
                            <div id="end-distance-text" class="text-[10px] text-slate-600 font-medium"></div>
                            <div class="text-[9px] font-mono text-slate-400">
                                {{ $lat_end }}, {{ $long_end }}
                            </div>
                        </div>
                        @else
                        <div class="text-[10px] text-slate-400 italic">Belum ada koordinat presensi pulang.</div>
                        @endif
                    </div>
                </div>

                <!-- 2. Titik Kantor & Radius -->
                <div class="space-y-1.5 pt-1 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Daftar Kantor ({{ count($officesData) }})</span>
                        <span class="text-[9px] text-slate-400">Klik untuk fokus</span>
                    </div>

                    <div class="space-y-1.5 max-h-36 overflow-y-auto custom-scrollbar pr-0.5">
                        @foreach($officesData as $off)
                        <div data-office-id="{{ $off['id'] }}" onclick="focusToOffice(this)"
                            class="p-2 rounded-xl border border-slate-100 hover:border-blue-300 hover:bg-blue-50/50 transition cursor-pointer bg-white group flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-building text-slate-500 group-hover:text-blue-600 text-[10px] shrink-0"></i>
                                    <span class="font-bold text-[11px] text-slate-800 group-hover:text-blue-700 capitalize truncate">{{ $off['name'] }}</span>
                                </div>
                                @if($off['address'])
                                <p class="text-[10px] text-slate-400 truncate pl-3.5">{{ $off['address'] }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                                ±{{ $off['radius'] }}m
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Legend / Petunjuk -->
                <div class="pt-1.5 border-t border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Petunjuk Visual</span>
                    <div class="grid grid-cols-2 gap-1.5 text-[10px] text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
                            <span>Presensi Masuk</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Presensi Pulang</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border border-blue-500 bg-blue-100 shrink-0"></span>
                            <span>Radius Absen</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 border border-orange-500 bg-orange-100 shrink-0"></span>
                            <span>Batas Geofence</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- FLOATING COMPACT LEGEND (DESKTOP BOTTOM LEFT) -->
    <div class="hidden sm:flex absolute bottom-4 left-4 z-[1000] pointer-events-none items-center gap-2">
        <div class="pointer-events-auto bg-white/90 backdrop-blur-md px-3 py-2 rounded-xl shadow-lg border border-slate-200/80 text-[11px] flex items-center gap-3">
            <button type="button" onclick="fitAllPoints()" class="font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-arrows-to-circle text-xs"></i>
                <span>Fit Semua Titik</span>
            </button>
            <span class="h-3.5 w-px bg-slate-200"></span>
            <span class="text-[10px] text-slate-500">
                <strong class="text-slate-800">{{ count($officesData) }}</strong> Titik Kantor Ditampilkan
            </span>
        </div>
    </div>

    <!-- FLOATING BOTTOM DOCK FOR MOBILE -->
    <div class="sm:hidden fixed bottom-3 inset-x-2.5 z-[1000] pointer-events-none">
        <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/90 p-1.5 flex items-center justify-between gap-1.5">
            @if($lat_start && $long_start)
            <button type="button" onclick="focusToStart()"
                class="flex-1 py-2 px-1.5 bg-blue-50 active:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1 transition">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Masuk</span>
            </button>
            @endif

            @if($lat_end && $long_end)
            <button type="button" onclick="focusToEnd()"
                class="flex-1 py-2 px-1.5 bg-amber-50 active:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1 transition">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Pulang</span>
            </button>
            @endif

            <button type="button" onclick="fitAllPoints()"
                class="flex-1 py-2 px-1.5 bg-slate-800 active:bg-slate-900 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                <i class="fa-solid fa-expand text-[10px]"></i>
                <span>Semua Titik</span>
            </button>
        </div>
    </div>

    <script id="offices-data" type="application/json">
        @json($officesData)
    </script>

    <script>
        // Data dari Controller
        const officesDataEl = document.getElementById('offices-data');
        const offices = officesDataEl ? JSON.parse(officesDataEl.textContent) : [];
        const latStart = parseFloat("{{ $lat_start ?? '' }}") || null;
        const longStart = parseFloat("{{ $long_start ?? '' }}") || null;
        const latEnd = parseFloat("{{ $lat_end ?? '' }}") || null;
        const longEnd = parseFloat("{{ $long_end ?? '' }}") || null;
        const selectedOfficeId = parseInt("{{ $office_id ?? 0 }}") || null;

        // Toggle Floating Info Panel
        let isPanelOpen = window.innerWidth >= 640;

        function updatePanelUI() {
            const panel = document.getElementById('floatingInfoPanel');
            const btn = document.getElementById('togglePanelBtn');
            const mobileTopBtn = document.getElementById('mobileTopToggleBtn');

            if (!isPanelOpen) {
                panel.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
                panel.classList.remove('opacity-100', 'translate-y-0');
                if (btn) btn.classList.remove('bg-blue-50', 'border-blue-200');
                if (mobileTopBtn) mobileTopBtn.classList.remove('bg-blue-600', 'text-white');
            } else {
                panel.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
                panel.classList.add('opacity-100', 'translate-y-0');
                if (btn) btn.classList.add('bg-blue-50', 'border-blue-200');
                if (mobileTopBtn) mobileTopBtn.classList.add('bg-blue-600', 'text-white');
            }
        }

        function toggleInfoPanel() {
            isPanelOpen = !isPanelOpen;
            updatePanelUI();
        }
        window.toggleInfoPanel = toggleInfoPanel;

        // Haversine Distance in Meters
        function getDistanceInMeters(lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return 999999;
            const R = 6378137;
            const dLat = (lat2 - lat1) * (Math.PI / 180);
            const dLon = (lon2 - lon1) * (Math.PI / 180);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(R * c);
        }

        // Bounding Box check
        function isInsideBoundingBox(lat, lng, tl, br) {
            if (!tl || !br) return false;
            const minLat = Math.min(tl.lat, br.lat);
            const maxLat = Math.max(tl.lat, br.lat);
            const minLng = Math.min(tl.lng, br.lng);
            const maxLng = Math.max(tl.lng, br.lng);
            return lat >= minLat && lat <= maxLat && lng >= minLng && lng <= maxLng;
        }

        // Default Center
        let defaultCenter = [-7.7903584, 110.4093648];
        if (latStart && longStart) {
            defaultCenter = [latStart, longStart];
        } else if (offices.length > 0 && offices[0].main_latitude && offices[0].main_longitude) {
            defaultCenter = [offices[0].main_latitude, offices[0].main_longitude];
        }

        // Init Map
        const map = L.map('map', {
            zoomControl: false
        }).setView(defaultCenter, 17);

        // Add zoom control at bottom-right (above mobile bar)
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const boundsGroup = [];
        const officeMarkers = {};

        // 1. Render all Offices with Pin, Circle Radius, and Geofence Rectangle
        offices.forEach(office => {
            if (office.main_latitude && office.main_longitude) {
                const officeLatLng = [office.main_latitude, office.main_longitude];
                boundsGroup.push(officeLatLng);

                // Custom DivIcon for Office
                const officeIcon = L.divIcon({
                    className: 'custom-office-pin',
                    html: `
                        <div class="flex flex-col items-center group cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-slate-900 border-2 border-white shadow-xl flex items-center justify-center text-white text-xs font-bold transition-transform hover:scale-110">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <span class="mt-0.5 px-2 py-0.5 bg-slate-900/90 backdrop-blur-xs text-white text-[10px] font-bold rounded-md shadow-xs whitespace-nowrap capitalize">
                                ${office.name}
                            </span>
                        </div>
                    `,
                    iconSize: [80, 42],
                    iconAnchor: [40, 20]
                });

                const marker = L.marker(officeLatLng, {
                    icon: officeIcon
                }).addTo(map);
                officeMarkers[office.id] = marker;

                const popupContent = `
                    <div class="p-1 text-slate-800 min-w-[180px]">
                        <div class="flex items-center gap-1.5 font-bold text-xs capitalize text-slate-900 mb-1">
                            <i class="fa-solid fa-building text-blue-600"></i>
                            <span>${office.name}</span>
                        </div>
                        <div class="text-[11px] text-slate-600 mb-1">${office.address || 'Alamat tidak tersedia'}</div>
                        <div class="text-[10px] text-blue-700 bg-blue-50 border border-blue-200 px-2 py-1 rounded-md font-semibold mb-1.5">
                            <i class="fa-solid fa-circle-dot mr-1"></i> Radius Jangkauan: ±${office.radius} meter
                        </div>
                        <div class="text-[9px] font-mono text-slate-400">${office.main_latitude}, ${office.main_longitude}</div>
                    </div>
                `;
                marker.bindPopup(popupContent);

                // Circle Radius
                const circle = L.circle(officeLatLng, {
                    radius: office.radius,
                    color: '#2563eb',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.12,
                    weight: 2,
                    dashArray: '5, 5'
                }).addTo(map);
                circle.bindTooltip(`Radius Jangkauan: ${office.name} (±${office.radius}m)`, {
                    sticky: true
                });

                // Rectangle Geofence
                if (office.boundary_top_left && office.boundary_bottom_right) {
                    const bounds = [
                        [office.boundary_top_left.lat, office.boundary_top_left.lng],
                        [office.boundary_bottom_right.lat, office.boundary_bottom_right.lng]
                    ];
                    const rect = L.rectangle(bounds, {
                        color: '#ea580c',
                        weight: 1.5,
                        fillColor: '#f97316',
                        fillOpacity: 0.08,
                        dashArray: '3, 4'
                    }).addTo(map);
                    rect.bindTooltip(`Area Geofence: ${office.name}`, {
                        sticky: true
                    });
                }
            }
        });

        let startMarker = null;
        let endMarker = null;

        // 2. Render Check-in Marker
        if (latStart && longStart) {
            const startLatLng = [latStart, longStart];
            boundsGroup.push(startLatLng);

            const startIcon = L.divIcon({
                className: 'custom-user-start-pin',
                html: `
                    <div class="flex flex-col items-center cursor-pointer group">
                        <div class="relative">
                            <span class="absolute -inset-1 rounded-full bg-blue-500 opacity-60 animate-ping"></span>
                            <div class="relative w-8 h-8 rounded-full bg-blue-600 border-2 border-white shadow-xl flex items-center justify-center text-white text-xs font-bold">
                                <i class="fa-solid fa-right-to-bracket"></i>
                            </div>
                        </div>
                        <span class="mt-0.5 px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded-md shadow-xs whitespace-nowrap">
                            Presensi Masuk
                        </span>
                    </div>
                `,
                iconSize: [90, 44],
                iconAnchor: [45, 22]
            });

            startMarker = L.marker(startLatLng, {
                icon: startIcon
            }).addTo(map);

            let closestOffice = null;
            let minDistance = 999999;
            let isInsideAny = false;
            let matchedOfficeName = '';

            offices.forEach(o => {
                if (o.main_latitude && o.main_longitude) {
                    const dist = getDistanceInMeters(latStart, longStart, o.main_latitude, o.main_longitude);
                    if (dist < minDistance) {
                        minDistance = dist;
                        closestOffice = o;
                    }

                    const inBox = isInsideBoundingBox(latStart, longStart, o.boundary_top_left, o.boundary_bottom_right);
                    if (dist <= o.radius || inBox) {
                        isInsideAny = true;
                        matchedOfficeName = o.name;
                    }
                }
            });

            const startPopupContent = `
                <div class="p-1 text-slate-800 min-w-[190px]">
                    <div class="font-bold text-xs text-blue-700 flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Presensi Masuk</span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-600 mb-1.5">${latStart}, ${longStart}</div>
                    ${isInsideAny 
                        ? `<div class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-md mb-1"><i class="fa-solid fa-circle-check mr-1"></i> Di Dalam Area ${matchedOfficeName}</div>`
                        : `<div class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-1 rounded-md mb-1"><i class="fa-solid fa-circle-xmark mr-1"></i> Di Luar Radius Kantor</div>`
                    }
                    ${closestOffice ? `<div class="text-[10px] text-slate-500">Jarak ke <strong>${closestOffice.name}</strong>: ±${minDistance}m (Radius: ±${closestOffice.radius}m)</div>` : ''}
                </div>
            `;
            startMarker.bindPopup(startPopupContent);

            // Update Floating Info Panel
            const badgeEl = document.getElementById('start-status-badge');
            const distEl = document.getElementById('start-distance-text');
            if (badgeEl) {
                if (isInsideAny) {
                    badgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fa-solid fa-circle-check text-[9px]"></i> Di Dalam Area (${matchedOfficeName})</span>`;
                } else {
                    badgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Di Luar Jangkauan</span>`;
                }
            }
            if (distEl && closestOffice) {
                distEl.innerHTML = `Jarak ke <strong>${closestOffice.name}</strong>: <span class="font-bold text-slate-800">±${minDistance}m</span> (Batas: ±${closestOffice.radius}m)`;
            }
        }

        // 3. Render Check-out Marker
        if (latEnd && longEnd) {
            const endLatLng = [latEnd, longEnd];
            boundsGroup.push(endLatLng);

            const endIcon = L.divIcon({
                className: 'custom-user-end-pin',
                html: `
                    <div class="flex flex-col items-center cursor-pointer group">
                        <div class="w-8 h-8 rounded-full bg-amber-500 border-2 border-white shadow-xl flex items-center justify-center text-white text-xs font-bold">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </div>
                        <span class="mt-0.5 px-2 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-md shadow-xs whitespace-nowrap">
                            Presensi Pulang
                        </span>
                    </div>
                `,
                iconSize: [90, 44],
                iconAnchor: [45, 22]
            });

            endMarker = L.marker(endLatLng, {
                icon: endIcon
            }).addTo(map);

            let closestOfficeEnd = null;
            let minDistanceEnd = 999999;
            let isInsideAnyEnd = false;
            let matchedOfficeNameEnd = '';

            offices.forEach(o => {
                if (o.main_latitude && o.main_longitude) {
                    const dist = getDistanceInMeters(latEnd, longEnd, o.main_latitude, o.main_longitude);
                    if (dist < minDistanceEnd) {
                        minDistanceEnd = dist;
                        closestOfficeEnd = o;
                    }

                    const inBox = isInsideBoundingBox(latEnd, longEnd, o.boundary_top_left, o.boundary_bottom_right);
                    if (dist <= o.radius || inBox) {
                        isInsideAnyEnd = true;
                        matchedOfficeNameEnd = o.name;
                    }
                }
            });

            const endPopupContent = `
                <div class="p-1 text-slate-800 min-w-[190px]">
                    <div class="font-bold text-xs text-amber-700 flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Presensi Pulang</span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-600 mb-1.5">${latEnd}, ${longEnd}</div>
                    ${isInsideAnyEnd 
                        ? `<div class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-md mb-1"><i class="fa-solid fa-circle-check mr-1"></i> Di Dalam Area ${matchedOfficeNameEnd}</div>`
                        : `<div class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-1 rounded-md mb-1"><i class="fa-solid fa-circle-xmark mr-1"></i> Di Luar Radius Kantor</div>`
                    }
                    ${closestOfficeEnd ? `<div class="text-[10px] text-slate-500">Jarak ke <strong>${closestOfficeEnd.name}</strong>: ±${minDistanceEnd}m (Radius: ±${closestOfficeEnd.radius}m)</div>` : ''}
                </div>
            `;
            endMarker.bindPopup(endPopupContent);

            const badgeEndEl = document.getElementById('end-status-badge');
            const distEndEl = document.getElementById('end-distance-text');
            if (badgeEndEl) {
                if (isInsideAnyEnd) {
                    badgeEndEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fa-solid fa-circle-check text-[9px]"></i> Di Dalam Area (${matchedOfficeNameEnd})</span>`;
                } else {
                    badgeEndEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Di Luar Jangkauan</span>`;
                }
            }
            if (distEndEl && closestOfficeEnd) {
                distEndEl.innerHTML = `Jarak ke <strong>${closestOfficeEnd.name}</strong>: <span class="font-bold text-slate-800">±${minDistanceEnd}m</span> (Batas: ±${closestOfficeEnd.radius}m)`;
            }
        }

        // 4. Fit All Points Function
        function fitAllPoints() {
            if (boundsGroup.length > 0) {
                const bounds = L.latLngBounds(boundsGroup);
                map.fitBounds(bounds, {
                    padding: [window.innerWidth < 640 ? 30 : 60, window.innerWidth < 640 ? 30 : 60],
                    maxZoom: 18
                });
            }
        }

        if (boundsGroup.length > 0) {
            fitAllPoints();
        }

        // Focus Handlers
        window.focusToStart = function() {
            if (latStart && longStart && startMarker) {
                map.flyTo([latStart, longStart], 18, {
                    duration: 1
                });
                startMarker.openPopup();
            }
        };

        window.focusToEnd = function() {
            if (latEnd && longEnd && endMarker) {
                map.flyTo([latEnd, longEnd], 18, {
                    duration: 1
                });
                endMarker.openPopup();
            }
        };

        window.focusToOffice = function(officeIdOrEl) {
            const officeId = typeof officeIdOrEl === 'object' && officeIdOrEl !== null
                ? parseInt(officeIdOrEl.dataset.officeId, 10)
                : parseInt(officeIdOrEl, 10);
            const off = offices.find(o => o.id === officeId);
            if (off && off.main_latitude && off.main_longitude) {
                map.flyTo([off.main_latitude, off.main_longitude], 18, {
                    duration: 1
                });
                if (officeMarkers[officeId]) {
                    officeMarkers[officeId].openPopup();
                }
            }
        };

        window.fitAllPoints = fitAllPoints;

        // Initialize UI state
        updatePanelUI();

        setTimeout(() => {
            map.invalidateSize();
        }, 200);
    </script>
</body>

</html>