<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Lokasi User | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body class="h-screen flex">
    <!-- Map Container -->
    <div id="map" class="w-full h-full"></div>

    <script>
        const officeCoords = [
            parseFloat("{{ $coordinates[0]->latitude ?? 0 }}") || null,
            parseFloat("{{ $coordinates[0]->longitude ?? 0 }}") || null
        ];

        const latStart = parseFloat("{{ $lat_start ?? '' }}") || null;
        const longStart = parseFloat("{{ $long_start ?? '' }}") || null;
        const latEnd = parseFloat("{{ $lat_end ?? '' }}") || null;
        const longEnd = parseFloat("{{ $long_end ?? '' }}") || null;

        const officeName = "{{ $officeName }}";

        // Initialize the map centered on the office
        const map = L.map('map').setView(officeCoords, 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 50
        }).addTo(map);

        const officeIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -30]
        });

        const yellowIcon = new L.Icon({
            iconUrl: "{{ asset('img/location-pin-yellow.png') }}",
            iconSize: [40, 40],
        });
        const blueIcon = new L.Icon({
            iconUrl: "{{ asset('img/location-pin-blue.png') }}",
            iconSize: [40, 40],
        });

        // Marker for the office
        L.marker(officeCoords, {
                icon: officeIcon
            }).addTo(map)
            .bindPopup(`${officeName}`)
            .openPopup();

        if (latStart && longStart) {
            L.marker([latStart, longStart], {
                    icon: blueIcon
                }).addTo(map)
                .bindPopup("Posisi presensi masuk").openPopup();
        }

        if (latEnd && longEnd) {
            L.marker([latEnd, longEnd], {
                    icon: yellowIcon
                }).addTo(map)
                .bindPopup("Posisi presensi pulang").openPopup();
        }

        function drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng) {
            if (boundaryRect) {
                map.removeLayer(boundaryRect);
            }

            var bounds = [
                [leftTopLat, leftTopLng], // Top-left corner
                [rightBottomLat, rightBottomLng] // Bottom-right corner
            ];

            boundaryRect = L.rectangle(bounds, {
                color: "#ff7800",
                weight: 2
            }).addTo(map);
        }
        // drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng);
    </script>

    <!-- Hidden inputs for boundary coordinates -->
    <input type="hidden" id="radius" placeholder="Radius (meters)" value="25" />

</body>

</html>
