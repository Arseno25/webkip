<?php
// resources/views/filament/clusters/k-means/pages/cluster-optimize.blade.php
?>
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Optimasi Jumlah Cluster
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Menentukan jumlah cluster optimal menggunakan metode Elbow (WCSS) dan Silhouette Score.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
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

        @if($optimalK)
            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- WCSS Chart -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Grafik WCSS (Elbow Method)
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Within Cluster Sum of Squares untuk setiap nilai K
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="wcssChart" class="w-full"></canvas>
                    </div>
                </div>

                <!-- Silhouette Score Chart -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Grafik Silhouette Score
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Silhouette Score untuk setiap nilai K
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="silhouetteChart" class="w-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Optimal K Card -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Nilai K Optimal</h4>
                                <p class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">{{ $optimalK }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Jumlah cluster optimal</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Silhouette Score Card -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Silhouette Score</h4>
                                <p class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">{{ number_format($silhouetteScore, 4) }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kualitas clustering</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WCSS Card -->
                <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">WCSS</h4>
                                <p class="mt-1 text-3xl font-semibold text-purple-600 dark:text-purple-400">{{ number_format($wcss[$optimalK], 4) }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Within Cluster Sum of Squares</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Detail Hasil Optimasi
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Perbandingan nilai WCSS dan Silhouette Score untuk setiap K
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">K</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">WCSS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Silhouette Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($wcss as $k => $value)
                                <tr class="{{ $k == $optimalK ? 'bg-blue-50 dark:bg-blue-900' : '' }} transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $k == $optimalK ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-300' }}">
                                        {{ $k }}
                                        @if($k == $optimalK)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                Optimal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ number_format($value, 4) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ number_format($silhouetteScores[$k], 4) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-end">
                <button wire:click="goToDefineCluster" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                    Lanjut ke Parameter Clustering
                </button>
            </div>

            <!-- Chart.js Script -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // WCSS Chart
                const wcssCtx = document.getElementById('wcssChart').getContext('2d');
                new Chart(wcssCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys({{ Js::from($wcss) }}),
                        datasets: [{
                            label: 'WCSS',
                            data: Object.values({{ Js::from($wcss) }}),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'WCSS'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'K (Jumlah Cluster)'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)'
                                }
                            }
                        }
                    }
                });

                // Silhouette Score Chart
                const silhouetteCtx = document.getElementById('silhouetteChart').getContext('2d');
                new Chart(silhouetteCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys({{ Js::from($silhouetteScores) }}),
                        datasets: [{
                            label: 'Silhouette Score',
                            data: Object.values({{ Js::from($silhouetteScores) }}),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.1,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Silhouette Score'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'K (Jumlah Cluster)'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)'
                                }
                            }
                        }
                    }
                });
            </script>
        @else
            <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 p-6">
                <div class="flex items-center justify-center">
                    <div class="flex-shrink-0">
                        <svg class="animate-spin h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Sedang Menghitung
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Mohon tunggu, sedang menghitung nilai K optimal...
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
