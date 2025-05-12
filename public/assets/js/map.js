document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('postMap');
    let map = null;
    let currentMarker = null;

    document.querySelectorAll('.btn-gps').forEach(button => {
        button.addEventListener('click', function() {
            console.log('GPS button clicked!');
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            console.log(`Coordinates: ${lat}, ${lng}`);

            if (mapContainer.style.display === 'none') {
                mapContainer.style.display = 'block';
                console.log('Map container is now visible');
                initializeMap(lat, lng);
            } else {
                map.setView([lat, lng], 13);
                updateMarker(lat, lng);
            }
        });
    });

    function initializeMap(lat, lng) {
        if (!map) {
            console.log('Initializing map...');
            map = L.map('postMap').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            console.log('Map initialized successfully');
        }
        updateMarker(lat, lng);
    }

    function updateMarker(lat, lng) {
        if (currentMarker) map.removeLayer(currentMarker);
        currentMarker = L.marker([lat, lng]).addTo(map)
            .bindPopup('Post Location');
    }
});