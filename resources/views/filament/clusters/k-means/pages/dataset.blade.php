<x-filament-panels::page>
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

    @if (!$isDataLoaded)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
        </div>
    @else
        <div class="space-y-6">
            {{-- Tampilkan data dalam tabel --}}
            <div class="bg-black rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-800">
                    <h3 class="text-lg font-medium text-black">Data Penerima KIP</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800">
                        <thead class="bg-gray-900">
                            <tr>
                                @foreach ($header as $h)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                                        {{ $h }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-black divide-y divide-gray-800">
                            @foreach ($rawRows as $row)
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

            {{-- Informasi --}}
            <div class="bg-black rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-black mb-4">Informasi Dataset</h3>
                <div class="space-y-4">
                    <p class="text-black">
                        Dataset ini berisi data penerima KIP yang akan digunakan untuk proses clustering.
                        Data yang digunakan meliputi:
                    </p>
                    <ul class="list-disc list-inside text-black space-y-2">
                        <li>Nama Sekolah</li>
                        <li>Nama Kecamatan</li>
                        <li>Tahun Penerimaan</li>
                        <li>Jumlah Dana</li>
                        <li>Jumlah Penerima</li>
                    </ul>
                    <p class="text-black">
                        Data ini akan diproses menggunakan algoritma K-Means Clustering untuk mengelompokkan
                        penerima KIP berdasarkan karakteristik yang sama. Untuk proses clustering, nama sekolah dan
                        kecamatan akan dikonversi ke ID masing-masing.
                    </p>
                </div>
            </div>

            {{-- Tombol Lanjutkan --}}
            <div class="flex justify-end">
                <x-filament::button
                    wire:click="goToDefineCluster"
                    color="primary"
                    class="bg-black text-black hover:bg-gray-900"
                >
                    Lanjutkan ke Penentuan Cluster
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
