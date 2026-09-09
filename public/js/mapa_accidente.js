(function(root) {
    'use strict';

    // Misma regla de borde y tolerancia numérica que GeografiaAccidente.php.
    function anillo(ring, x, y) {
        if (ring.length < 4) return -1;
        var inside = false;
        for (var i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            var ax = ring[j][0], ay = ring[j][1], bx = ring[i][0], by = ring[i][1];
            var dx = bx - ax, dy = by - ay, length2 = dx * dx + dy * dy;
            var px = x - ax, py = y - ay;
            if (length2 > 0) {
                var t = (px * dx + py * dy) / length2;
                var ex = x - (ax + t * dx), ey = y - (ay + t * dy);
                if (t >= 0 && t <= 1 && Math.sqrt(ex * ex + ey * ey) <= 1e-10) return 0;
            } else if (Math.sqrt(px * px + py * py) <= 1e-10) return 0;
            if ((ay > y) !== (by > y) && x < (bx - ax) * (y - ay) / (by - ay) + ax) inside = !inside;
        }
        return inside ? 1 : -1;
    }

    function contiene(geometry, x, y) {
        if (!geometry || !isFinite(x) || !isFinite(y)) return false;
        var polygons = geometry.type === 'Polygon' ? [geometry.coordinates] :
            (geometry.type === 'MultiPolygon' ? geometry.coordinates : []);
        return polygons.some(function(rings) {
            if (!rings.length || anillo(rings[0], x, y) < 0) return false;
            return !rings.slice(1).some(function(hole) { return anillo(hole, x, y) > 0; });
        });
    }

    function crear(element) {
        var $ = root.jQuery, L = root.L, scope = $(element);
        var container = scope.find('#coordenadas-accidente')[0];
        var state = scope.find('#mapa-estado'), modify = scope.find('#mapa-modificar');
        var confirm = scope.find('#mapa-confirmar'), retry = scope.find('#mapa-reintentar');
        var lat = scope.find('[name=gps_latitud]'), lng = scope.find('[name=gps_longitud]');
        var confirmed = scope.find('[name=ubicacion_confirmada]');
        function mensaje(text, error) { state.text(text).toggleClass('text-danger', !!error); }
        function limpiar() { lat.val(''); lng.val(''); confirmed.val('0'); }
        limpiar();
        if (!L || !container) {
            mensaje('No se pudo iniciar el mapa. Recargue la página para confirmar la ubicación.', true);
            return {seleccionar: function() { limpiar(); }};
        }
        var map = L.map(container, {center: [-23.4, -58.4], zoom: 6, minZoom: 5, maxZoom: 18, maxBoundsViscosity: 1});
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | INE, Censo 2022'
        }).addTo(map);
        var feature = null, outline = null, mask = null, marker = null, lastValid = null;
        var editing = false, generation = 0, request = null, department = null, district = null;
        var cache = Object.create(null);
        function navegacion(enabled) {
            ['dragging', 'scrollWheelZoom', 'doubleClickZoom', 'touchZoom', 'boxZoom', 'keyboard'].forEach(function(key) {
                if (map[key]) map[key][enabled ? 'enable' : 'disable']();
            });
        }
        navegacion(false);
        function finalizarEdicion() {
            editing = false;
            if (marker) marker.dragging.disable();
            confirm.prop('disabled', true);
        }
        function ajustar() {
            if (!outline || !$(container).is(':visible')) return;
            map.invalidateSize();
            map.setMinZoom(0);
            map.setMaxBounds(null);
            var bounds = outline.getBounds();
            map.fitBounds(bounds, {padding: [15, 15], maxZoom: 13, animate: false});
            map.setMinZoom(map.getZoom());
            map.setMaxBounds(bounds.pad(0.08));
        }
        function seleccionar(dep, dist) {
            department = dep || null; district = dist || null;
            var version = ++generation;
            if (request) { request.abort(); request = null; }
            limpiar(); finalizarEdicion(); feature = null; lastValid = null;
            modify.prop('disabled', true); retry.hide(); navegacion(false);
            if (outline) { map.removeLayer(outline); outline = null; }
            if (mask) { map.removeLayer(mask); mask = null; }
            if (marker) { map.removeLayer(marker); marker = null; }
            if (!department) {
                map.setMaxBounds(null); map.setMinZoom(5); map.setView([-23.4, -58.4], 6);
                mensaje('Seleccione un departamento para ubicar el siniestro.');
                return;
            }
            mensaje('Cargando límites de la zona seleccionada…');
            var key = district ? 'distritos/' + district : 'departamentos/' + department;
            function cargar(data) {
                if (version !== generation) return;
                var expected = district || department;
                if (!data.properties || String(data.properties.id) !== String(expected) ||
                    (district && String(data.properties.departamento_id) !== String(department)) ||
                    !data.geometry || !data.properties.referencia ||
                    !contiene(data.geometry, data.properties.referencia[0], data.properties.referencia[1])) {
                    fallo('La cartografía recibida no corresponde a la zona seleccionada.');
                    return;
                }
                cache[key] = data;
                feature = data;
                outline = L.geoJSON(data, {style: {color: '#245b98', weight: 2, fillOpacity: 0.06}}).addTo(map);
                var polygons = data.geometry.type === 'Polygon' ? [data.geometry.coordinates] : data.geometry.coordinates;
                var outside = [[[-85, -180], [-85, 180], [85, 180], [85, -180]]];
                var holes = [];
                polygons.forEach(function(rings) {
                    outside.push(rings[0].map(function(p) { return [p[1], p[0]]; }));
                    rings.slice(1).forEach(function(ring) { holes.push(ring.map(function(p) { return [p[1], p[0]]; })); });
                });
                mask = L.layerGroup().addTo(map);
                var style = {stroke: false, fillColor: '#333', fillOpacity: 0.35, fillRule: 'evenodd', interactive: false};
                L.polygon(outside, style).addTo(mask);
                holes.forEach(function(ring) { L.polygon(ring, style).addTo(mask); });
                lastValid = L.latLng(data.properties.referencia[1], data.properties.referencia[0]);
                marker = L.marker(lastValid, {draggable: true, title: 'Ubicación del siniestro'}).addTo(map);
                marker.dragging.disable();
                marker.on('dragend', function() { mover(marker.getLatLng()); });
                navegacion(true); ajustar();
                modify.prop('disabled', !district);
                mensaje(data.properties.nivel_limite === 'departamento'
                    ? 'Este distrito no tiene límite configurado. Se permite ubicar el siniestro dentro del departamento ' + data.properties.nombre_limite + '. Pulse Modificar ubicación y confirme el punto.'
                    : district ? 'Límite: ' + data.properties.nombre + '. Pulse Modificar ubicación y confirme el punto exacto.' :
                    'Límite: ' + data.properties.nombre + '. Seleccione un distrito para confirmar la ubicación.');
            }
            function fallo(text) {
                if (version !== generation) return;
                feature = null; limpiar(); finalizarEdicion(); modify.prop('disabled', true);
                mensaje(text, true); retry.show();
            }
            if (cache[key]) cargar(cache[key]);
            else request = $.ajax({url: $(container).attr('data-geografia-url') + '/' + key, dataType: 'json', timeout: 30000})
                .done(cargar).fail(function(xhr, status) {
                    if (status === 'abort') return;
                    fallo((xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo cargar el límite. Reintente antes de confirmar la ubicación.');
                });
        }
        function mover(point) {
            if (!editing || !feature || !marker) return;
            if (!contiene(feature.geometry, point.lng, point.lat)) {
                marker.setLatLng(lastValid);
                mensaje('La ubicación debe estar dentro del ' + (feature.properties.nivel_limite === 'departamento' ? 'departamento' : 'distrito') + ' seleccionado. Se conserva el último punto válido.', true);
                return;
            }
            lastValid = point; marker.setLatLng(point); limpiar();
            mensaje('Punto seleccionado. Pulse Confirmar ubicación.');
        }
        map.on('click', function(event) { mover(event.latlng); });
        modify.on('click', function() {
            if (!feature || !district || !marker) return;
            editing = true; limpiar(); marker.dragging.enable(); confirm.prop('disabled', false);
            mensaje('Haga clic dentro del contorno o arrastre el marcador. Luego confirme la ubicación.');
        });
        confirm.on('click', function() {
            if (!editing || !feature || !district || !lastValid || !contiene(feature.geometry, lastValid.lng, lastValid.lat)) return;
            // No redondear: un punto de borde no debe desplazarse al exterior al guardarlo.
            lat.val(lastValid.lat); lng.val(lastValid.lng); confirmed.val('1');
            finalizarEdicion(); mensaje('Ubicación confirmada dentro de ' + (feature.properties.nombre_limite || feature.properties.nombre) + '.');
        });
        retry.on('click', function() { seleccionar(department, district); });
        $(document).on('siat:mapa-visible', function() {
            if ($(container).is(':visible')) { map.invalidateSize(); if (outline) ajustar(); }
        });
        return {seleccionar: seleccionar};
    }
    var api = {contiene: contiene, crear: crear};
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.SiatMapa = api;
})(typeof window !== 'undefined' ? window : globalThis);
