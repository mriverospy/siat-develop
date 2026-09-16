Vue.component('cabecera', {
    template: '#cabecera',

    data: function() {
        return {
            departamentos: window.departamentos,
            departamento_id: null,
            distritos: [],
            distrito_id: null,
            distrito_text: 'Este campo es requerido.',
            localidades: [],
            localidad_id: null,
            localidad_text: 'Este campo es requerido.'
        }
    },

    ready: function() {
        this.$nextTick(function() {
            this.mapaAccidente = window.SiatMapa.crear(this.$el);
            this.mapaAccidente.seleccionar(this.departamento_id, this.distrito_id);
        });

        var that = this;

        // Setup select 2
        if ($(this.$el).find('select.select2')) {
            $(this.$el).find('select.select2').select2();
        }

        // Setup bootstrap switch
        if ($(this.$el).find('.bootstrap-switch')) {
            $(this.$el).find('.bootstrap-switch').bootstrapSwitch();
        }

        // Setup date time picker
        if ($(this.$el).find('input.datetime-picker')) {
            $(this.$el).find('input.datetime-picker').datetimepicker({
                autoclose: true,
                format: 'yyyy-mm-dd hh:ii:00'
            });
        }

        // Setup icheck radios/checks
        if ($(this.$el).find('.icheck input')) {
            $(this.$el).find('.icheck input').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        }
    },

    watch: {
        departamento_id: function(currentValue) {
            if (this.mapaAccidente) this.mapaAccidente.seleccionar(currentValue, null);
            this.distrito_id = null;
            this.distritos = [];

            this.localidad_id = null;
            this.localidades = [];

            if (currentValue) {
                $.ajax({
                    method: 'GET',
                    url: '/accidentes/distritos/' + currentValue,
                    beforeSend: function() {
                        this.distrito_text = 'Cargando...';
                    }.bind(this),
                    error: function() {
                        this.distrito_text = 'Este campo es requerido.';
                    }.bind(this),
                    success: function(response) {
                        this.distrito_text = 'Este campo es requerido.';
                        if (String(currentValue) !== String(this.departamento_id)) return;
                        if (response.status == 'success') {
                            this.distritos = response.data.distritos;
                        }
                    }.bind(this)
                });
            }
        },
        distrito_id: function(currentValue) {
            if (this.mapaAccidente) this.mapaAccidente.seleccionar(this.departamento_id, currentValue);
            this.localidad_id = null;
            this.localidades = [];

            if (currentValue) {
                $.ajax({
                    method: 'GET',
                    url: '/accidentes/localidades/' + currentValue,
                    beforeSend: function() {
                        this.localidad_text = 'Cargando...';
                    }.bind(this),
                    error: function() {
                        this.localidad_text = 'Este campo es requerido.';
                    }.bind(this),
                    success: function(response) {
                        this.localidad_text = 'Este campo es requerido.';
                        if (String(currentValue) !== String(this.distrito_id)) return;
                        if (response.status == 'success') {
                            this.localidades = response.data.localidades;
                        }
                    }.bind(this)
                });
            }
        }
    }
});

Vue.component('agrupado', {
    template: '#agrupado',

    props: [
        'agrupado'
    ]
});

Vue.component('grupo', {
    template: '#grupo',

    props: [
        'seccion',
        'grupo'
    ],

    methods: {
        duplicarGrupo: function(grupoId, grupo) {
            this.$dispatch('duplicar-grupo', {
                seccion: this.seccion,
                grupoId: grupoId,
                grupo: grupo
            })
        },
        eliminarGrupo : function(grupoId, grupo){
            this.$dispatch('eliminar-grupo', {seccion: this.seccion, grupo: grupo});
        }
    }
});

Vue.component('text', {
    template: '#text',

    props: [
        'grupo',
        'question',
        'duplicable',
        'agrupador',
        'index'
    ]
});

Vue.component('number', {
    template: '#number',

    props: [
        'grupo',
        'question',
        'duplicable',
        'agrupador',
        'index'
    ]
});

Vue.component('radio', {
    template: '#radio',

    props: [
        'grupo',
        'question',
        'duplicable',
        'agrupador',
        'index'
    ],

    ready: function() {
        // Setup select 2
        if ($(this.$el).find('select.select2')) {
            $(this.$el).find('select.select2').select2({
                placeholder: 'Seleccionar...',
                allowClear: true
            });
        }

        // Setup icheck radios/checks
        if ($(this.$el).find('.icheck input')) {
            $(this.$el).find('.icheck input').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        }
    }
});

Vue.component('check', {
    template: '#check',

    props: [
        'grupo',
        'question',
        'duplicable',
        'agrupador',
        'index'
    ],

    ready: function() {
        // Setup icheck radios/checks
        if ($(this.$el).find('.icheck input')) {
            $(this.$el).find('.icheck input').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        }
    }
});

Vue.component('date', {
    template: '#date',

    props: [
        'grupo',
        'question',
        'duplicable',
        'agrupador',
        'index'
    ],

    ready: function() {
        // Setup date time picker
        if ($(this.$el).find('input.datepicker')) {
            $(this.$el).find('input.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                todayHighlight: true
            });
        }
    }
});

function form70Values() {
    var groups = {};
    $('#cargar-accidente').serializeArray().forEach(function(field) {
        var match = field.name.match(/^respuestas\[(\d+)\]\[(\d+)\]/);
        if (match) { groups[match[1]] = groups[match[1]] || {}; groups[match[1]][match[2]] = field.value.trim(); }
    });
    return groups;
}
function form70Applies(id, values, vehicles) {
    var rule = siat.reglas.condiciones[id] || {};
    if (rule.sin && values[rule.sin]) return false;
    if (rule.roles && rule.roles.indexOf(values[135]) < 0) return false;
    if (rule.pregunta && rule.valores.indexOf(values[rule.pregunta]) < 0) return false;
    var vehicle = vehicles[values[125]];
    if (values[135] === 'Ciclista' && values[125] === '0') vehicle = 'Bicicleta';
    return !rule.vehiculos || rule.vehiculos.indexOf(vehicle) >= 0;
}
function form70MarkInvalid(element, invalid) {
    element.toggleClass('has-error', invalid);
    element.find('input, select').attr('aria-invalid', invalid ? 'true' : 'false');
}
window.form70Refresh = function() {
    var groups = form70Values(), vehicles = {};
    Object.keys(groups).forEach(function(key) { if (groups[key][115]) vehicles[groups[key][115]] = groups[key][118]; });
    $('.form70-question').each(function() {
        var el = $(this), id = Number(el.attr('data-question')), values = groups[el.attr('data-group')] || {};
        var applies = form70Applies(id, values, vehicles);
        if (!applies) {
            el.find('input:not(:radio):not(:checkbox), select').val('');
            el.find('input:radio, input:checkbox').prop('checked', false).iCheck('update');
        }
        el.toggle(applies).find('input, select').prop('disabled', !applies);
        if (!applies || values[id]) form70MarkInvalid(el, false);
        el.find('.form70-required').toggle(applies && (siat.reglas.obligatorias.indexOf(id) >= 0 || (id === 131 && !values[130])));
    });
    $('#cargar-accidente .form-group.has-error').each(function() {
        var element = $(this);
        var empty = element.find('input, select').filter(function() {
            return !String($(this).val() || '').trim();
        }).length > 0;
        if (!empty && (!element.find('[name=ubicacion_confirmada]').length || $('[name=ubicacion_confirmada]').val() === '1')) {
            form70MarkInvalid(element, false);
        }
    });
};
window.form70Validate = function(section) {
    window.form70Refresh();
    if (section === undefined || section === 0) {
        var locationInvalid = $('[name=ubicacion_confirmada]').val() !== '1';
        var fields = {caratula:'Carátula',fecha_accidente:'Fecha y hora',lugar:'Ubicación del siniestro',tipo_camino:'Tipo de vía',area:'Área',departamento_id:'Departamento',distrito_id:'Distrito',localidad_id:'Localidad',gps_latitud:'Latitud',gps_longitud:'Longitud'};
        var headerError = locationInvalid ? 'Confirme la ubicación del siniestro en el mapa.' : null;
        for (var name in fields) {
            var field = $('[name="'+name+'"]');
            var invalid = !String(field.val() || '').trim();
            form70MarkInvalid(field.closest('.form-group'), invalid);
            if (invalid && !headerError) headerError = 'Complete '+fields[name]+'.';
        }
        if (locationInvalid) form70MarkInvalid($('[name=ubicacion_confirmada]').closest('.form-group'), true);
        if (headerError) return headerError;
    }
    var groups = form70Values(), error = null, vehicles = {};
    Object.keys(groups).forEach(function(key) {
        if (groups[key][115]) vehicles[groups[key][115]] = groups[key][118];
    });
    siat.secciones.forEach(function(seccion) {
        if (section !== undefined && section !== seccion.index) return;
        seccion.grupos.forEach(function(group) {
            var key = group.duplicable ? group.id * 1000000 + group.agrupador + 1 : group.id;
            var values = groups[key] || {};
            group.preguntas.forEach(function(q) {
                var element = $('.form70-question[data-group="'+key+'"][data-question="'+q.pregunta_id+'"]');
                if (element.find('input, select').first().prop('disabled')) return;
                var required = q.obligatorio || siat.reglas.obligatorias.indexOf(q.pregunta_id) >= 0 || (q.pregunta_id === 131 && !values[130]);
                var invalid = required && !values[q.pregunta_id];
                form70MarkInvalid(element, !!invalid);
                if (invalid && !error) error = group.grupo_nombre+': complete '+q.pregunta+'.';
                if (q.pregunta_id === 125 && values[125]) {
                    var withoutVehicle = values[125] === '0' && ['Peatón', 'Ciclista'].indexOf(values[135]) >= 0;
                    var invalidVehicle = !withoutVehicle && !Object.prototype.hasOwnProperty.call(vehicles, values[125]);
                    form70MarkInvalid(element, invalidVehicle);
                    if (invalidVehicle && !error) {
                        error = group.grupo_nombre + ' (persona ' + (group.agrupador + 1) + '): vehículo "' + values[125] +
                            '" inválido para el rol "' + (values[135] || '') + '". Vehículos registrados: ' +
                            (Object.keys(vehicles).join(', ') || 'ninguno') + '. Use un número registrado; 0 solo para Peatón o Ciclista.';
                    }
                }
            });
        });
    });
    return error;
};

var app = new Vue({
    el: 'body',

    data: {
        currentIndex: 0,
        secciones: siat.secciones,
        progressBar: {
            width: '0%'
        },
        alert: {
            type: 'info',
            message: ''
        },
        savingForm: false,
        showSuccess: false,
        canGoPrevious: false,
        canGoNext: true,
        errors: []
    },

    events: {
        'eliminar-grupo': function(msg) {
            var grupos = this.secciones[msg.seccion].grupos;
            if (grupos.filter(function(g) { return g.id === msg.grupo.id; }).length <= 1) return;
            grupos.splice(grupos.indexOf(msg.grupo), 1);
            this.$nextTick(window.form70Refresh);
        },
        'duplicar-grupo': function(msg) {
            if (typeof siat.secciones[msg.seccion] != 'undefined') {
                siat.secciones[msg.seccion].grupos.push({
                    agrupador: Math.max.apply(null, siat.secciones[msg.seccion].grupos.map(function(g) { return g.agrupador; })) + 1,
                    duplicable: msg.grupo.duplicable,
                    grupo_nombre: msg.grupo.grupo_nombre,
                    id: msg.grupo.id,
                    preguntas: msg.grupo.preguntas,
                    tipo: msg.grupo.tipo
                });
                this.$nextTick(window.form70Refresh);
            }
        }
    },

    ready: function() {
        $('div.loading').detach();

        $('#cargar-accidente').removeClass('hide');
        $('#cargar-accidente').on('input change ifChanged', 'input, select', window.form70Refresh);
        this.$nextTick(window.form70Refresh);

        Mousetrap.bind('left', function(e) {
            app.previousQuestion();
        });

        Mousetrap.bind('right', function(e) {
            app.nextQuestion();
        });

        Offline.on('down', function() {
            app.alert.type = 'danger';
            app.alert.message = 'Se perdió la conexión con el servidor, se intentará restablecer la conexión en unos segundos más.';
        });

        Offline.on('checking', function() {
            app.alert.type = 'warning';
            app.alert.message = 'Chequeando conexión con el servidor...';
        });

        Offline.on('reconnect:failure', function() {
            app.alert.type = 'warning';
            app.alert.message = 'Un intento de conexión con el servidor falló, se volverá a reintentar en unos segundos más.';
        });

        Offline.on('requests:flush', function() {
            app.alert.type = 'info';
            app.alert.message = 'Se restableció la conexión con el servidor, los datos se han guardado correctamente.';
            app.savingForm = false;
            app.showSuccess = true;
        });
    },

    watch: {
        currentIndex: function() {
            this.$nextTick(function() { $(document).trigger('siat:mapa-visible'); });
            var progressWidth = parseInt((this.currentIndex * 100) / this.secciones.length);
            this.progressBar.width = progressWidth + '%';

            this.canGoPrevious = (this.currentIndex > 0 && this.currentIndex <= (this.secciones.length - 1));
            this.canGoNext = (this.currentIndex >= 0 && this.currentIndex < (this.secciones.length - 1));
        },
    },

    computed: {
        currentView: function() {
            if (this.secciones[this.currentIndex] == undefined) {
                return null;
            }

            return this.secciones[this.currentIndex].tipo;
        },
        currentQuestion: function() {
            if (this.secciones[this.currentIndex] == undefined) {
                return null;
            }

            return this.secciones[this.currentIndex];
        },
        showForm: function() {
            return (this.savingForm == false && this.showSuccess == false);
        }
    },

    methods: {
        successSave: function() {
            this.currentIndex = -1;
            this.showSuccess = true;
        },

        previousQuestion: function() {
            if (this.currentIndex > 0) {
                this.currentIndex = this.currentIndex - 1;
                $('html, body').animate({ scrollTop: 0 }, 'fast');
            }
        },

        nextQuestion: function() {
            var error = window.form70Validate(this.currentIndex);
            if (error) { alert(error); return; }
            if (this.currentIndex < this.secciones.length - 1) this.currentIndex++;
        },

        saveForm: function() {
            var error = window.form70Validate();
            if (error) { alert(error); return false; }

            // Reset error messages
            this.errors = [];

            // Fire ajax request
            $.ajax({
                method: 'POST',
                url: '/accidentes/cargar',
                data: $('#cargar-accidente').serialize(),
                beforeSend: function() {
                    app.savingForm = true;
                },
                error: function() {
                    app.savingForm = false;
                },
                success: function(response) {
                    app.savingForm = false;
                    if (response.status == 'success') {
                        app.successSave();
                    }
                    else {
                        app.$set('errors', response.payload);
                    }
                }
            });
        }
    }
});
