<!-- resources/views/filament/pages/tentukan-cluster.blade.php -->
<x-filament::page>
    <div class="space-y-6">
        <div class=" rounded-xl shadow p-6">
            <h2 class="text-lg font-medium mb-4">Tentukan Centroid & Cluster</h2>
            
            <form wire:submit.prevent="submit" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end mt-6">
                    <x-filament::button type="submit">
                        Simpan & Proses
                    </x-filament::button>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-950 rounded-xl p-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Keterangan Parameter:</h3>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                <li><strong>Type Centroid:</strong>
                    <ul class="ml-6 mt-1 space-y-1">
                        <li>Rata-Rata Nilai: Menggunakan rata-rata sebagai titik awal</li>
                        <li>Random: Memilih titik awal secara acak</li>
                        <li>K-Data Pertama: Menggunakan K data pertama sebagai titik awal</li>
                    </ul>
                </li>
                <li><strong>Jumlah Cluster:</strong> Banyaknya kelompok yang akan dibentuk (K)</li>
                <li><strong>Max Perulangan:</strong> Batas maksimum iterasi jika belum konvergen</li>
            </ul>
        </div>
    </div>
</x-filament::page>