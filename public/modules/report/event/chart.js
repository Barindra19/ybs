var ReportEvent = function() {

    return {
        initAmChart: function() {
            if (typeof(AmCharts) === 'undefined' || $('#report_event_chart').size() === 0) {
                return;
            }
            console.log(DATA);
            var chart = AmCharts.makeChart("report_event_chart", {
                "type": "serial",
                "addClassNames": true,
                "theme": "light",
                "path": BASE_URL + "/plugins/amcharts/ammap/images/",
                "autoMargins": false,
                "marginLeft": 100,
                "marginRight": 8,
                "marginTop": 10,
                "marginBottom": 26,
                "balloon": {
                    "adjustBorderColor": false,
                    "horizontalPadding": 10,
                    "verticalPadding": 8,
                    "color": "#ffffff"
                },
                "dataProvider": DATA,
                "valueAxes": [{
                    "axisAlpha": 0,
                    "position": "left"
                }],
                "startDuration": 1,
                "graphs": [{
                    "alphaField": "alpha",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "fillAlphas": 1,
                    "title": "Income",
                    "type": "column",
                    "valueField": "income",
                    "dashLengthField": "dashLengthColumn"
                }, {
                    "id": "graph2",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "bullet": "round",
                    "lineThickness": 3,
                    "bulletSize": 7,
                    "bulletBorderAlpha": 1,
                    "bulletColor": "#FFFFFF",
                    "useLineColorForBulletBorder": true,
                    "bulletBorderThickness": 3,
                    "fillAlphas": 0,
                    "lineAlpha": 1,
                    "title": "Expenses",
                    "valueField": "expenses"
                }],
                "categoryField": "date",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "tickLength": 0
                },
                "export": {
                    "enabled": true
                }
            });
        },

        init: function() {

            this.initAmChart();

        }
    };

}();

if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
        ReportEvent.init(); // init metronic core componets
    });
}
