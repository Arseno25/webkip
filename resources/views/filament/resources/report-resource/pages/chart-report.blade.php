<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{
            chartData: @js($this->getChartData()),
            charts: {
                subdistrictChart: null,
                levelChart: null,
                genderChart: null,
                yearChart: null
            },
            init() {
                this.initCharts();
                
                Livewire.on('refresh-chart', () => {
                    $wire.getChartData().then(data => {
                        this.chartData = data;
                        this.updateCharts();
                    });
                });
            },
            
            initCharts() {
                // Grafik Distribusi berdasarkan Kecamatan
                const subdistrictCtx = document.getElementById('subdistrictChart').getContext('2d');
                this.charts.subdistrictChart = new Chart(subdistrictCtx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.subdistrictChart.labels,
                        datasets: [{
                            label: 'Jumlah Penerima KIP',
                            data: this.chartData.subdistrictChart.data,
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
                
                // Grafik Distribusi berdasarkan Jenjang Sekolah
                const levelCtx = document.getElementById('levelChart').getContext('2d');
                this.charts.levelChart = new Chart(levelCtx, {
                    type: 'pie',
                    data: {
                        labels: this.chartData.levelChart.labels,
                        datasets: [{
                            data: this.chartData.levelChart.data,
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.7)',  // SD - Green
                                'rgba(59, 130, 246, 0.7)',  // SMP - Blue
                                'rgba(245, 158, 11, 0.7)',  // SMA - Yellow
                                'rgba(239, 68, 68, 0.7)'    // SMK - Red
                            ],
                            borderColor: [
                                'rgba(16, 185, 129, 1)',
                                'rgba(59, 130, 246, 1)',
                                'rgba(245, 158, 11, 1)',
                                'rgba(239, 68, 68, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
                
                // Grafik Distribusi berdasarkan Jenis Kelamin
                const genderCtx = document.getElementById('genderChart').getContext('2d');
                this.charts.genderChart = new Chart(genderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: this.chartData.genderChart.labels,
                        datasets: [{
                            data: this.chartData.genderChart.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',  // Laki-laki - Blue
                                'rgba(236, 72, 153, 0.7)'   // Perempuan - Pink
                            ],
                            borderColor: [
                                'rgba(59, 130, 246, 1)',
                                'rgba(236, 72, 153, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
                
                // Grafik Distribusi berdasarkan Tahun Penerimaan
                const yearCtx = document.getElementById('yearChart').getContext('2d');
                this.charts.yearChart = new Chart(yearCtx, {
                    type: 'line',
                    data: {
                        labels: this.chartData.yearChart.labels,
                        datasets: [{
                            label: 'Jumlah Penerima KIP',
                            data: this.chartData.yearChart.data,
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            },
            
            updateCharts() {
                // Update Grafik Distribusi berdasarkan Kecamatan
                this.charts.subdistrictChart.data.labels = this.chartData.subdistrictChart.labels;
                this.charts.subdistrictChart.data.datasets[0].data = this.chartData.subdistrictChart.data;
                this.charts.subdistrictChart.update();
                
                // Update Grafik Distribusi berdasarkan Jenjang Sekolah
                this.charts.levelChart.data.labels = this.chartData.levelChart.labels;
                this.charts.levelChart.data.datasets[0].data = this.chartData.levelChart.data;
                this.charts.levelChart.update();
                
                // Update Grafik Distribusi berdasarkan Jenis Kelamin
                this.charts.genderChart.data.labels = this.chartData.genderChart.labels;
                this.charts.genderChart.data.datasets[0].data = this.chartData.genderChart.data;
                this.charts.genderChart.update();
                
                // Update Grafik Distribusi berdasarkan Tahun Penerimaan
                this.charts.yearChart.data.labels = this.chartData.yearChart.labels;
                this.charts.yearChart.data.datasets[0].data = this.chartData.yearChart.data;
                this.charts.yearChart.update();
            }
        }">
            <!-- Grafik Distribusi berdasarkan Kecamatan -->
            <div class="p-6 bg-white rounded-xl shadow">
                <h2 class="text-xl font-bold mb-4">Distribusi Berdasarkan Kecamatan</h2>
                <div class="h-80">
                    <canvas id="subdistrictChart"></canvas>
                </div>
            </div>

            <!-- Grafik Distribusi berdasarkan Jenjang Sekolah -->
            <div class="p-6 bg-white rounded-xl shadow">
                <h2 class="text-xl font-bold mb-4">Distribusi Berdasarkan Jenjang Sekolah</h2>
                <div class="h-80">
                    <canvas id="levelChart"></canvas>
                </div>
            </div>

            <!-- Grafik Distribusi berdasarkan Jenis Kelamin -->
            <div class="p-6 bg-white rounded-xl shadow">
                <h2 class="text-xl font-bold mb-4">Distribusi Berdasarkan Jenis Kelamin</h2>
                <div class="h-80">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Grafik Distribusi berdasarkan Tahun Penerimaan -->
            <div class="p-6 bg-white rounded-xl shadow">
                <h2 class="text-xl font-bold mb-4">Distribusi Berdasarkan Tahun Penerimaan</h2>
                <div class="h-80">
                    <canvas id="yearChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-filament-panels::page>
