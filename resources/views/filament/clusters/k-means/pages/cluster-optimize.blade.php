<x-filament-panels::page>
    <div class="space-y-6">
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-black rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-black mb-6">Optimasi Jumlah Cluster</h2>

            <div class="space-y-6">
                <div class="bg-gray-900 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-black mb-4">Parameter Optimasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-black font-medium">Jumlah Cluster (K):</p>
                            <p class="text-black">{{ $this->k }}</p>
                        </div>
                        <div>
                            <p class="text-black font-medium">Maksimum Iterasi:</p>
                            <p class="text-black">{{ $this->maxIterations }}</p>
                        </div>
                        <div>
                            <p class="text-black font-medium">Type Centroid:</p>
                            <p class="text-black">{{ $this->centroidType }}</p>
                        </div>
                    </div>
                </div>

                @if (!empty($this->wcss))
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Grafik WCSS -->
                        <div class="lg:col-span-2 bg-gray-900 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-black mb-4">Grafik WCSS</h3>
                            <div class="h-80">
                                <canvas id="wcssChart"></canvas>
                            </div>
                        </div>

                        <!-- Informasi Penting -->
                        <div class="space-y-4">
                            <div class="bg-gray-900 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-black mb-2">Nilai K Optimal</h3>
                                <div class="text-3xl font-bold text-black">{{ $this->optimalK }}</div>
                                <p class="text-sm text-black mt-2">Jumlah cluster optimal berdasarkan Silhouette Score</p>
                            </div>

                            <div class="bg-gray-900 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-black mb-2">Silhouette Score</h3>
                                <div class="text-3xl font-bold text-black">
                                    {{ number_format($this->silhouetteScore, 4) }}
                                </div>
                                <p class="text-sm text-black mt-2">Skor validasi cluster terbaik</p>
                            </div>

                            <div class="bg-gray-900 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-black mb-2">WCSS Minimum</h3>
                                <div class="text-3xl font-bold text-black">
                                    {{ number_format(min($this->wcss), 4) }}
                                </div>
                                <p class="text-sm text-black mt-2">Within Cluster Sum of Squares terendah</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Detail -->
                    <div class="bg-gray-900 rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg font-semibold text-black">Detail Perhitungan</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-700">
                                <thead class="bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                            K
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                            WCSS
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                            Silhouette Score
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-900 divide-y divide-gray-700">
                                    @foreach ($this->wcss as $k => $value)
                                        <tr class="{{ $k == $this->optimalK ? 'bg-gray-800' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ $k }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ number_format($value, 4) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ number_format($this->silhouetteScores[$k], 4) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($k == $this->optimalK)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Optimal
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
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
                @endif

                <div class="flex justify-end">
                    <x-filament::button
                        wire:click="goToClustering"
                        color="primary"
                        class="bg-black text-black hover:bg-gray-900"
                    >
                        Lanjut ke Hasil Clustering
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="bg-black rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-black mb-4">Informasi Optimasi</h3>
            <div class="space-y-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Metode Optimasi</h4>
                    <p class="text-black">
                        Proses optimasi menggunakan metode Silhouette Score untuk menentukan jumlah cluster optimal.
                        Nilai Silhouette Score yang lebih tinggi menunjukkan kualitas clustering yang lebih baik.
                    </p>
                </div>

                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Interpretasi Hasil</h4>
                    <p class="text-black">
                        Jumlah cluster optimal (K) yang dipilih adalah nilai yang menghasilkan Silhouette Score
                        tertinggi. Nilai ini akan digunakan untuk proses clustering selanjutnya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($this->wcss))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('wcssChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($this->wcss)) !!},
                    datasets: [{
                        label: 'WCSS',
                        data: {!! json_encode(array_values($this->wcss)) !!},
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: function(context) {
                            const index = context.dataIndex;
                            const k = {!! json_encode($this->optimalK) !!};
                            return index + 2 === k ? 'rgb(16, 185, 129)' : 'rgb(59, 130, 246)';
                        },
                        pointRadius: function(context) {
                            const index = context.dataIndex;
                            const k = {!! json_encode($this->optimalK) !!};
                            return index + 2 === k ? 6 : 4;
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
                                color: 'black',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: 'black'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Within Cluster Sum of Squares (WCSS)',
                                color: 'black',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: 'black'
                            }
                        }
                    }
                }
            });
        </script>
    @endif
</x-filament-panels::page>
