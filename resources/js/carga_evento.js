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
                        if (response.status == 'success') {
                            this.distritos = response.data.distritos;
                        }
                    }.bind(this)
                });
            }
        },
        distrito_id: function(currentValue) {
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
            this.$remove(this.seccion);
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
        'duplicar-grupo': function(msg) {
            if (typeof siat.secciones[msg.seccion] != 'undefined') {
                siat.secciones[msg.seccion].grupos.push({
                    agrupador: siat.secciones[msg.seccion].grupos.length + 1,
                    duplicable: msg.grupo.duplicable,
                    grupo_nombre: msg.grupo.grupo_nombre,
                    id: msg.grupo.id,
                    preguntas: msg.grupo.preguntas,
                    tipo: msg.grupo.tipo
                });
            }
        }
    },

    ready: function() {
        $('div.loading').detach();

        $('#cargar-accidente').removeClass('hide');

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
            if (this.currentIndex >= 0 && this.currentIndex <= this.secciones.length) {
                let avanzar = true;
                if(this.currentIndex > 0){
                    this.secciones[this.currentIndex].grupos[0].preguntas.forEach(pregunta => {
                        if(pregunta.obligatorio){
                            let grupo = pregunta.grupo_id;
                            if(pregunta.grupo_duplicable){
                               grupo = (grupo * 10) + this.secciones[this.currentIndex].grupos[0].agrupador + 1;
                            }
                            let preguntaID = pregunta.pregunta_id;
                            let name = `respuestas[${grupo}][${preguntaID}]`;
                            if(["radio","check"].includes(pregunta.tipo)){
                                name = `respuestas[${grupo}][${preguntaID}][]`;
                                if(pregunta.mostrar_selector){
                                    let select = document.getElementsByName(name)[0].value;
                                    if(select == null || select === ''){
                                        alert('Por favor, ' + pregunta.instrucciones);
                                        avanzar = false;
                                    }
                                }else{
                                    let opciones = document.getElementsByName(name);
                                    let seleccionada = false;
                                    for(let opcion of opciones){
                                        if(opcion.checked){
                                            seleccionada = true;
                                            break;
                                        }
                                    }
                                    if(!seleccionada){
                                        alert('Por favor, ' + pregunta.instrucciones);
                                        avanzar = false;
                                    }
                                }
                            }else{
                                let input = document.getElementsByName(name)[0].value;
                                if(input == null || input === ''){
                                    alert('Por favor, ' + pregunta.instrucciones);
                                    avanzar = false;
                                }
                            }
                        }
                    });
                }

                if(avanzar){
                    this.currentIndex = this.currentIndex + 1;
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                }
            }
        },

        saveForm: function() {
            if ($('input[name=caratula]').val() == '') {
                alert('Por favor, ingrese una Carátula para este accidente');
                this.currentIndex = 0;
                return false;
            }

            if ($('input[name=fecha_accidente]').val() == '') {
                alert('Por favor, defina la fecha de este accidente');
                this.currentIndex = 0;
                return false;
            }

            if ($('select[name=localidad_id]').val() == null || $('select[name=localidad_id]').val() == '') {
                alert('Por favor, selecciona la localidad donde se produjo el accidente');
                this.currentIndex = 0;
                return false;
            }

            if ($('input[name=gps_latitud]').val() == '' || $('input[name=gps_longitud]').val() == '') {
                alert('Por favor, especifique el lugar donde ocurrió el accidente en el mapa');
                this.currentIndex = 0;
                return false;
            }

            for (let i = 1; i < this.secciones.length; i++){
                for(let pregunta of this.secciones[i].grupos[0].preguntas){
                    if(pregunta.obligatorio){
                        let grupo = pregunta.grupo_id;
                        if(pregunta.grupo_duplicable){
                            grupo = (grupo * 10) + this.secciones[i].grupos[0].agrupador + 1;
                        }
                        let preguntaID = pregunta.pregunta_id;
                        let name = `respuestas[${grupo}][${preguntaID}]`;
                        if(["radio","check"].includes(pregunta.tipo)){
                            name = `respuestas[${grupo}][${preguntaID}][]`;
                            if(pregunta.mostrar_selector){
                                let select = document.getElementsByName(name)[0].value;
                                if(select == null || select === ''){
                                    alert('Tiene preguntas sin responder');
                                    return  false;
                                }
                            }else{
                                let opciones = document.getElementsByName(name);
                                let seleccionada = false;
                                for(let opcion of opciones){
                                    if(opcion.checked){
                                        seleccionada = true;
                                        break;
                                    }
                                }
                                if(!seleccionada){
                                    alert('Tiene preguntas sin responder');
                                    return false;
                                }
                            }
                        }else{
                            let input = document.getElementsByName(name)[0].value;
                            if(input == null || input === ''){
                                alert('Tiene preguntas sin responder');
                                return false;
                            }
                        }
                    }
                }
            }


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
