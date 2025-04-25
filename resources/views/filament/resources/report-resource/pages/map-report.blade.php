<x-filament-panels::page>
    <x-slot name="headerEnd">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>
    </x-slot>

    <div x-data="mapViewer(@js($mapData))" x-init="init()">
        {{ $this->form }}

        <div id="map-container" class="p-3 bg-white rounded-xl shadow" style="margin-top: 2rem;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-700/50">Peta Distribusi Penerima KIP</h2>
                <div class="flex space-x-2">
                    <button id="showSubdistricts" @click="toggleLayer('subdistrictsLayer')" style="margin-right: 5px" class="px-3 py-1 bg-gray-300 text-gray-950 rounded-lg text-sm">Kecamatan</button>
                    <button id="showSchools" @click="toggleLayer('schoolsLayer')" style="margin-right: 5px" class=" px-3 py-1 bg-gray-300 text-gray-950 rounded-lg text-sm">Sekolah</button>
                    <button id="showRecipients" @click="toggleLayer('recipientsLayer')"  class=" px-3 py-1 bg-gray-300 text-gray-950  rounded-lg text-sm">Penerima KIP</button>
                </div>
            </div>

            <div>
                <div id="map" style="height: 600px; width: 100%; z-index: 10" class="rounded-lg"></div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('mapViewer', (initialMapData) => ({
                    map: null,
                    mapData: initialMapData,
                    subdistrictsLayer: null,
                    schoolsLayer: null,
                    recipientsLayer: null,

                    init() {
                        // Pastikan map hanya diinisialisasi sekali
                        if (!this.map) {
                            this.initMap();
                        }
                        
                        // Watch for changes in Livewire component's mapData property
                        this.$wire.$watch('mapData', (newMapData) => {
                            console.log('Map data updated:', newMapData);
                            this.mapData = newMapData;
                            this.refreshMap();
                        });
                    },

                    initMap() {
                        this.map = L.map('map').setView([-6.2088, 106.8456], 10);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(this.map);

                        this.subdistrictsLayer = L.layerGroup().addTo(this.map);
                        this.schoolsLayer = L.layerGroup().addTo(this.map);
                        this.recipientsLayer = L.layerGroup().addTo(this.map);

                        this.renderMapData();
                    },

                    refreshMap() {
                        this.subdistrictsLayer.clearLayers();
                        this.schoolsLayer.clearLayers();
                        this.recipientsLayer.clearLayers();

                        this.renderMapData();
                    },

                    toggleLayer(layerName) {
                        const layer = this[layerName];
                        const button = document.getElementById('show' + layerName.charAt(0).toUpperCase() + layerName.slice(1, -5));

                        if (this.map.hasLayer(layer)) {
                            this.map.removeLayer(layer);
                            button.classList.remove(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                                   layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                            button.classList.add('bg-gray-400');
                        } else {
                            this.map.addLayer(layer);
                            button.classList.remove('bg-gray-400');
                            button.classList.add(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                                layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                        }
                    },

                    renderMapData() {
                        console.log('Rendering map data:', this.mapData);

                        // Add subdistrict boundaries
                        this.mapData.subdistricts.forEach(subdistrict => {
                            if (subdistrict.boundaries) {
                                try {
                                    const geojson = JSON.parse(subdistrict.boundaries);
                                    const layer = L.geoJSON(geojson, {
                                        style: {
                                            color: '#3b82f6',
                                            weight: 2,
                                            opacity: 0.8,
                                            fillColor: '#3b82f6',
                                            fillOpacity: 0.2
                                        }
                                    }).addTo(this.subdistrictsLayer);

                                    layer.bindPopup(`
                                        <strong>Kecamatan: ${subdistrict.name}</strong><br>
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
                                        html: `<div style="background-color: #3b82f6; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; justify-content: center; align-items: center; font-weight: bold;">${subdistrict.count}</div>`,
                                        iconSize: [30, 30],
                                        iconAnchor: [15, 15]
                                    })
                                }).addTo(this.subdistrictsLayer)
                                .bindPopup(`
                                    <strong>Kecamatan: ${subdistrict.name}</strong><br>
                                    Jumlah Penerima KIP: ${subdistrict.count}
                                `);
                            }
                        });

                        // Add school markers
                        this.mapData.schools.forEach(school => {
                            if (school.latitude && school.longitude) {
                                // Menggunakan URL langsung untuk gambar
                                const schoolIcon = L.icon({
                                    iconUrl: '/assets/marker/school-marker.png',
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 32],
                                    popupAnchor: [0, -32]
                                });
                                
                                L.marker([school.latitude, school.longitude], {
                                    icon: schoolIcon
                                }).addTo(this.schoolsLayer)
                                .bindPopup(`
                                    <strong>Sekolah: ${school.name}</strong><br>
                                    Jenjang: ${school.level}<br>
                                    Jumlah Penerima KIP: ${school.count}
                                `);
                            }
                        });

                        // Add KIP recipient markers
                        this.mapData.kipRecipients.forEach(recipient => {
                            if (recipient.latitude && recipient.longitude) {
                                // Menggunakan URL langsung untuk gambar
                                const recipientIcon = L.icon({
                                    iconUrl: '/assets/marker/home-marker.png',
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 32],
                                    popupAnchor: [0, -32]
                                });
                                
                                L.marker([recipient.latitude, recipient.longitude], {
                                    icon: recipientIcon
                                }).addTo(this.recipientsLayer)
                                .bindPopup(`
                                    <strong>Penerima: ${recipient.name}</strong><br>
                                    Sekolah: ${recipient.school || 'Tidak Ada'}<br>
                                    Kecamatan: ${recipient.subdistrict || 'Tidak Ada'}
                                `);
                            }
                        });

                        // Fit map to bounds if we have data
                        const bounds = [];

                        this.mapData.subdistricts.forEach(subdistrict => {
                            if (subdistrict.latitude && subdistrict.longitude) {
                                bounds.push([subdistrict.latitude, subdistrict.longitude]);
                            }
                        });

                        this.mapData.schools.forEach(school => {
                            if (school.latitude && school.longitude) {
                                bounds.push([school.latitude, school.longitude]);
                            }
                        });

                        this.mapData.kipRecipients.forEach(recipient => {
                            if (recipient.latitude && recipient.longitude) {
                                bounds.push([recipient.latitude, recipient.longitude]);
                            }
                        });

                        if (bounds.length > 0) {
                            this.map.fitBounds(bounds);
                        }
                    }
                }));
            });
        </script>
        @endpush
    </div>
</x-filament-panels::page>
