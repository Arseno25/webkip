<x-filament-panels::page>
    <div class="w-full h-full">
        <div class="mb-5 flex flex-col">
            <label>Upload File GeoJSON</label>
            <input type="file" id="geojson-file" accept=".geojson" class="p-2 border rounded">
        </div>
        <div id="map" class="w-full h-[600px] rounded-lg border border-gray-300"></div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            #map { min-height: 600px;
            z-index: 0;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize map after container is fully loaded
                const map = L.map('map', {
                    preferCanvas: true,
                    zoomControl: true
                }).setView([-9.7318891, 120.0912804], 9);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    // attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);

                // Handle file upload
                document.getElementById('geojson-file').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const geojson = JSON.parse(e.target.result);
                            const layer = L.geoJSON(geojson, {
                                onEachFeature: function(feature, layer) {
                                    if (feature.properties) {
                                        let popupContent = '<div style="max-width: 300px; max-height: 200px; overflow: auto;">';
                                        const excludedProps = ['OBJECTID', 'AREA', 'PERIMETER', 'KODE_UNSUR'];
                                        for (const key in feature.properties) {
                                            if (!excludedProps.includes(key)) {
                                                popupContent += `<strong>${key}:</strong> ${feature.properties[key]}<br>`;
                                            }
                                        }
                                        popupContent += '</div>';
                                        layer.bindPopup(popupContent);
                                    }
                                }
                            }).addTo(map);
                            map.fitBounds(layer.getBounds());
                        } catch (error) {
                            console.error('Error parsing GeoJSON:', error);
                            alert('Invalid GeoJSON file');
                        }
                    };
                    reader.readAsText(file);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
