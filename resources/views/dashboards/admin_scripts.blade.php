{{-- Admin Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {

        // ── Global Sales Trend (12 months, AJAX) ─────────────────────────────────
        var adminSalesChart = new ApexCharts(document.querySelector("#admin-sales-chart"), {
            series: [{
                name: "Matured Clients",
                data: []
            }],
            chart: {
                height: 330,
                type: "area",
                toolbar: {
                    show: false
                },
                sparkline: {
                    enabled: false
                }
            },
            colors: ["#3b5de7"],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: "smooth",
                width: 2
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [20, 100]
                }
            },
            xaxis: {
                categories: [],
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f1f1'
            },
            tooltip: {
                y: {
                    formatter: function(v) {
                        return v + ' closures';
                    }
                }
            }
        });
        adminSalesChart.render();

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/home/chartdata',
            success: function(response) {
                if (response.status == true) {
                    var labels = [],
                        totals = [];
                    response.sales.forEach(function(el) {
                        totals.push(parseInt(el.total));
                        labels.push(el.month);
                    });
                    adminSalesChart.updateSeries([{
                        data: totals
                    }]);
                    adminSalesChart.updateOptions({
                        xaxis: {
                            categories: labels
                        }
                    });
                }
            }
        });

        // ── Task Completion Trend (12 months, server-rendered) ────────────────────
        var taskTrendData = @json($adminData['task_completion_trend']);
        var taskLabels = taskTrendData.map(function(d) {
            return d.month;
        });
        var taskCounts = taskTrendData.map(function(d) {
            return d.count;
        });

        var adminTaskChart = new ApexCharts(document.querySelector("#admin-task-trend-chart"), {
            series: [{
                name: "Tasks Completed",
                data: taskCounts
            }],
            chart: {
                height: 330,
                type: "bar",
                toolbar: {
                    show: false
                }
            },
            colors: ["#34c38f"],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%'
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: taskLabels,
                labels: {
                    style: {
                        fontSize: '9px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f1f1'
            },
            tooltip: {
                y: {
                    formatter: function(v) {
                        return v + ' tasks';
                    }
                }
            }
        });
        adminTaskChart.render();

        // ── Project Health Donut ──────────────────────────────────────────────────
        var projSeries = [
            Number('{{ $adminData["proj_todo"] ?? 0 }}'),
            Number('{{ $adminData["proj_in_progress"] ?? 0 }}'),
            Number('{{ $adminData["proj_completed"] ?? 0 }}')
        ];
        var projChart = new ApexCharts(document.querySelector("#admin-project-chart"), {
            series: projSeries,
            chart: {
                type: 'donut',
                height: 200,
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 2,
                    left: 2,
                    blur: 8,
                    opacity: 0.1
                }
            },
            labels: ['ToDo', 'InProgress', 'Completed'],
            colors: ['#adb5bd', '#f1b44c', '#34c38f'],
            stroke: {
                show: true,
                width: 4,
                colors: ['#fff']
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'bottom',
                offsetY: 0,
                height: 28,
                fontSize: '11px'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#9599ad',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce(function(a, b) {
                                        return a + b;
                                    }, 0);
                                }
                            },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontWeight: 700,
                                color: '#343a40',
                                offsetY: 5
                            }
                        }
                    }
                }
            }
        });
        projChart.render();

        // ── CSD Client Health Donut ───────────────────────────────────────────────
        var csdHealthChart = new ApexCharts(document.querySelector("#admin-csd-health-chart"), {
            series: [
                Number('{{ $adminData["csd_healthy"] ?? 0 }}'),
                Number('{{ $adminData["csd_at_risk"] ?? 0 }}'),
                Number('{{ $adminData["csd_churning"] ?? 0 }}')
            ],
            chart: {
                type: 'donut',
                height: 200,
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 2,
                    left: 2,
                    blur: 8,
                    opacity: 0.1
                }
            },
            labels: ['Healthy', 'At Risk', 'Churning'],
            colors: ['#34c38f', '#f1b44c', '#f46a6a'],
            stroke: {
                show: true,
                width: 4,
                colors: ['#fff']
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'bottom',
                offsetY: 0,
                height: 28,
                fontSize: '11px'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Clients',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#9599ad',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce(function(a, b) {
                                        return a + b;
                                    }, 0);
                                }
                            },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontWeight: 700,
                                color: '#343a40',
                                offsetY: 5
                            }
                        }
                    }
                }
            }
        });
        csdHealthChart.render();

        // ── Project Category Distribution Bar Chart ───────────────────────────────
        var deptDistData = @json($adminData['dept_project_distribution']);
        var deptLabels = deptDistData.map(function(d) {
            return d.name;
        });
        var deptCounts = deptDistData.map(function(d) {
            return d.projects_count;
        });

        var adminDeptChart = new ApexCharts(document.querySelector("#admin-dept-projects-chart"), {
            series: [{
                name: "Projects",
                data: deptCounts
            }],
            chart: {
                type: 'bar',
                height: 260,
                toolbar: {
                    show: false
                }
            },
            colors: ["#556ee6"],
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    horizontal: true,
                    barHeight: '55%'
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px'
                }
            },
            xaxis: {
                categories: deptLabels,
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(v) {
                        return v + ' project(s)';
                    }
                }
            }
        });
        adminDeptChart.render();
    });
</script>
