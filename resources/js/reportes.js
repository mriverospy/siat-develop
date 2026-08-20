/**
 * Reportes / Vue App
 */
var Vue = require('vue');

jQuery(document).ready(function($) {
    $('#reporte-rango-fechas').daterangepicker({
        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Últimos 7 días': [moment().subtract('days', 6), moment()],
            'Últimos 30 días': [moment().subtract('days', 29), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
        },
        opens: 'right',
        startDate: moment().subtract('days', 29),
        endDate: moment(),
        locale: {
            applyLabel: 'Definir',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            weekLabel: 'S',
            customRangeLabel: 'Definir Rango',
            daysOfWeek: moment()._lang._weekdaysMin.slice(),
            monthNames: moment()._lang._monthsShort.slice(),
            firstDay: 0
        }
    }, function(start, end) {
        app.$set('fecha_inicio', start.format('YYYY-MM-DD'))
        app.$set('fecha_fin', end.format('YYYY-MM-DD'));
        $('#reporte-rango-fechas span').html(start.format('DD-MM-YYYY') + ' &mdash; ' + end.format('DD-MM-YYYY'));
    });

    $('#ejecutar-reporte').on('click', function(e) {
        e.preventDefault();
        app.cargarReporte();
    })
});

import Graph from './components/Graph.vue';
import Summary from './components/Summary.vue';

let app = new Vue({
    el: '#reportes',

    data: {
        fecha_inicio: null,
        fecha_fin: null
    },

    components: {
        Graph,
        Summary
    },

    methods: {
        cargarReporte() {
            this.$broadcast('fecha-reporte', { fechaInicio: this.fecha_inicio, fechaFin: this.fecha_fin })
        }
    }
});
