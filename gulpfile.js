var elixir = require('laravel-elixir');

require('laravel-elixir-vueify');

elixir.config.js.browserify.watchify = {
    enabled: true,
    options: {
        poll: true
    }
}

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.scripts(['./resources/js/mapa_accidente.js'], './public/js/mapa_accidente.js');
    mix.browserify('reportes.js');
    mix.browserify('reportes-builder.js');
    mix.browserify('reporte-datos-builder.js');

    mix.less('styles.less');
    mix.less('carga_evento.less');

    mix.scripts([
        '../plugins/sparklines/jquery.sparklines.min.js',
        '../plugins/jstree/dist/jstree.min.js',
        '../plugins/codeprettifier/prettify.js',
        '../plugins/bootstrap-switch/bootstrap-switch.js',
        '../plugins/bootstrap-tabdrop/js/bootstrap-tabdrop.js',
        '../plugins/iCheck/icheck.min.js',
        '../plugins/bootstrap-timepicker/bootstrap-timepicker.js',
        '../plugins/bootstrap-datepicker/bootstrap-datepicker.js',
        '../plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.js',
        '../plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
        '../plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.es.js',
        'enquire.min.js',
        '../plugins/bootbox/bootbox.js',
        '../plugins/simpleWeather/jquery.simpleWeather.min.js',
        '../plugins/wijets/wijets.js',
        '../plugins/form-daterangepicker/daterangepicker.js',
        '../plugins/form-daterangepicker/moment.min.js',
        '../plugins/form-nestable/jquery.nestable.min.js',
        '../plugins/form-nestable/ui-nestable.js',
        'application.js',
        '../plugins/form-select2/select2.min.js',
        '../plugins/form-multiselect/js/jquery.multi-select.min.js'
        ]);

    mix.scripts([
        './resources/plugins/bootstrap-switch/bootstrap-switch.js',
        './resources/plugins/iCheck/icheck.min.js',
        './resources/plugins/form-select2/select2.min.js',
        './resources/plugins/bootstrap-timepicker/bootstrap-timepicker.js',
        './resources/plugins/bootstrap-datepicker/bootstrap-datepicker.js',
        './resources/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.js',
        './resources/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
        './resources/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.es.js',
        './resources/js/carga_evento.js'
        ],
        './public/js/carga_evento.js');

    mix.scripts([
        '../plugins/bootstrap-switch/bootstrap-switch.js',
        '../plugins/iCheck/icheck.min.js',
        '../plugins/form-select2/select2.min.js',
        '../plugins/bootstrap-timepicker/bootstrap-timepicker.js',
        '../plugins/bootstrap-datepicker/bootstrap-datepicker.js',
        '../plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.js',
        '../plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
        '../plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.es.js',
        'editar_accidente.js'
        ],
        './public/js/editar_accidente.js');

    mix.copy('resources/assets/plugins/form-select2/select2-spinner.gif', 'public/css/select2-spinner.gif')
       .copy('resources/assets/plugins/form-select2/select2.png', 'public/css/select2.png')
       .copy('resources/assets/plugins/form-select2/select2x2.png', 'public/css/select2x2.png')
       .copy('resources/assets/plugins/iCheck/skins/minimal/*.png', 'public/css')
       .copy('resources/assets/plugins/form-multiselect/img/switch.png', 'public/img/switch.png');
});
