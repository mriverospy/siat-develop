@extends('layouts.base_layout')

@section('page-title', 'Importar Datos')

@section('layout_content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Formato de Archivo de Datos</h2>
            </div>
            <div class="panel-body">
                <p>Para poder importar datos de accidentes al SIAT, es necesario bajar el formato del archivo de datos e ingresar los datos en ese archivo respetando el formato definido en él.</p>
                <p>Para bajar el formato de archivo de datos que le corresponde a su institución <strong>({{ $currentUser->sede->institucion->nombre }})</strong>, por favor, haga click en el botón <strong>Bajar Formato de Archivo de Datos</strong> disponible abajo:</p>
                <p class="text-center">
                    <a class="btn btn-default btn-md" href="{{ route('accidentes.bajar-formato-archivo') }}"><i class="fa fa-download fa-fw"></i>Bajar Formato de Archivo de Datos</a>
                    <a class="btn btn-default btn-md" href="{{ route('accidentes.bajar-diccionario-datos') }}"><i class="fa fa-info fa-fw"></i>Bajar Diccionario de Datos</a>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h2>Cargar Archivo de Datos</h2>
            </div>
            <div class="panel-body">
                {!! Form::open(['route' => array('accidentes.importar-archivo-datos'), 'files' => true]) !!}
                    <div class="form-horizontal">
                        <div class="form-group">
                            {!! Form::label('file', 'Seleccione Archivo de Datos', ['class' => 'control-label']) !!}
                            {!! Form::file('file') !!}
                        </div>
                    </div>

                    <div class="panel-footer">
                        <div class="row">
                            <div class="pull-right">
                                {!! Form::submit('Cargar Archivo de Datos', ['class' => 'btn btn-primary']) !!}
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h2>Cómo uso el Formato de Archivo de Datos y cargo mis datos usando Microsoft Excel?</h2>
            </div>
            <div class="panel-body">
                <p>Una vez que bajó el Formato de Archivo de Datos a su computadora, debe importar este formato a Microsoft Excel para empezar a cargar sus datos.</p>
                <p>Hay dos formas de importar el Formato de Archivo de Datos a Microsoft Excel: puede abrir el archivo en Excel o puede importarlo como rango de datos externos.</p>
                <h3>Importar el Formato de Archivo de Datos abriendo el archivo en Excel</h3>
                <p>El Formato de Archivo de Datos se puede abrir en un libro de Excel con el comando Abrir.</p>
                <p>
                    <ol>
                        <li>Haga clic en el <strong>botón de Microsoft Office</strong> (<img src="{{ asset('img/help/excel-start-button.gif') }}" alt="Botón de Microsoft Office" />) y, a continuación, en <strong>Abrir</strong>.</li>
                        <li>
                            Aparece el cuadro de diálogo <strong>Abrir</strong>.
                            <ul>
                                <li>
                                    <strong>En los equipos que estén ejecutando Windows Vista</strong>
                                    <ul>
                                        <li>En la lista, seleccione archivos de texto.</li>
                                        <li>En la barra de dirección, busque y haga doble clic en el archivo de Formato de Archivo de Datos.</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>En los equipos que estén ejecutando Microsoft Windows XP</strong>
                                    <ul>
                                        <li>En la lista Tipo de archivo, seleccione <strong>Archivos de texto</strong>.</li>
                                        <li>En la lista Buscar en, busque y haga doble clic en el archivo de Formato de Archivo de Datos.</li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>Excel abrirá automáticamente el Formato de Archivo de Datos y mostrará los datos en un libro nuevo.</li>
                    </ol>
                </p>
                <p>Recuerde que las <em>columnas corresponden a cada pregunta</em> y que, de ésta forma, <em>cada accidente ocupará una fila en su archivo de datos</em>.</p>
                <h3>Importar el Formato de Archivo de Datos como rango de datos externos en Excel</h3>
                <p>
                    <ol>
                        <li>Inicie Microsoft Excel en su computadora (por defecto, Excel se abrirá con un documento en blanco por defecto).</li>
                        <li>Haga clic en la celda en la que desea poner los datos del archivo de texto.</li>
                        <li>En la pestaña <strong>Datos</strong>, en el grupo <strong>Obtener datos externos</strong>, haga clic en <strong>Desde texto</strong>.<br/><img src="{{ asset('img/help/excel-obtener-datos.gif') }}" alt="Obtener datos externos en Microsoft Excel" /></li>
                        <li>
                            <strong>En los equipos que estén ejecutando Windows Vista</strong>
                            <ul>
                                <li>En la lista barra de <strong>Dirección</strong>, busque y haga doble clic en el archivo de Formato de Archivo de Datos.</li>
                            </ul>
                        </li>
                        <li>
                            <strong>En los equipos que estén ejecutando Microsoft Windows XP</strong>
                            <ul>
                                <li>
                                    En la lista <strong>Buscar en</strong>, busque y haga doble clic en el archivo de Formato de Archivo de Datos.
                                    <br/>
                                    Siga las instrucciones del Asistente para importar texto. Haga clic en Ayuda para obtener más información sobre el uso del Asistente para importar texto o vea Asistente para importar texto. Cuando termine de realizar los pasos del Asistente, haga clic en <strong>Finalizar</strong> para completar la operación de importar.
                                </li>
                            </ul>
                        </li>
                        <li>
                            En el cuadro de diálogo <strong>Importar datos</strong>, haga lo siguiente:
                            <ul>
                                <li>Si lo desea, haga clic en <strong>Propiedades</strong> para definir las opciones de actualización, formato y diseño de los datos importados.</li>
                                <li>
                                    En <strong>¿Dónde desea situar los datos?</strong>, siga uno de los procedimientos siguientes:
                                    <ul>
                                        <li>Para devolver los datos a la ubicación seleccionada, haga clic en <strong>Hoja de cálculo existente</strong>.</li>
                                        <li>Para devolver los datos a la esquina superior izquierda de una nueva hoja de cálculo, haga clic en <strong>Hoja de cálculo nueva</strong>.</li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>Haga clic en <strong>Aceptar</strong>.</li>
                    </ol>
                </p>
                <p>El Formato de Archivo de Datos ya debe estar disponible en Excel para empezar a cargar los datos. Recuerde que puede guardar sus datos en el formato que desee (Excel, CSV, etc.), no existen restricciones.</p>
                <p>Cuando termine de cargar los datos en Excel, siga el procedimiento disponible en esta página (Cómo preparo mis datos en Microsoft Excel para importarlos al SIAT?) para importar sus datos al SIAT.</p>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h2>Cómo preparo mis datos en Microsoft Excel para importarlos al SIAT?</h2>
            </div>
            <div class="panel-body">
                <p>Una vez finalizado la carga de sus datos de accidentes en Excel, debe preparar sus datos para importarlos al SIAT. El proceso requiere simplemente exportar sus datos (en Excel) a CSV.</p>
                <p>Hay dos formas de importar datos de un archivo de texto en Microsoft Excel: puede abrir el archivo de texto en Excel o puede importarlo como rango de datos externos.</p>
                <p>Para bajar el archivo de datos que le corresponde a su institución ({{ $currentUser->sede->institucion->nombre }}), por favor, haga click en el botón Bajar Archivo de Datos disponible abajo:</p>
                <p>
                    <div class="alert alert-info">
                        Por favor, recuerde que Excel tiene límites al momento de exportar datos, los límites actuales son hasta <strong>1.048.576 filas</strong> y <strong>16.384 columnas</strong>.
                        <br />
                        Si supera estos límites, recuerde que puede dividir/separar el archivo en archivos más pequeños para superar esta limitación.
                    </div>
                </p>
                <p>
                    <ol>
                        <li>Haga clic en el <strong>botón de Microsoft Office</strong> (<img src="{{ asset('img/help/excel-start-button.gif') }}" alt="Botón de Microsoft Office" />) y, después, haga click en <strong>Guardar como</strong>. Se abrirá el cuadro de diálogo <strong>Guardar como</strong>.</li>
                        <li>En el cuadro <strong>Guardar como tipo</strong>, elija <strong>CSV (delimitado por comas)</strong> como el formato de archivo de texto para la hoja de cálculo.</li>
                        <li>
                            <strong>En los equipos que estén ejecutando Windows Vista</strong>
                            <ul>
                                <li>En la <strong>barra de dirección</strong>, examine la ubicación donde desea guardar el nuevo archivo de texto y, a continuación, haga clic en <strong>Guardar</strong>.</li>
                            </ul>
                        </li>
                        <li>
                            <strong>En los equipos que estén ejecutando Microsoft Windows XP</strong>
                            <ul>
                                <li>En el cuadro <strong>Guardar en</strong>, examine la ubicación donde desea guardar el nuevo archivo de texto y, a continuación, haga clic en <strong>Guardar</strong>.</li>
                            </ul>
                        </li>
                        <li>Aparecerá un cuadro de diálogo en el que se informa de que sólo la hoja de cálculo actual se va a guardar en el nuevo archivo, haga clic en <strong>Aceptar</strong>. Puede guardar otras hojas de cálculo como archivos de texto distintos repitiendo este procedimiento para cada hoja.</li>
                        <li>Aparecerá un segundo cuadro de diálogo en el que se indica que la hoja de cálculo puede contener características no compatibles con los formatos de archivo de texto, <strong>haga clic en Sí</strong>.</li>
                        <li>Ahora ya puede cargar su archivo de datos en la sección de <strong>Cargar Archivo de Datos</strong> más arriba.</li>
                    </ol>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
