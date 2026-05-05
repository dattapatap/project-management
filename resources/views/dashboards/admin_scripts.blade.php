{{-- Admin Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // Sales Chart
        var salesOptions = {
            series: [{
                name: "Matured Clients",
                data: []
            }],
            chart: {
                height: 270,
                type: "area",
                toolbar: {
                    show: false
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
                categories: []
            },
        };
        var adminSalesChart = new ApexCharts(document.querySelector("#admin-sales-chart"), salesOptions);
        adminSalesChart.render();

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/home/chartdata',
            success: function(response) {
                if (response.status == true) {
                    var labels = [];
                    var totals = [];
                    response.sales.forEach(element => {
                        totals.push(parseInt(element.total));
                        labels.push(element.month);
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

        // Admin Project Health Chart
        var projSeries = [
            Number('{{ $adminData["proj_todo"] ?? 0 }}'),
            Number('{{ $adminData["proj_in_progress"] ?? 0 }}'),
            Number('{{ $adminData["proj_completed"] ?? 0 }}')
        ];
        var projOptions = {
            series: projSeries,
            chart: {
                type: 'donut',
                height: 300,
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
                width: 5,
                colors: ['#fff']
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'bottom',
                offsetY: 0,
                height: 30,
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Projects',
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#9599ad',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontWeight: 700,
                                color: '#343a40',
                                offsetY: 5
                            }
                        }
                    }
                }
            }
        };
        var projChart = new ApexCharts(document.querySelector("#admin-project-chart"), projOptions);
        projChart.render();
    });
</script>
