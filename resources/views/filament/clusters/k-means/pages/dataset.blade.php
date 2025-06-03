<x-filament-panels::page>
    @if (!$isDataLoaded)
        <div class="flex items-center justify-center p-8">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500 mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
    @else
        <div class="space-y-6">
            {{-- Tampilkan data dalam tabel --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Data Penerima KIP</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach ($header as $h)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $h }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($rawRows as $row)
                                <tr>
                                    @foreach ($row as $value)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $value }}
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
                    wire:click="lanjutkan"
                    color="primary"
                >
                    Lanjutkan ke Penentuan Cluster
                </x-filament::button>
            </div>

            {{-- Informasi --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dataset</h3>
                <div class="space-y-4">
                    <p class="text-gray-600">
                        Dataset ini berisi data penerima KIP yang akan digunakan untuk proses clustering.
                        Data yang digunakan meliputi:
                    </p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Nama Sekolah</li>
                        <li>Nama Kecamatan</li>
                        <li>Tahun Penerimaan</li>
                        <li>Jumlah Dana</li>
                        <li>Jumlah Penerima</li>
                    </ul>
                    <p class="text-gray-600">
                        Data ini akan diproses menggunakan algoritma K-Means Clustering untuk mengelompokkan
                        penerima KIP berdasarkan karakteristik yang sama. Untuk proses clustering, nama sekolah dan
                        kecamatan akan dikonversi ke ID masing-masing.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
