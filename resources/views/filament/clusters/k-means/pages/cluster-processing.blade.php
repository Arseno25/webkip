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
            >
                Optimasi Cluster
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
