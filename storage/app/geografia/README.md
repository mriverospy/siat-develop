# Cartografía para la carga de accidentes

Fuente: Instituto Nacional de Estadística (INE), Cartografía Censal 2022.
Descargada el 2026-09-08. Los límites son referenciales para fines estadísticos;
esta adaptación no representa una delimitación legal ni un producto patrocinado por el INE.

- Página: https://www.ine.gov.py/microdatos/cartografia-digital-2022.php
- Archivo: https://www.ine.gov.py/microdatos/register/CARTOGRAFIA%20CENSAL%202022/CARTOGRAFIA%20CENSAL_GeoJSON/territorio.rar
- Capa utilizada: `TOTAL PAIS/Distritos Paraguay_2022.geojson` (263 entidades).
- Licencia aplicable: Licencia de Uso de la Información Pública del Gobierno Paraguayo,
  https://www.ine.gov.py/microdatos/license.php (permite uso y transformación con atribución).

## Preparación y cobertura

El script `scripts/geografia/importar.py` transforma EPSG:4674 a EPSG:4326
con pyproj, repara geometrías mediante Shapely `make_valid` y conserva los
vértices sin simplificar. Los departamentos se obtienen por unión de todos los
distritos del INE de cada departamento, incluidos aquellos sin ID local, para
que las dos validaciones utilicen límites coherentes. El punto de referencia
se calcula con `representative_point`, siempre interior; no se registra como
ubicación confirmada automáticamente.

Los GeoJSON se almacenan comprimidos sin pérdida (`.geojson.gz`) para reducir
el tamaño del repositorio. PHP los descomprime antes de servirlos. El navegador
y el servidor verifican la misma geometría y admiten puntos sobre el borde con
una tolerancia exclusivamente numérica de 1e-10 grados. No se admiten puntos
en el interior de huecos. Las teselas de OpenStreetMap sólo sirven como fondo.

`catalogo.json` contiene los IDs locales, nombres, departamento padre, código
INE, hash SHA-256 de la capa de origen y casos pendientes. Las equivalencias de
escritura están explícitas en el script; nunca se elige un distrito por
similitud aproximada durante el uso del formulario. Se conservan los IDs
locales duplicados de Yby Yau asociados a la misma geometría.

Cobertura inicial: 18 departamentos y 252 de los 258 registros de distritos.
Los siguientes registros no tienen correspondencia directa y utilizan el límite de su departamento:

| ID local | Nombre | Departamento |
| --- | --- | --- |
| 15 | Vallemi | Concepcion |
| 238 | Pozo Colorado | Presidente Hayes |
| 248 | Gral. Eugenio A. Garay | Boqueron |
| 251 | Menno | Boqueron |
| 252 | Fernheim | Boqueron |
| 253 | Neuland | Boqueron |

Para aplicarles un límite distrital se requiere una correspondencia territorial verificada o
una corrección del catálogo por sus responsables. Mientras tanto se permite confirmar y guardar
ubicaciones dentro del departamento, informando esta condición en el mapa. No sustituir por un
rectángulo, punto central o distrito vecino. Un nombre o departamento cambiado
en el catálogo local invalida su asociación distrital hasta actualizarla; se utiliza el departamento verificado.

## Actualización reproducible

1. Descargar la cartografía de la fuente y extraer la capa de distritos.
2. Exportar únicamente los catálogos locales con `scripts/geografia/exportar.php`.
3. Instalar Shapely 2.1.2 y pyproj 3.7.1 en un entorno temporal de Python.
4. Ejecutar desde la raíz del proyecto:

```sh
php scripts/geografia/exportar.php /tmp/catalogos-siat.json
python scripts/geografia/importar.py /ruta/Distritos.geojson /tmp/catalogos-siat.json /tmp/geografia-nueva
```

5. Revisar `pendientes`, equivalencias y cambios de IDs. Eliminar asociaciones
   obsoletas al reemplazar el conjunto de archivos. Registrar nueva fuente,
   licencia y fecha. No usar este importador para otros esquemas o CRS sin adaptarlo.
6. Sustituir el conjunto local tras revisar la cobertura y ejecutar:

```sh
vendor/bin/phpunit tests/Unit/GeografiaAccidenteTest.php
vendor/bin/phpunit tests/Feature/UbicacionCargaTest.php
node tests/js/geografia.test.js
```

## Integración y publicación

La validación geográfica se aplica sólo a `cargarRespuestas`. No modifica
registros históricos ni el flujo de edición. Las rutas de geometría requieren
autenticación y permiso de carga de accidentes. Si el distrito no tiene asociación o archivo geográfico, se utiliza el límite departamental
tanto en el navegador como en el servidor. Una geometría departamental ausente o una
cartografía corrupta bloquea la confirmación y el guardado con un mensaje explícito.

Publicar conjuntamente los archivos PHP, vistas, rutas, `public/js/mapa_accidente.js`,
`public/js/carga_evento.js` y este directorio completo. No hay migraciones de
base de datos ni dependencias nuevas en ejecución; la preparación usa Python
sólo durante la actualización de la cartografía. `gulpfile.js` incluye la
publicación del módulo del mapa. Las vistas versionan ambos scripts por fecha
de modificación para evitar usar una copia anterior del navegador.
