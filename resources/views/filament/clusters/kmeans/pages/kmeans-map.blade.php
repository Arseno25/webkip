<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Peta Hasil Clustering
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Visualisasi persebaran sekolah berdasarkan hasil clustering
                    </p>
                </div>
                <div class="hidden sm:block">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Map Container -->
        <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
            <div class="p-6">
                <div id="map" class="w-full h-[600px] rounded-lg"></div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Keterangan Cluster</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($clusterColors as $cluster => $color)
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $color }}"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Cluster {{ $cluster }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            min-height: 600px;
            z-index: 0;
        }
        .leaflet-popup-content {
            margin: 10px;
        }
        .leaflet-popup-content h3 {
            font-weight: bold;
            margin-bottom: 8px;
        }
        .leaflet-popup-content p {
            margin: 4px 0;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapData = @json($this->getMapData());

            // Initialize map
            const map = L.map('map', {
                preferCanvas: true,
                zoomControl: true
            }).setView([-6.200000, 106.816666], 9); // Default to Jakarta

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            // Add markers for each location
            mapData.forEach(location => {
                const marker = L.circleMarker([location.lat, location.lng], {
                    radius: 8,
                    fillColor: location.color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);

                // Create popup content
                let popupContent = '<div>';
                popupContent += `<h3>${location.info.Sekolah}</h3>`;
                for (const [key, value] of Object.entries(location.info)) {
                    if (key !== 'Sekolah') {
                        popupContent += `<p><strong>${key}:</strong> ${value}</p>`;
                    }
                }
                popupContent += '</div>';

                marker.bindPopup(popupContent);
            });

            // Fit bounds if there are markers
            if (mapData.length > 0) {
                const bounds = mapData.map(loc => [loc.lat, loc.lng]);
                map.fitBounds(bounds);
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
