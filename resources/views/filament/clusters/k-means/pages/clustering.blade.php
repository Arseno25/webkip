<!-- resources/views/filament/clusters/k-means/pages/clustering.blade.php -->
<x-filament-panels::page>
    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Hasil K-Means Clustering</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-300">
                Hasil pengelompokan data menggunakan algoritma K-Means.
            </p>
        </div>

        @if(session('error'))
            <div class="p-4 mb-6 text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if(!empty($clusterResults))
            <!-- Parameter Clustering -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Jumlah Cluster</h4>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $k }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Total kelompok yang terbentuk</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Iterasi</h4>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $iterations }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Jumlah iterasi yang dilakukan</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">WCSS</h4>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($wcss, 4) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Within Cluster Sum of Squares</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Silhouette Score</h4>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($silhouetteScore, 4) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Kualitas hasil clustering</p>
                </div>
            </div>

            <!-- Centroids -->
            <div class="mb-6">
                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Centroid Setiap Cluster</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Cluster</th>
                                    @foreach($features as $feature)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            {{ str_replace('_', ' ', $feature) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($centroids as $i => $centroid)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-300">
                                            Cluster {{ $i + 1 }}
                                        </td>
                                        @foreach($features as $feature)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ number_format($centroid[$feature], 4) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cluster Results -->
            <div class="mb-6">
                <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-700">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Hasil Pengelompokan</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Cluster</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Sekolah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kecamatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tahun</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Dana</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Penerima</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($clusterResults as $clusterIndex => $cluster)
                                    @foreach($cluster as $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Cluster {{ $clusterIndex + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $data['school_name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $data['subdistrict_name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $data['year_received'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ number_format($data['amount']) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $data['recipient'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Download Results -->
            <div class="flex justify-end mt-6">
                <x-filament::button
                    wire:click="downloadResults"
                    icon="heroicon-o-arrow-down-tray"
                    color="success"
                >
                    Download Hasil (CSV)
                </x-filament::button>
            </div>
        @else
            <div class="p-4 text-amber-700 bg-amber-100 rounded-lg dark:bg-amber-200 dark:text-amber-800" role="alert">
                Sedang memproses clustering...
            </div>
        @endif
    </div>
</x-filament-panels::page>
