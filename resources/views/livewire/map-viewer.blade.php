<div>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="selectedSubdistrict" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                <select id="selectedSubdistrict" wire:model.live="selectedSubdistrict" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Semua Kecamatan</option>
                    @foreach($subdistricts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="selectedSchool" class="block text-sm font-medium text-gray-700">Sekolah</label>
                <select id="selectedSchool" wire:model.live="selectedSchool" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="selectedLevel" class="block text-sm font-medium text-gray-700">Jenjang</label>
                <select id="selectedLevel" wire:model.live="selectedLevel" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Semua Jenjang</option>
                    @foreach($levels as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="p-6 bg-white rounded-xl shadow" x-data="{
            mapData: @js($this->getMapData()),
            map: null,
            subdistrictsLayer: null,
            schoolsLayer: null,
            recipientsLayer: null,
            formBoundariesLayer: null,

            init() {
                this.initMap();

                Livewire.on('mapDataUpdated', (data) => {
                    this.mapData = data;
                    this.refreshMap();
                });
                
                // Listen for boundaries updates from form
                Livewire.on('loadBoundaries', (boundaries) => {
                    if (this.formBoundariesLayer) {
                        this.map.removeLayer(this.formBoundariesLayer);
                    }
                    
                    if (boundaries) {
                        try {
                            const geoJson = JSON.parse(boundaries);
                            this.formBoundariesLayer = L.geoJSON(geoJson, {
                                style: {
                                    color: '#FF0000',
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 0.2
                                }
                            }).addTo(this.map);
                            this.formBoundariesLayer.bringToFront();
                        } catch (e) {
                            console.error('Error parsing boundaries:', e);
                        }
                    }
                });
            },

            initMap() {
                this.map = L.map('map').setView([-6.2088, 106.8456], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
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

            renderMapData() {
                // Add subdistrict boundaries
                this.mapData.subdistricts.forEach(subdistrict => {
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
                            }).addTo(this.subdistrictsLayer);

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
                                html: `<div style=\"background-color: #3388ff; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;\"></div>`,
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            })
                        }).addTo(this.subdistrictsLayer)
                        .bindPopup(`
                            <strong>${subdistrict.name}</strong><br>
                            Jumlah Penerima KIP: ${subdistrict.count}
                        `);
                    }
                });

                // Add school markers
                this.mapData.schools.forEach(school => {
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
                                html: `<div style=\"background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;\"></div>`,
                                iconSize: [14, 14],
                                iconAnchor: [7, 7]
                            })
                        }).addTo(this.schoolsLayer)
                        .bindPopup(`
                            <strong>${school.name}</strong><br>
                            Jenjang: ${school.level}<br>
                            Jumlah Penerima KIP: ${school.count}
                        `);
                    }
                });

                // Add KIP recipient markers
                this.mapData.kipRecipients.forEach(recipient => {
                    if (recipient.latitude && recipient.longitude) {
                        L.marker([recipient.latitude, recipient.longitude], {
                            icon: L.divIcon({
                                className: 'recipient-marker',
                                html: `<div style=\"background-color: #10b981; width: 8px; height: 8px; border-radius: 50%; border: 1px solid white;\"></div>`,
                                iconSize: [8, 8],
                                iconAnchor: [4, 4]
                            })
                        }).addTo(this.recipientsLayer)
                        .bindPopup(`
                            <strong>${recipient.name}</strong><br>
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
            },

            toggleLayer(layerName) {
                const layer = this[layerName];
                const button = document.getElementById('show' + layerName.charAt(0).toUpperCase() + layerName.slice(1, -5));

                if (this.map.hasLayer(layer)) {
                    this.map.removeLayer(layer);
                    button.classList.remove(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                           layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                    button.classList.add('bg-gray-300');
                } else {
                    this.map.addLayer(layer);
                    button.classList.remove('bg-gray-300');
                    button.classList.add(layerName === 'subdistrictsLayer' ? 'bg-blue-500' :
                                        layerName === 'schoolsLayer' ? 'bg-red-500' : 'bg-green-500');
                }
            }
        }">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-700/50">Peta Distribusi Penerima KIP</h2>
                <div class="flex space-x-2">
                    <button id="showSubdistricts" @click="toggleLayer('subdistrictsLayer')" class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm">Kecamatan</button>
                    <button id="showSchools" @click="toggleLayer('schoolsLayer')" class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm">Sekolah</button>
                    <button id="showRecipients" @click="toggleLayer('recipientsLayer')" class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm">Penerima KIP</button>
                </div>
            </div>

            <div id="map" style="height: 600px; width: 100%;" class="rounded-lg"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
</div>
