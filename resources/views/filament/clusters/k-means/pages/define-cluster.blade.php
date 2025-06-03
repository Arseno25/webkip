<!-- resources/views/filament/pages/tentukan-cluster.blade.php -->
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
            <h2 class="text-2xl font-bold text-black mb-6">Penentuan Parameter Clustering</h2>

            <form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        class="bg-black text-black hover:bg-gray-900"
                    >
                        Proses Clustering
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="bg-black rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-black mb-4">Informasi Parameter</h3>
            <div class="space-y-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Type Centroid</h4>
                    <p class="text-black">
                        Pilih metode penentuan centroid awal untuk proses clustering:
                    </p>
                    <ul class="list-disc list-inside text-black mt-2 space-y-1">
                        <li>Rata-Rata Nilai: Menggunakan rata-rata dari data untuk centroid awal</li>
                        <li>Random: Memilih data secara acak sebagai centroid awal</li>
                        <li>K-Data Pertama: Menggunakan K data pertama sebagai centroid awal</li>
                    </ul>
                </div>

                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Jumlah Cluster (K)</h4>
                    <p class="text-black">
                        Tentukan jumlah cluster yang diinginkan (2-10). Jumlah cluster yang optimal akan ditentukan
                        melalui proses optimasi di langkah berikutnya.
                    </p>
                </div>

                <div class="bg-gray-900 rounded-lg p-4">
                    <h4 class="text-md font-medium text-black mb-2">Maksimum Iterasi</h4>
                    <p class="text-black">
                        Tentukan jumlah maksimum iterasi untuk proses clustering. Proses akan berhenti jika
                        sudah mencapai jumlah iterasi maksimum atau jika centroid sudah tidak berubah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
