<!-- resources/views/filament/pages/proses-kmeans.blade.php -->
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Informasi Proses --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Proses Clustering</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Jumlah Iterasi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $iterations }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Silhouette Score</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($silhouetteScore, 4) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Jumlah Cluster</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ count($result['centroids'] ?? []) }}</p>
                </div>
            </div>
        </div>

        {{-- Hasil Clustering --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Hasil K-Means Clustering</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach ($header as $h)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $h) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($header as $h)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($h === 'amount')
                                            Rp {{ number_format($row[$h], 0, ',', '.') }}
                                        @elseif (is_array($row[$h]))
                                            {{ json_encode($row[$h]) }}
                                        @else
                                            {{ $row[$h] }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tombol Lanjutkan --}}
        <div class="flex justify-end">
            <x-filament::button
                wire:click="goToOptimize"
                color="primary"
                class="inline-flex items-center px-4 py-2 bg-primary-600 dark:bg-primary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:border-primary-700 dark:focus:border-primary-600 focus:ring focus:ring-primary-200 dark:focus:ring-primary-300 active:bg-primary-600 dark:active:bg-primary-700 disabled:opacity-25 transition"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
                Optimasi Cluster
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
