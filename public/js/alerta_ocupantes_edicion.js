(function () {
    var alerta = document.getElementById('alerta-ocupantes-edicion');
    if (!alerta || !window.app || !window.edicionAccidente) return;

    var codigos = {};
    Object.keys(edicionAccidente.grupoPreguntas).forEach(function (grupo) {
        edicionAccidente.grupoPreguntas[grupo].preguntas.forEach(function (pregunta) {
            if (['V_1', 'VPY_01', 'P_1'].indexOf(pregunta.codigo) !== -1) codigos[pregunta.pregunta_id] = pregunta.codigo;
        });
    });
    var encontrados = Object.keys(codigos).map(function (id) { return codigos[id]; });
    if (['V_1', 'VPY_01', 'P_1'].some(function (codigo) { return encontrados.indexOf(codigo) === -1; })) return;

    function numero(valor) {
        if (Array.isArray(valor)) valor = valor.length === 1 ? valor[0] : '';
        valor = String(valor == null ? '' : valor).trim();
        return /^\d+(?:\.0+)?$/.test(valor) ? String(Number(valor)) : '';
    }

    function actualizar() {
        var grupos = {}, mensajes = [], vehiculos = {}, personas = {};
        function aplicar(id, agrupador, valor) {
            var codigo = codigos[id];
            if (!codigo) return;
            var clave = (codigo === 'P_1' ? 'persona:' : 'vehiculo:') + agrupador;
            grupos[clave] = grupos[clave] || {};
            grupos[clave][codigo] = numero(valor);
        }
        Object.keys(edicionAccidente.respuestasAccidente).forEach(function (clave) {
            var respuesta = edicionAccidente.respuestasAccidente[clave];
            aplicar(respuesta.pregunta_id, respuesta.agrupador, respuesta.respuestas);
        });
        app.setEdiciones.forEach(function (edicion) {
            aplicar(edicion.preguntaId, edicion.agrupador, edicion.modoEdicion === 'borrar' ? '' : edicion.respuestaNueva);
        });
        Object.keys(grupos).forEach(function (clave) {
            var grupo = grupos[clave];
            if (clave.indexOf('vehiculo:') === 0) {
                if (!grupo.V_1 || grupo.V_1 === '0') mensajes.push('Indique un número válido para cada vehículo.');
                else if (grupo.VPY_01 === undefined || grupo.VPY_01 === '') mensajes.push('Vehículo ' + grupo.V_1 + ': indique un número válido de ocupantes.');
                else if (Object.prototype.hasOwnProperty.call(vehiculos, grupo.V_1)) mensajes.push('El número de vehículo ' + grupo.V_1 + ' está repetido.');
                else vehiculos[grupo.V_1] = Number(grupo.VPY_01);
            }
            if (clave.indexOf('persona:') === 0) {
                if (!grupo.P_1) mensajes.push('Indique el vehículo asociado a cada persona; use 0 cuando corresponda.');
                else if (grupo.P_1 !== '0') personas[grupo.P_1] = (personas[grupo.P_1] || 0) + 1;
            }
        });
        Object.keys(personas).forEach(function (n) {
            if (!Object.prototype.hasOwnProperty.call(vehiculos, n)) mensajes.push('Hay personas asociadas al vehículo ' + n + ', que no está registrado o no tiene una cantidad válida de ocupantes.');
        });
        Object.keys(vehiculos).forEach(function (n) {
            var cantidad = personas[n] || 0;
            if (vehiculos[n] !== cantidad) mensajes.push('Vehículo ' + n + ': se declararon ' + vehiculos[n] + ' ocupantes y se registraron ' + cantidad + ' ' + (cantidad === 1 ? 'persona' : 'personas') + '. Las cantidades deben coincidir para guardar.');
        });
        // El servidor comprueba también los valores ausentes y otras inconsistencias.
        alerta.textContent = mensajes.join(' ');
        alerta.style.display = mensajes.length ? '' : 'none';
    }
    app.$watch('setEdiciones', actualizar, { deep: true });
    actualizar();
}());
