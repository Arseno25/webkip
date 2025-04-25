<div>
    {{ $this->form }}

    <div id="map-container" class="p-3 mt-3 bg-white rounded-xl shadow mt-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Peta Distribusi Penerima KIP</h2>
            <div class="flex space-x-2">
                <button id="showSubdistricts" class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm">Kecamatan</button>
                <button id="showSchools" class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm">Sekolah</button>
                <button id="showRecipients" class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm">Penerima KIP</button>
            </div>
        </div>

        <div id="map" style="height: 600px; width: 100%;" class="rounded-lg"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let mapData = @json($mapData);
            let map = null;
            let subdistrictsLayer = null;
            let schoolsLayer = null;
            let recipientsLayer = null;

            function initMap() {
                if (map) {
                    map.remove();
                }

                console.log('Initializing map...');
                map = L.map('map').setView([-6.2088, 106.8456], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                subdistrictsLayer = L.layerGroup().addTo(map);
                schoolsLayer = L.layerGroup().addTo(map);
                recipientsLayer = L.layerGroup().addTo(map);

                renderMapData();

                // Trigger resize event untuk memastikan peta dirender dengan benar
                setTimeout(() => {
                    map.invalidateSize();
                }, 200);

                // Setup button event listeners
                document.getElementById('showSubdistricts').addEventListener('click', function() {
                    toggleLayer('subdistrictsLayer');
                });

                document.getElementById('showSchools').addEventListener('click', function() {
                    toggleLayer('schoolsLayer');
                });

                document.getElementById('showRecipients').addEventListener('click', function() {
                    toggleLayer('recipientsLayer');
                });
            }

            function toggleLayer(layerName) {
                const layer = layerName === 'subdistrictsLayer' ? subdistrictsLayer :
                              layerName === 'schoolsLayer' ? schoolsLayer : recipientsLayer;

                const button = document.getElementById('show' + layerName.charAt(0).toUpperCase() + layerName.slice(1, -5));

                if (map.hasLayer(layer)) {
                    map.removeLayer(layer);
                    button.classList.remove(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                           layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                    button.classList.add('bg-gray-300');
                } else {
                    map.addLayer(layer);
                    button.classList.remove('bg-gray-300');
                    button.classList.add(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                        layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                }
            }

            function renderMapData() {
                console.log('Rendering map data:', mapData);

                if (!mapData || !mapData.subdistricts || !mapData.schools || !mapData.kipRecipients) {
                    console.error('Invalid map data structure:', mapData);
                    return;
                }

                // Add subdistrict boundaries
                mapData.subdistricts.forEach(subdistrict => {
                    if (subdistrict.boundaries) {
                        try {
                            const geojson = JSON.parse(subdistrict.boundaries);
                            const layer = L.geoJSON(geojson, {
                                style: {
                                    fillColor: '#3388ff',
                                    weight: 2,
                                    opacity: 1,
                                    color: '#3388ff',
                                    fillOpacity: 0.2
                                }
                            }).addTo(subdistrictsLayer);

                            layer.bindPopup(`
                                <strong>${subdistrict.name}</strong><br>
                                Jumlah Penerima KIP: ${subdistrict.count}
                            `);
                        } catch (e) {
                            console.error('Error parsing GeoJSON:', e);
                        }
                    }

                    // Add marker for subdistrict center
                    if (subdistrict.latitude && subdistrict.longitude) {
                        L.marker([subdistrict.latitude, subdistrict.longitude], {
                            icon: L.divIcon({
                                className: 'subdistrict-marker',
                                html: `<div style="background-color: #3388ff; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            })
                        }).addTo(subdistrictsLayer)
                        .bindPopup(`
                            <strong>${subdistrict.name}</strong><br>
                            Jumlah Penerima KIP: ${subdistrict.count}
                        `);
                    }
                });

                // Add school markers
                mapData.schools.forEach(school => {
                    if (school.latitude && school.longitude) {
                        let color;
                        switch (school.level) {
                            case 'SD': color = '#10b981'; break;
                            case 'SMP': color = '#3b82f6'; break;
                            case 'SMA': color = '#f59e0b'; break;
                            case 'SMK': color = '#ef4444'; break;
                            default: color = '#6b7280';
                        }

                        L.marker([school.latitude, school.longitude], {
                            icon: L.divIcon({
                                className: 'school-marker',
                                html: `<div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;"></div>`,
                                iconSize: [14, 14],
                                iconAnchor: [7, 7]
                            })
                        }).addTo(schoolsLayer)
                        .bindPopup(`
                            <strong>${school.name}</strong><br>
                            Jenjang: ${school.level}<br>
                            Jumlah Penerima KIP: ${school.count}
                        `);
                    }
                });

                // Add KIP recipient markers
                mapData.kipRecipients.forEach(recipient => {
                    if (recipient.latitude && recipient.longitude) {
                        L.marker([recipient.latitude, recipient.longitude], {
                            icon: L.divIcon({
                                className: 'recipient-marker',
                                html: `<div style="background-color: #10b981; width: 8px; height: 8px; border-radius: 50%; border: 1px solid white;"></div>`,
                                iconSize: [8, 8],
                                iconAnchor: [4, 4]
                            })
                        }).addTo(recipientsLayer)
                        .bindPopup(`
                            <strong>${recipient.name}</strong><br>
                            Sekolah: ${recipient.school || 'Tidak Ada'}<br>
                            Kecamatan: ${recipient.subdistrict || 'Tidak Ada'}
                        `);
                    }
                });

                // Fit map to bounds if we have data
                const bounds = [];

                mapData.subdistricts.forEach(subdistrict => {
                    if (subdistrict.latitude && subdistrict.longitude) {
                        bounds.push([subdistrict.latitude, subdistrict.longitude]);
                    }
                });

                mapData.schools.forEach(school => {
                    if (school.latitude && school.longitude) {
                        bounds.push([school.latitude, school.longitude]);
                    }
                });

                mapData.kipRecipients.forEach(recipient => {
                    if (recipient.latitude && recipient.longitude) {
                        bounds.push([recipient.latitude, recipient.longitude]);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds);
                }
            }

            // Initialize map
            setTimeout(initMap, 100);
        });
    </script>
</div>
