/**
 * Reportes / Vue App
 */
var Vue = require('vue');

jQuery(document).ready(function($) {
    $('form').on('submit', function(e) {
        e.preventDefault();
        app.saveReport();
    });

    $('select[name=filtros]').on('change', function(e) {
        app.definirFiltro($('select[name=filtros]').val());
    });
});

let app = new Vue({
    el: '#reporte-datos-builder',

    data: {
        nombre: '',
        descripcion: '',
        compartido: false,
        instituciones: [],
        institucionesDisponibles: window.siat.instituciones,
        filtros: [],
        campos: [],
        agrupar_campos: false,
        filtro: {
            campo: '',
            comparador: '',
            valor: ''
        }
    },

    ready() {
        if (item != null) {
            this.nombre = item.nombre;
            this.descripcion = item.descripcion;
            this.compartido = item.compartido;
            if (item.instituciones) {
                this.instituciones = item.instituciones;
            }
            else {
                this.instituciones = []
            }
            if (item.filtros == '') {
                this.filtros = [];
            }
            else {
                this.filtros = item.filtros;
            }
            this.agrupar_campos = item.agrupar_campos;
            this.campos = item.campos;
        }
    },

    computed: {
        comparadoresDisponibles() {
            if (this.filtro.campo == '') {
                return [];
            }

            let campos = {
                'departamento_id': 'string',
                'distrito_id': 'string',
                'localidad_id': 'string',
                'caratula': 'string',
                'expediente_judicial': 'string',
                'sumario_policial': 'string',
                'otro': 'string',
                'dependencia': 'string',
                'lugar': 'string',
                'tipo_camino': 'string',
                'altura': 'number',
                'codigo_postal': 'string',
                'heridos': 'number',
                'fallecidos': 'number',
                'ilesos': 'number',
                'sede_id': 'string',
                'participantes_siniestro': 'number'
            }

            let comparadores = {
                'string': [
                    {
                        'label': 'contiene a',
                        'value': 'contiene'
                    },
                    {
                        'value': 'no_contiene',
                        'label': 'no contiene a'
                    }
                ],
                'number': [
                    {
                        'label': 'es igual a',
                        'value': 'igual'
                    },
                    {
                        'label': 'no es igual a',
                        'value': 'no_es_igual'
                    },
                    {
                        'label': 'es mayor a',
                        'value': 'mayor'
                    },
                    {
                        'label': 'es mayor o igual a',
                        'value': 'mayor_o_igual'
                    },
                    {
                        'label': 'es menor a',
                        'value': 'menor'
                    },
                    {
                        'label': 'es menor o igual a',
                        'value': 'menor_o_igual'
                    }
                ]
            };

            if (typeof campos[this.filtro.campo] != 'undefined') {
                return comparadores[campos[this.filtro.campo]];
            }
            else {
                return [];
            }
        }
    },

    methods: {
        definirFiltro(filtro) {
            this.filtro.campo = filtro
        },
        validateReport() {
            // Validar nombre
            if (this.nombre.trim() == '') {
                swal({
                    title: 'Error!',
                    text: 'Por favor, ingrese un nombre para el reporte.',
                    type: 'warning',
                });
                return false;
            }

            if (this.filtros && this.filtros.length > 6) {
                swal({
                    title: 'Error!',
                    text: 'Sólo se puede definir hasta 6 filtros por reporte.',
                    type: 'warning',
                });
                return false;
            }

            // Validar campos
            if (this.campos.length == 0) {
                swal({
                    title: 'Error!',
                    text: 'Por favor, defina uno o más campos.',
                    type: 'warning',
                });
                return false;
            }

            if (this.agrupar_campos) {
                if (this.campos.length > 4) {
                    swal({
                        title: 'Error!',
                        text: 'Sólo se puede definir hasta 4 campos agrupados por reporte.',
                        type: 'warning',
                    });
                    return false;
                }
            }

            return true;
        },
        saveReport() {
            // Validar datos del reporte
            if (this.validateReport()) {
                // Data para el form
                let formData = this.$data;

                // Obtener token
                formData['_token'] = $('meta[name=_token]').attr('content');

                // Estamos editando?
                if (item != null) {
                    formData['_method'] = 'PATCH';
                }

                if (formData['agrupar_campos'] == undefined) {
                    formData['agrupar_campos'] = 0;
                }
                else {
                    formData['agrupar_campos'] = (formData['agrupar_campos'] == true) ? 1 : 0;
                }

                $.ajax({
                    data: formData,
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN' : $('meta[name=_token]').attr('content') },
                    method: 'POST',
                    url: $('form').attr('action'),
                    error: function() {
                        swal({
                            title: 'Error!',
                            text: 'Se produjo un error al guardar el reporte. Puede recargar la página e intentar de nuevo.',
                            type: 'warning',
                        });
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location = '/reporte_datos/' + response.payload.id;
                        }
                        else {
                            swal({
                                title: 'Error!',
                                text: response.message,
                                type: 'warning',
                            });
                        }
                    }
                });
            }
        },
        agregarInstitucion() {
            const institucionId = $('select[name=instituciones]').val();
            const institucionNombre = $('select[name=instituciones] option:selected').text();

            let match = _.findWhere(this.instituciones, {
                id: institucionId
            });

            if (typeof match == 'undefined') {
                if (institucionId == '') {
                    swal({
                        title: 'Error!',
                        text: 'Por favor, seleccione una institución.',
                        type: 'warning',
                    });
                    return false;
                }

                this.instituciones.push({
                    id: institucionId,
                    nombre: institucionNombre
                });
            }
            else {
                swal({
                    title: 'Error!',
                    text: institucionNombre + ' ya tiene acceso a este reporte.',
                    type: 'warning',
                });
            }
        },
        borrarInstitucion(institucion) {
            this.instituciones = _.filter(this.instituciones, function(_institucion) {
                if (_institucion.id == institucion.id) {
                    return false;
                }
                else {
                    return true;
                }
            }, institucion);
        },
        agregarFiltro() {
            if (this.filtros && this.filtros.length >= 6) {
                swal({
                    title: 'Error!',
                    text: 'Sólo se puede definir hasta 6 filtros por reporte.',
                    type: 'warning',
                });
                return false;
            }

            let filtro = null;
            let comparador = null;
            let valor = null;
            filtro = $('select[name=filtros]').val();
            comparador = $('select[name=comparadores]').val();
            valor = $('input[name=valores]').val();
            if (filtro == '' || comparador == '' || valor == '') {
                swal({
                    title: 'Error!',
                    text: 'Por favor, seleccione un campo, un comparador y un valor para agregar el filtro.',
                    type: 'warning',
                });
                return false;
            }

            let match = _.find(this.filtros, function(_filtro) {
                if (_filtro.filtro.valor == filtro
                    &&
                    _filtro.comparador.valor == comparador
                    &&
                    _filtro.valor == valor) {
                    return true;
                }
                else {
                    return false;
                }
            }, filtro, comparador, valor);

            if (typeof match == 'undefined') {
                this.filtros.push({
                    filtro: {
                        nombre: $('select[name=filtros] option:selected').text(),
                        valor: filtro
                    },
                    comparador: {
                        nombre: $('select[name=comparadores] option:selected').text(),
                        valor: comparador
                    },
                    valor: valor
                });
                $('input[name=valores]').val('');
            }
            else {
                swal({
                    title: 'Error!',
                    text: 'Ya existe un filtro definido con las mismas características.',
                    type: 'warning',
                });
            }

            this.filtro.campo = '';
            this.filtro.comparador = '';
            this.filtro.valor = '';
        },
        borrarFiltro(filtro) {
            this.filtros = _.filter(this.filtros, function(_filtro) {
                if (_filtro.filtro.nombre == filtro.filtro.nombre
                    &&
                    _filtro.comparador.nombre == filtro.comparador.nombre
                    &&
                    _filtro.valor == filtro.valor) {
                    return false;
                }
                else {
                    return true;
                }
            }, filtro);
        },
        agregarCampo() {
            if (this.agrupar_campos && this.campos.length >= 4) {
                swal({
                    title: 'Error!',
                    text: 'Sólo se puede definir hasta 4 campos agrupados por reporte.',
                    type: 'warning',
                });
                return false;
            }

            let campo = null;
            let nombre = null;
            let tipo = null;
            campo = $('select[name=campos]').val();
            nombre = $('select[name=campos] option:selected').text();
            tipo = $('select[name=campos] option:selected').data('tipo');
            if (campo == '') {
                swal({
                    title: 'Error!',
                    text: 'Por favor, seleccione un campo para agregar al informe.',
                    type: 'warning',
                });
                return false;
            }

            let match = _.findWhere(this.campos, {
                valor: campo
            });

            if (typeof match == 'undefined') {
                this.campos.push({
                    nombre: nombre,
                    valor: campo
                });
            }
            else {
                swal({
                    title: 'Error!',
                    text: nombre + ' ya está seleccionado.',
                    type: 'warning',
                });
            }
        },
        borrarCampo(campo) {
            this.campos = _.filter(this.campos, function(_campo) {
                if (_campo.nombre == campo.nombre
                    &&
                    _campo.valor == campo.valor) {
                    return false;
                }
                else {
                    return true;
                }
            }, campo);
        }
    }
})
