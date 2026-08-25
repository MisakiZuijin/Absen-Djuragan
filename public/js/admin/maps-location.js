// #MAPS LOKASI UNTUK KANTOR

const map = L.map('map').setView([-7.790366860887182, 110.40936457008371], 18);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 50
}).addTo(map);

var mainMarker = null;
var boundary = null;

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

// Event listener untuk klik di peta
map.on('click', function(e) {
    // Hapus marker sebelumnya jika ada
    if (mainMarker) {
        map.removeLayer(mainMarker);
    }


    var officeName = document.getElementById("namaKantor").value;

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


    var areaInSquareMeters = Math.pow(distanceInMeters * 2, 2);

    // Isi input field dengan koordinat yang dihitung
    document.getElementById('latitulefttop').value = leftTopLat;
    document.getElementById('longitudelefttop').value = leftTopLng;
    document.getElementById('latiturightbottom').value = rightBottomLat;
    document.getElementById('longituderightbottom').value = rightBottomLng;

    // Gambar boundary di peta berdasarkan koordinat
    drawBoundary(leftTopLat, leftTopLng, rightBottomLat, rightBottomLng);


});

function calculateDistance(lat, distanceInMeters) {
    const earthRadius = 6371000;

    const latitudeInDegrees = (distanceInMeters / earthRadius) * (180 / Math.PI);

    const longitudeInDegrees = (distanceInMeters / (earthRadius * Math.cos(Math.PI * lat / 180))) * (180 / Math.PI);

    return {
        latitude: latitudeInDegrees,
        longitude: longitudeInDegrees
    };
}