Vue.component('text', {
    template: '#text',

    data: function() {
        return {
            valorRespuesta: ''
        }
    },

    props: [
        'pregunta',
        'respuesta'
    ],

    ready: function() {
        if (this.respuesta != null) {
            this.valorRespuesta = this.respuesta.respuestas;
        }
    },

    methods: {
        guardar: function() {
            this.$dispatch('guardar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta,
                valorRespuesta: this.valorRespuesta
            });
        },
        borrar: function() {
            this.$dispatch('borrar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta
            });
        }
    }
});

Vue.component('number', {
    template: '#number',

    data: function() {
        return {
            valorRespuesta: ''
        }
    },

    props: [
        'pregunta',
        'respuesta'
    ],

    ready: function() {
        if (this.respuesta != null) {
            this.valorRespuesta = this.respuesta.respuestas;
        }
    },

    methods: {
        guardar: function() {
            this.$dispatch('guardar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta,
                valorRespuesta: this.valorRespuesta
            });
        },
        borrar: function() {
            this.$dispatch('borrar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta
            });
        }
    }
});

Vue.component('radio', {
    template: '#radio',

    data: function() {
        return {
            valorRespuesta: ''
        }
    },

    props: [
        'pregunta',
        'respuesta'
    ],

    ready: function() {
        if (this.respuesta != null) {
            this.valorRespuesta = this.respuesta.respuestas;
        }
    },

    methods: {
        guardar: function() {
            this.$dispatch('guardar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta,
                valorRespuesta: this.valorRespuesta
            });
        },
        borrar: function() {
            this.$dispatch('borrar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta
            });
        }
    }
});

Vue.component('check', {
    template: '#check',

    data: function() {
        return {
            valorRespuesta: []
        }
    },

    props: [
        'pregunta',
        'respuesta'
    ],

    ready: function() {
        if (this.respuesta != null) {
            this.valorRespuesta = this.respuesta.respuestas;
        }
    },

    methods: {
        guardar: function() {
            this.$dispatch('guardar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta,
                valorRespuesta: this.valorRespuesta
            });
        },
        borrar: function() {
            this.$dispatch('borrar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta
            });
        }
    }
});

Vue.component('date', {
    template: '#date',

    data: function() {
        return {
            valorRespuesta: ''
        }
    },

    props: [
        'pregunta',
        'respuesta'
    ],

    ready: function() {
        if (this.respuesta != null) {
            this.valorRespuesta = this.respuesta.respuestas;
        }

        // Setup date time picker
        if ($(this.$el).find('input.datepicker')) {
            $(this.$el).find('input.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                todayHighlight: true
            });
        }
    },

    methods: {
        guardar: function() {
            this.$dispatch('guardar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta,
                valorRespuesta: this.valorRespuesta
            });
        },
        borrar: function() {
            this.$dispatch('borrar-respuesta', {
                pregunta: this.pregunta,
                respuesta: this.respuesta
            });
        }
    }
});

var app = new Vue({
    el: '#editar-accidente',

    data: {
        modoEdicion: 'bloque',
        agrupadoresAccidente: window.edicionAccidente.agrupadoresAccidente,
        grupoPreguntas: window.edicionAccidente.grupoPreguntas,
        respuestasAccidente: window.edicionAccidente.respuestasAccidente,
        grupoPreguntaSeleccionado: {},
        agrupadorSeleccionado: {},
        preguntaSeleccionada: {},
        respuestaSeleccionada: null,
        setEdiciones: []
    },

    events: {
        'guardar-respuesta': function(msg) {
            // define modo de edición
            var modoEdicion = (msg.respuesta == null) ? 'agregar' : 'modificar';

            // define agrupador
            var agrupador = 1;
            if (msg.pregunta.grupo_duplicable) {
                if (modoEdicion == 'agregar') {
                    // usar agrupador nuevo
                    agrupador = this.agrupadorSeleccionado.agrupador;
                }
                else {
                    agrupador = msg.respuesta.agrupador;
                }
            }

            // define respuesta anterior
            var respuestaAnterior = 'Sin Definir';
            if (modoEdicion == 'modificar') {
                respuestaAnterior = msg.respuesta.respuestas
            }

            // define respuesta nueva
            var respuestaNueva = msg.valorRespuesta;

            // Conservar un único cambio pendiente por pregunta y persona/vehículo.
            this.setEdiciones = this.setEdiciones.filter(function(edicion) {
                return String(edicion.preguntaId) !== String(msg.pregunta.pregunta_id) || String(edicion.agrupador) !== String(agrupador);
            });
            this.setEdiciones.push({
                modoEdicion: modoEdicion,
                preguntaId: msg.pregunta.pregunta_id,
                pregunta: msg.pregunta.pregunta,
                tipoPregunta: msg.pregunta.tipo,
                agrupador: agrupador,
                respuestaAnterior: respuestaAnterior,
                respuestaNueva: respuestaNueva
            });

            // resetear pregunta y respuesta seleccionada
            this.preguntaSeleccionada = {};
            this.respuestaSeleccionada = null;
        },
        'borrar-respuesta': function(msg) {
            this.setEdiciones = this.setEdiciones.filter(function(edicion) {
                return String(edicion.preguntaId) !== String(msg.pregunta.pregunta_id) || String(edicion.agrupador) !== String(msg.respuesta.agrupador);
            });
            this.setEdiciones.push({
                modoEdicion: 'borrar',
                preguntaId: msg.pregunta.pregunta_id,
                pregunta: msg.pregunta.pregunta,
                tipoPregunta: msg.pregunta.tipo,
                agrupador: msg.respuesta.agrupador,
                respuestaAnterior: msg.respuesta.respuestas,
                respuestaNueva: ''
            });

            // resetear pregunta y respuesta seleccionada
            this.preguntaSeleccionada = {};
            this.respuestaSeleccionada = null;
        }
    },

    ready: function() {},

    watch: {},

    computed: {
        mostrarPregunta: function() {
            if ($.isEmptyObject(this.grupoPreguntaSeleccionado)) {
                return false;
            }
            else {
                if (this.grupoPreguntaSeleccionado.duplicable) {
                    if ($.isEmptyObject(this.agrupadorSeleccionado)) {
                        return false;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return true;
                }
            }
        },
        agrupadores: function() {
            if (this.grupoPreguntaSeleccionado && this.grupoPreguntaSeleccionado.duplicable) {
                var agrupadores = [];
                var that = this;
                $.each(this.agrupadoresAccidente, function(idGrupo, agrupadoresAccidente) {
                    if (idGrupo == that.grupoPreguntaSeleccionado.id) {
                        agrupadores = agrupadoresAccidente;
                    }
                })
                return agrupadores;
            }
            else {
                return [];
            }
        }
    },

    methods: {
        editarBloque: function() {
            this.modoEdicion = 'bloque';
        },
        editarRespuestas: function() {
            this.modoEdicion = 'respuestas';
        },
        nuevoAgrupador: function() {
            if (!this.grupoPreguntaSeleccionado.duplicable) return;
            var grupoId = this.grupoPreguntaSeleccionado.id;
            if (!this.agrupadoresAccidente[grupoId]) {
                this.$set('agrupadoresAccidente.' + grupoId, []);
            }
            var agrupadorNuevo = 0;

            var indice = 0;

            var that = this;

            $.each(this.agrupadores, function(index, agrupador) {
                indice = indice + 1;
                if (agrupador.agrupador > agrupadorNuevo) {
                    agrupadorNuevo = agrupador.agrupador
                }
            })

            indice = indice + 1;
            agrupadorNuevo = agrupadorNuevo + 1;

            var nuevo = {
                agrupador: agrupadorNuevo,
                titulo: this.grupoPreguntaSeleccionado.grupo_nombre + ' - ' + indice
            };
            this.agrupadoresAccidente[grupoId].push(nuevo);
            this.seleccionarAgrupador(nuevo);
        },
        seleccionarGrupoPreguntas: function(grupoPregunta) {
            this.grupoPreguntaSeleccionado = grupoPregunta;
            this.agrupadorSeleccionado = {};
            this.preguntaSeleccionada = {};
            this.respuestaSeleccionada = null;
        },
        seleccionarAgrupador: function(agrupador) {
            this.agrupadorSeleccionado = agrupador;
            this.preguntaSeleccionada = {};
            this.respuestaSeleccionada = null;
        },
        seleccionarPregunta: function(pregunta) {
            this.preguntaSeleccionada = pregunta;
            var respuestaSeleccionada = null;
            var that = this;
            if (this.grupoPreguntaSeleccionado.duplicable) {
                // si es duplicable, buscar respuesta usando agrupador
                $.each(this.respuestasAccidente, function(index, respuesta) {
                    if (respuesta.pregunta_id == that.preguntaSeleccionada.pregunta_id && respuesta.agrupador == that.agrupadorSeleccionado.agrupador) {
                        respuestaSeleccionada = respuesta;
                    }
                })
            }
            else {
                // si no es duplicable, buscar respuesta usando pregunta id
                $.each(this.respuestasAccidente, function(index, respuesta) {
                    if (respuesta.pregunta_id == that.preguntaSeleccionada.pregunta_id) {
                        respuestaSeleccionada = respuesta;
                    }
                })
            }
            this.respuestaSeleccionada = respuestaSeleccionada;
        },
        borrarEdicion: function(edicion) {
            this.setEdiciones.splice(this.setEdiciones.indexOf(edicion), 1);
        },
        guardarCambios: function() {
            // Data para el form
            var formData = {
                'accidente': {
                    'caratula': $('input[name=caratula]').val(),
                    'expediente_judicial': $('input[name=expediente_judicial]').val(),
                    'sumario_policial': $('input[name=sumario_policial]').val(),
                    'otro': $('input[name=otro]').val(),
                    'dependencia': $('input[name=dependencia]').val(),
                    'fecha_siniestro': $('input[name=fecha_siniestro]').val(),
                    'hora_siniestro': $('input[name=hora_siniestro]').val(),
                    'lugar': $('input[name=lugar]').val(),
                    'altura': $('input[name=altura]').val(),
                    'tipo_camino': $('[name=tipo_camino]').val(),
                    'area': $('[name=area]').val(),
                    'localidad_id': $('select[name=localidad_id]').val(),
                    'codigo_postal': $('input[name=codigo_postal]').val(),
                    'gps_latitud': $('input[name=gps_latitud]').val(),
                    'gps_longitud': $('input[name=gps_longitud]').val(),
                    'heridos': $('input[name=heridos]').val(),
                    'fallecidos': $('input[name=fallecidos]').val(),
                    'ilesos': $('input[name=ilesos]').val(),
                    'participantes_siniestro': $('input[name=participantes_siniestro]').val()
                },
                'ediciones': this.$data.setEdiciones
            }

            // Obtener token
            formData['_token'] = $('meta[name=_token]').attr('content');

            $.ajax({
                data: formData,
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN' : $('meta[name=_token]').attr('content') },
                method: 'POST',
                url: $('form').attr('action'),
                error: function() {
                    swal({
                        title: 'Error!',
                        text: 'Se produjo un error al guardar los cambios. Puede recargar la página e intentar de nuevo.',
                        type: 'warning',
                    });
                },
                success: function(response) {
                    if (response.status == 'success') {
                        window.location = '/accidentes/' + response.payload.id;
                    }
                    else {
                        swal({
                            title: 'Error!',
                            text: response.message,
                            type: 'warning'
                        });
                    }
                }
            });
        }
    },
});
