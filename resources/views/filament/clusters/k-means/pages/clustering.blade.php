<!-- resources/views/filament/clusters/k-means/pages/clustering.blade.php -->
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
            <h2 class="text-2xl font-bold text-black mb-6">Hasil Clustering</h2>

            <div class="space-y-6">
                <div class="bg-gray-900 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-black mb-4">Parameter Clustering</h3>
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

                <div class="bg-gray-900 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-black mb-4">Hasil Clustering</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-700">
                            <thead class="bg-gray-800">
                                <tr>
                                    @foreach ($this->header as $column)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                            {{ $column }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-gray-900 divide-y divide-gray-700">
                                @foreach ($this->rows as $row)
                                    <tr>
                                        @foreach ($row as $value)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ $value }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-filament::button
                        wire:click="goToOptimize"
                        color="primary"
                        class="bg-black text-black hover:bg-gray-900"
                    >
                        Optimasi Cluster
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="bg-black rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-black mb-4">Informasi Clustering</h3>
            <div class="space-y-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Metode Clustering</h4>
                    <p class="text-black">
                        Proses clustering menggunakan algoritma K-Means dengan parameter yang telah dioptimasi.
                        Hasil clustering menunjukkan pengelompokan data berdasarkan karakteristik yang sama.
                    </p>
                </div>

                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Interpretasi Hasil</h4>
                    <p class="text-black">
                        Setiap baris data dikelompokkan ke dalam cluster tertentu berdasarkan kedekatan dengan
                        centroid cluster. Angka cluster menunjukkan kelompok yang dimiliki oleh data tersebut.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
