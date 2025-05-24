<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Optimasi Metode Euclidean Distance</h2>
                <div class="flex items-center space-x-2">
                    <span
                        class="px-3 py-1 text-sm font-medium rounded-full {{ $bestK ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $bestK ? 'Analisis Selesai' : 'Menunggu Data' }}
                    </span>
                </div>
            </div>

            <!-- Form Input Parameter -->
            <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end" style="margin-bottom: 2rem">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Cluster (K) Minimum</label>
                    <input style="background: transparent;" type="number" name="k_min" value="{{ request('k_min', 1) }}" min="1" max="100"
                        class="form-input rounded-md border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Cluster (K) Maksimum</label>
                    <input style="background: transparent;" type="number" name="k_max" value="{{ request('k_max', 10) }}" min="1"
                        max="100" class="form-input rounded-md border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maksimal Iterasi</label>
                    <input style="background: transparent;" type="number" name="max_iter" value="{{ request('max_iter', 100) }}" min="1"
                        max="1000" class="form-input rounded-md border-gray-300" />
                </div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md" style="background: #ffb23a;">Proses</button>
            </form>

            @if (!empty($wcss))
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Grafik Euclidean Distance -->
                    <div class="lg:col-span-2  rounded-lg border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold mb-4">Grafik Euclidean Distance</h3>
                        <div class="h-80">
                            <canvas id="euclidean distanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Informasi Penting -->
                    <div class="space-y-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">Rekomendasi Cluster</h3>
                            <div class="text-3xl font-bold text-blue-600">{{ $bestK }}</div>
                            <p class="text-sm text-blue-600 mt-2">Jumlah cluster optimal berdasarkan metode euclidean distance</p>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                            <h3 class="text-lg font-semibold text-purple-800 mb-2">Silhouette Score</h3>
                            <div class="text-3xl font-bold text-purple-600">
                                {{ number_format(max($silhouetteScores), 4) }}
                            </div>
                            <p class="text-sm text-purple-600 mt-2">Skor validasi cluster terbaik</p>
                        </div>

                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                            <h3 class="text-lg font-semibold text-green-800 mb-2">WCSS Minimum</h3>
                            <div class="text-3xl font-bold text-green-600">
                                {{ number_format(min($wcss), 4) }}
                            </div>
                            <p class="text-sm text-green-600 mt-2">Within Cluster Sum of Squares terendah</p>
                        </div>
                    </div>
                </div>

                <!-- Tabel Detail -->
                <div class=" rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Perhitungan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        K
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        WCSS
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Silhouette Score
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="background: transparent;">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class=" divide-y divide-gray-200">
                                @foreach ($wcss as $k => $value)
                                    <tr class="{{ $k == $bestK ? 'bg-green-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $k }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($value, 4) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($silhouetteScores[$k], 4) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($k == $bestK)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Optimal
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    -
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Belum ada data untuk optimasi. Silakan upload dataset terlebih dahulu.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if (!empty($wcss))
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('euclidean distanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(array_keys($wcss)) !!},
                        datasets: [{
                            label: 'WCSS',
                            data: {!! json_encode(array_values($wcss)) !!},
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: function(context) {
                                const index = context.dataIndex;
                                const k = {!! json_encode($bestK) !!};
                                return index + 1 === k ? 'rgb(16, 185, 129)' : 'rgb(59, 130, 246)';
                            },
                            pointRadius: function(context) {
                                const index = context.dataIndex;
                                const k = {!! json_encode($bestK) !!};
                                return index + 1 === k ? 6 : 4;
                            }
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `WCSS: ${context.parsed.y.toFixed(4)}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Jumlah Cluster (K)',
                                    font: {
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Within Cluster Sum of Squares (WCSS)',
                                    font: {
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        }
                    }
                });
            </script>
        @endif
    </div>

    @if(!empty($wcss))
        <div class="flex justify-end mt-4 space-x-4">
            <x-filament::button tag="a" href="{{ url('/admin/k-means/define-cluster') }}">
                Lanjut ke Penentuan Centroid
            </x-filament::button>
        </div>
    @endif
</x-filament::page>
