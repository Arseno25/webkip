<!-- resources/views/filament/pages/proses-kmeans.blade.php -->
<x-filament::page>
    <div class="space-y-6">
        <!-- Hasil akhir K-Means bisa ditampilkan di bawah sini -->
        <h2 class="text-2xl font-bold mt-8">Hasil K-Means</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-950 p-4 rounded">
                <div class="text-sm text-gray-500">Jumlah Cluster</div>
                <div class="text-2xl font-bold">{{ count($result['clusters']) }}</div>
            </div>
            <div class="bg-gray-950 p-4 rounded">
                <div class="text-sm text-gray-500">Jumlah Iterasi</div>
                <div class="text-2xl font-bold">{{ $iterations }}</div>
            </div>
            <div class="bg-gray-950 p-4 rounded">
                <div class="text-sm text-gray-500">Silhouette Score</div>
                <div class="text-2xl font-bold">{{ number_format($silhouetteScore, 4) }}</div>
            </div>
        </div>
        @foreach($result['history'] as $iterasi => $step)
            <div class="mb-8">
                <div class="bg-purple-600 text-white px-4 py-2 rounded-t font-bold">
                    Perulangan Ke - {{ $iterasi + 1 }}
                </div>
                <div class=" p-4 rounded-b shadow">
                    <!-- Tabel Centroid -->
                    <h4 class="font-bold mb-2 mt-2">Penentuan Centroid</h4>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full mb-4 border">
                            <thead>
                                <tr>
                                    @foreach($step['centroids'] as $idx => $centroid)
                                        <th class="px-4 py-2 border">Centroid {{ $idx + 1 }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < count($step['centroids'][0]); $i++)
                                    <tr>
                                        @foreach($step['centroids'] as $centroid)
                                            <td class="px-4 py-2 border">{{ number_format($centroid[$i], 2) }}</td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabel Euclidean Distance -->
                    <h4 class="font-bold mb-2 mt-6">Perulangan {{ $iterasi + 1 }} - Hitung Euclidean Distance</h4>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border">Nama</th>
                                    @foreach($step['centroids'] as $idx => $centroid)
                                        <th class="px-4 py-2 border">Centroid {{ $idx + 1 }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($step['distances'] as $idx => $distArr)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $rows[$idx]['nama'] }}</td>
                                        @foreach($distArr as $dist)
                                            <td class="px-4 py-2 border">{{ number_format($dist, 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end space-x-4">
            <x-filament::button
                wire:click="$refresh"
                color="secondary">
                Proses Ulang
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ url('/admin/k-means/clustering') }}"
                color="primary">
                Lanjut ke Hasil Clustering
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ url('/admin/k-means/cluster-optimize') }}"
                color="secondary">
                Analisis dengan Metode Elbow
            </x-filament::button>
        </div>
    </div>
</x-filament::page>
