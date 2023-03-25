$(document).ready(function(){

    var options = {
        series: [
            {
                name: "Total Sales",
                type: "bar",
                data: [23, 32, 27, 38, 27, 32, 27, 38, 22, 31, 21, 16],
            },
        ],
        labels: ["Jan", "Feb","Mar","Apr","May","Jun","Jul","Aug", "Sep","Oct","Nov","Dec",],
        chart: { height:250, type: "line", toolbar: { show: !1 } },
        stroke: { width: [0, 0, 3], curve: "smooth" },
        plotOptions: { bar: { horizontal: !1, columnWidth: "60%" } },
        dataLabels: { enabled: !1 },
        legend: { show: !1 },
        colors: ["#48a697"],
    },
    chart = new ApexCharts(
        document.querySelector("#line-column-chart"),
        options
    );
    chart.render();

    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/home/chartdata',
        success: function(response) {
            console.log(response);
            if(response.status == true){
                var RgisterFees = response.sales;
                var label = [];
                var totsale  = [];
                RgisterFees.forEach(element => {
                    totsale.push(parseInt(element.total));
                    label.push(element.month);
                });
                setTimeout(() => {
                    chart.updateSeries([{ data : totsale }]);
                    chart.updateOptions({ labels: label });
                }, 900);
            }
        },
    });


});


