<!-- resources/views/filament/clusters/k-means/pages/define-cluster.blade.php -->
<?php
?>
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Parameter K-Means Clustering
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Tentukan parameter yang akan digunakan dalam proses clustering.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Section -->
        @if(session('error'))
            <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Parameter Form -->
        <form wire:submit.prevent="submit">
            <div class="space-y-6">
                <!-- Number of Clusters -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Jumlah Cluster (K)
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Tentukan jumlah kelompok yang akan dibentuk
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <input type="number" wire:model="k" min="2" max="10" step="1"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white sm:text-sm"
                                required>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Nilai optimal dari hasil optimasi adalah {{ $k }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Maximum Iterations -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Maksimum Iterasi
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Batasi jumlah iterasi untuk mencapai konvergensi
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <input type="number" wire:model="maxIterations" min="50" max="1000" step="10"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white sm:text-sm"
                                required>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Jumlah maksimum iterasi untuk mencapai konvergensi (50-1000)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Centroid Initialization -->
                <div class="bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Metode Inisialisasi Centroid
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Pilih metode untuk menentukan posisi awal centroid
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <select wire:model="centroidType"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white sm:text-sm">
                                <option value="kmeans++">K-Means++ (Rekomendasi)</option>
                                <option value="average">Average (Rata-rata)</option>
                                <option value="random">Random (Acak)</option>
                            </select>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Keterangan Metode:</h4>
                                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <li><span class="font-medium">K-Means++:</span> Metode cerdas yang memilih centroid awal dengan optimal</li>
                                    <li><span class="font-medium">Average:</span> Menggunakan rata-rata dan sebaran data untuk penentuan centroid</li>
                                    <li><span class="font-medium">Random:</span> Memilih centroid secara acak dari data yang ada</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Informasi Parameter
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Jumlah Cluster (K)</h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Menentukan berapa banyak kelompok yang akan dibentuk. Nilai optimal telah ditentukan dari
                                        proses optimasi sebelumnya menggunakan metode Silhouette Score.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Maksimum Iterasi</h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Batas maksimum iterasi yang akan dilakukan dalam proses clustering. Proses akan berhenti
                                        jika telah mencapai konvergensi atau mencapai batas maksimum iterasi.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Metode Inisialisasi</h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        K-Means++ adalah metode inisialisasi yang lebih baik karena memilih titik pusat awal
                                        dengan probabilitas berdasarkan jarak, sehingga menghasilkan clustering yang lebih stabil.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 dark:bg-primary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:border-primary-700 dark:focus:border-primary-600 focus:ring focus:ring-primary-200 dark:focus:ring-primary-300 active:bg-primary-600 dark:active:bg-primary-700 disabled:opacity-25 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                        Mulai Proses Clustering
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
