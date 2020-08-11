/*
 * JavaScript for Reports Submenu Page
 *
 */
jQuery( function($) {

    var ctx = $('#payment-report-chart');


    if( !pms_chart_labels )
        pms_chart_labels = [];

    if( !pms_chart_earnings )
        pms_chart_earnings = [];

    if( !pms_chart_payments )
        pms_chart_payments = [];


    var paymentReportsChart = new Chart( ctx, {
        type : 'line',
        data : {
            labels : pms_chart_labels,
            datasets : [
                {
                    label : 'Earnings',
                    yAxisID : 'y-axis-earnings',
                    borderColor : 'rgba(39,174,96,0.5)',
                    backgroundColor : 'rgba(39,174,96,0.1)',
                    pointBackgroundColor : 'rgba(39,174,96,1)',
                    lineTension : 0,
                    data : pms_chart_earnings
                },
                {
                    label : 'Payments',
                    yAxisID : 'y-axis-payments',
                    borderColor : 'rgba(230,126,34,0.5)',
                    backgroundColor : 'rgba(230,126,34,0.1)',
                    pointBackgroundColor : 'rgba(230,126,34,1)',
                    lineTension : 0,
                    data : pms_chart_payments
                }
            ]
        },
        options : {

            responsive : true,

            // Tooltips
            tooltips : {
                mode : 'x-axis',
                callbacks : {
                    label : function( tooltipItem, data ) {

                        if( tooltipItem.datasetIndex == 0 )
                            return data.datasets[0].label + ' (' + pms_currency + ')' +  ' : ' + data.datasets[0].data[tooltipItem.index];

                        return data.datasets[tooltipItem.datasetIndex].label + ' : ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

                    }
                }
            },

            // Legend
            legend : {
                position : 'bottom',
                labels : {
                    padding: 40,
                    boxWidth : 30
                }
            },

            // Two y-axes for the revenue and for the payments count
            scales : {
                yAxes : [
                    {
                        type : 'linear',
                        position: 'right',
                        id : 'y-axis-earnings',
                        ticks : {
                            beginAtZero : true
                        }
                    },
                    {
                        type : 'linear',
                        position: 'right',
                        id : 'y-axis-payments',
                        ticks : {
                            beginAtZero : true,
                            stepSize : 1
                        },
                        gridLines : {
                            drawOnChartArea : false
                        }
                    }
                ]
            }
        }
    });

});