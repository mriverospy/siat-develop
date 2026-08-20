<template>
    <div>
        <div v-show="pendingData == true" class="mb0">
            <p class="text-center text-muted">
                <br />
                <i class="fa fa-warning fa-5x fa-fw"></i>
                <br />
                Seleccione Rango de Fecha para Visualizar los Datos
            </p>
        </div>
        <div v-show="loading == true" class="mb0">
            <p class="text-center text-muted">
                <br />
                <i class="fa fa-circle-o-notch fa-spin fa-5x fa-fw"></i>
                <br />
                Cargando...
            </p>
        </div>
        <div v-el:grafico width="100%" height="350"></div>
    </div>
</template>

<script>
    export default {
        props: [
            'reporte'
        ],

        data() {
            return {
                pendingData: true,
                loading: false
            }
        },

        events: {
            'fecha-reporte': function(fechas) {
                this.pendingData = false
                this.loading = true;
                this.getGraphData(fechas)
            }
        },

        methods: {
            getGraphData(fechas) {
                let context = this;
                $.ajax({
                    data: {
                        '_token': $('meta[name=_token]').attr('content'),
                        'reporte_id': this.reporte,
                        'fecha_inicio': fechas.fechaInicio,
                        'fecha_fin': fechas.fechaFin
                    },
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN' : $('meta[name=_token]').attr('content') },
                    method: 'POST',
                    url: siatGraphSummaryEndpoint,
                    error: function() {
                        //
                    },
                    success: function(response) {
                        context.renderGraph(response.payload);
                    }
                });
            },
            renderGraph(data) {
                this.loading = false;

                let heridosColor = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                let fallecidosColor = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                let ilesosColor = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
                let totalColor = '#' + (Math.random().toString(16) + '0000000').slice(2, 8);

                Highcharts.chart(this.$els.grafico, {
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: [
                                    'viewFullscreen',
                                    'printChart',
                                    'separator',
                                    'downloadPNG',
                                    'downloadJPEG',
                                    'downloadPDF',
                                    'downloadSVG',
                                    'separator',
                                    'downloadCSV',
                                    'downloadXLS'
                                ]
                            }
                        }
                    },
                    chart: {
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Sumario de Accidentes'
                    },
                    subtitle: {
                        text: 'Fuente: SIAT/ANTSV'
                    },
                    xAxis: [
                        {
                            categories: data.labels,
                            crosshair: true
                        }
                    ],
                    yAxis: [
                        { // Primary yAxis
                            labels: {
                                format: '{value:,.0f}',
                                style: {
                                    color: Highcharts.getOptions().colors[1]
                                }
                            },
                            title: {
                                text: 'Totales',
                                style: {
                                    color: Highcharts.getOptions().colors[1]
                                }
                            },
                            min: 0,
                            minRange : 0.1,
                            plotOptions: {
                                line: {
                                    softThreshold: false
                                }
                            }
                        }
                    ],
                    tooltip: {
                        shared: true
                    },
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        // x: 120,
                        verticalAlign: 'top',
                        // y: 50,
                        floating: false,
                        backgroundColor: '#ffffff'
                    },
                    series: [
                        {
                            name: 'Heridos',
                            type: 'line',
                            color: heridosColor,
                            data: data.total_heridos,
                            tooltip: { valueSuffix: '' }
                        },
                        {
                            name: 'Fallecidos',
                            type: 'line',
                            color: fallecidosColor,
                            data: data.total_fallecidos,
                            tooltip: { valueSuffix: '' }
                        },
                        {
                            name: 'Ilesos',
                            type: 'line',
                            color: ilesosColor,
                            data: data.total_ilesos,
                            tooltip: { valueSuffix: '' }
                        },
                        {
                            name: 'Total Participantes',
                            type: 'line',
                            color: totalColor,
                            data: data.total_participantes_siniestro,
                            tooltip: { valueSuffix: '' }
                        }
                    ]
                });
            }
        }
    }
</script>
