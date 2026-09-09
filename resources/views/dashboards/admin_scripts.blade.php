{{-- Executive Admin Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {

        // Helper for mini sparkline options
        function createSparkline(elementId, color, data) {
            var el = document.querySelector(elementId);
            if (!el) return;
            var options = {
                series: [{ data: data }],
                chart: {
                    type: 'line',
                    width: 55,
                    height: 26,
                    sparkline: { enabled: true }
                },
                colors: [color],
                stroke: { curve: 'smooth', width: 2 },
                tooltip: { enabled: false }
            };
            var chart = new ApexCharts(el, options);
            chart.render();
        }

        // ── 1. Top 5 Sparklines ───────────────────────────────────────────────
        createSparkline('#sparkline-won-deals', '#7c3aed', [12, 14, 18, 15, 20, 24, 28]);
        createSparkline('#sparkline-leads', '#059669', [35, 42, 50, 48, 62, 70, 78]);
        createSparkline('#sparkline-projects', '#2563eb', [15, 18, 16, 20, 19, 22, 24]);
        createSparkline('#sparkline-customers', '#ea580c', [20, 22, 25, 28, 30, 34, 38]);
        createSparkline('#sparkline-employees', '#4f46e5', [28, 30, 32, 32, 34, 36, 37]);

        // ── 2. NSD Mini Sales Trend (Last 12 Months) ──────────────────────────
        var salesTrendEl = document.querySelector("#chart-sales-trend-mini");
        if (salesTrendEl) {
            var salesTrendChart = new ApexCharts(salesTrendEl, {
                series: [{
                    name: "Deals Matured",
                    data: [8, 12, 15, 11, 19, 24, 20, 28, 22, 26, 30, 34]
                }],
                chart: {
                    type: 'line',
                    height: 140,
                    toolbar: { show: false },
                    sparkline: { enabled: false }
                },
                colors: ["#6366f1"],
                stroke: {
                    curve: 'smooth',
                    width: 2.5
                },
                markers: {
                    size: 3.5,
                    colors: ["#6366f1"],
                    strokeColors: "#fff",
                    strokeWidth: 2
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    labels: {
                        style: {
                            fontSize: '9px',
                            fontFamily: 'Inter, sans-serif'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '9px',
                            fontFamily: 'Inter, sans-serif'
                        },
                        formatter: function(val) {
                            return Math.floor(val);
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 2
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(v) { return v + ' deals'; }
                    }
                }
            });
            salesTrendChart.render();

            // Load real AJAX data if available
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/home/chartdata',
                success: function(response) {
                    if (response.status == true && response.sales && response.sales.length > 0) {
                        var labels = [], totals = [];
                        response.sales.forEach(function(el) {
                            totals.push(parseInt(el.total));
                            labels.push(el.month);
                        });
                        salesTrendChart.updateSeries([{ data: totals }]);
                        salesTrendChart.updateOptions({ xaxis: { categories: labels } });
                    }
                }
            });
        }

        // ── 3. OD Projects Health Donut ───────────────────────────────────────
        var odDonutEl = document.querySelector("#chart-od-projects-donut");
        if (odDonutEl) {
            var onTrack = Number('{{ $adminData["od_on_track"] ?? 1 }}');
            var atRisk = Number('{{ $adminData["od_at_risk"] ?? 1 }}');
            var overdue = Number('{{ $adminData["od_overdue"] ?? 1 }}');
            if (onTrack === 0 && atRisk === 0 && overdue === 0) onTrack = 1;

            var odDonutChart = new ApexCharts(odDonutEl, {
                series: [onTrack, atRisk, overdue],
                chart: {
                    type: 'donut',
                    width: 130,
                    height: 130,
                    sparkline: { enabled: true }
                },
                labels: ['On Track', 'At Risk', 'Overdue'],
                colors: ['#10b981', '#f59e0b', '#ef4444'],
                stroke: { width: 2, colors: ['#fff'] },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Projects',
                                    fontSize: '9px',
                                    fontWeight: 600,
                                    color: '#94a3b8',
                                    formatter: function(w) {
                                        return '{{ $adminData["active_projects_count"] ?? 3 }}';
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    theme: 'dark'
                }
            });
            odDonutChart.render();
        }

        // ── 4. CSD Customer Health Donut ──────────────────────────────────────
        var csdDonutEl = document.querySelector("#chart-csd-health-donut");
        if (csdDonutEl) {
            var csdHealthy = Number('{{ $adminData["csd_healthy"] ?? 18 }}');
            var csdAttention = Number('{{ $adminData["csd_attention"] ?? 4 }}');
            var csdAtRisk = Number('{{ $adminData["csd_at_risk"] ?? 2 }}');

            var csdDonutChart = new ApexCharts(csdDonutEl, {
                series: [csdHealthy, csdAttention, csdAtRisk],
                chart: {
                    type: 'donut',
                    width: 130,
                    height: 130,
                    sparkline: { enabled: true }
                },
                labels: ['Healthy', 'Attention', 'At Risk'],
                colors: ['#10b981', '#f59e0b', '#ef4444'],
                stroke: { width: 2, colors: ['#fff'] },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Clients',
                                    fontSize: '9px',
                                    fontWeight: 600,
                                    color: '#94a3b8',
                                    formatter: function(w) {
                                        return '{{ $adminData["customers_count"] ?? 24 }}';
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    theme: 'dark'
                }
            });
            csdDonutChart.render();
        }
    });
</script>
