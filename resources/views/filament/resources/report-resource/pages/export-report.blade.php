<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-xl font-bold mb-4">Export Data Penerima KIP</h2>
            <p class="mb-4">Silakan pilih filter di atas untuk menyaring data yang ingin diekspor. Jika tidak ada filter yang dipilih, semua data akan diekspor.</p>
            
            <div class="flex justify-end">
                <x-filament::button
                    wire:click="export"
                    color="primary"
                    icon="heroicon-o-arrow-down-tray"
                >
                    Export Excel
                </x-filament::button>
            </div>
            
            <div class="mt-8">
                <h3 class="text-lg font-semibold mb-2">Petunjuk Penggunaan:</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>Pilih <strong>Kecamatan</strong> untuk menyaring data berdasarkan kecamatan tertentu.</li>
                    <li>Pilih <strong>Sekolah</strong> untuk menyaring data berdasarkan sekolah tertentu.</li>
                    <li>Pilih <strong>Jenjang</strong> untuk menyaring data berdasarkan jenjang sekolah (SD, SMP, SMA, SMK).</li>
                    <li>Pilih <strong>Tahun Penerimaan</strong> untuk menyaring data berdasarkan tahun penerimaan KIP.</li>
                    <li>Pilih <strong>Jenis Kelamin</strong> untuk menyaring data berdasarkan jenis kelamin penerima KIP.</li>
                    <li>Klik tombol <strong>Export Excel</strong> untuk mengunduh data dalam format Excel (.xlsx).</li>
                </ul>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>File Excel yang dihasilkan akan berisi kolom-kolom berikut:</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>Nomor urut</li>
                                <li>Nama penerima KIP</li>
                                <li>Jenis kelamin</li>
                                <li>Kelas</li>
                                <li>Nomor KIP</li>
                                <li>Tahun penerimaan</li>
                                <li>Nama sekolah</li>
                                <li>Jenjang sekolah</li>
                                <li>Nama kecamatan</li>
                                <li>Alamat</li>
                                <li>Koordinat (latitude dan longitude)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
