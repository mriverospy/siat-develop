"""Preparar cartografía INE 2022. Ver storage/app/geografia/README.md."""
import argparse
import gzip
import hashlib
import json
import unicodedata
from pathlib import Path
from pyproj import Transformer
from shapely import make_valid
from shapely.geometry import shape, mapping, MultiPolygon
from shapely.ops import transform, unary_union


def normalizar(texto):
    return ''.join(c for c in unicodedata.normalize('NFD', texto.lower().strip()) if not unicodedata.combining(c))


# Equivalencias de escritura revisadas dentro del mismo departamento.
ALIAS = {
    'san carlos': 'san carlos del apa', "yvy ya'u": 'yby yau', 'azotey': "azote'y",
    'sgto. jose feliz lopez': 'sargento jose felix lopez',
    'san pedro del ycuamandiyu': 'san pedro del ycuamandyyu',
    'gral. elizardo aquino': 'general elizardo aquino',
    'gral. resquin': 'general francisco isidoro resquin', 'guayaibi': 'guajayvi',
    'yryvu cua': 'yrybucua', 'mauricio jose troche': 'capitan mauricio jose troche',
    'colonia independencia': 'independencia', 'dr. bottrell': 'doctor bottrell',
    'cecilio baez': 'dr. cecilio baez',
    'mariscal francisco solano lope': 'mariscal francisco solano lopez',
    'moises bertoni': 'dr. moises s. bertoni', 'fulgencio yegros': 'yegros',
    'gral. delgado': 'general delgado', 'leandro oviedo': 'jose leandro oviedo',
    'mayor otano': 'mayor julio dionisio otano',
    'gral. bernardino caballero': 'caballero',
    'san roque gonzalez': 'roque gonzalez de santa cruz',
    "dr. juan e. o'leary": "juan e. o'leary", 'gral. diaz': 'gral. jose eduvigis diaz',
    'guazu cua': 'guazu-cua', 'los laureles': 'laureles',
    'mayor martinez': 'mayor jose dejesus martinez',
    'san juan bautista del neembucu': 'san juan bautista de neembucu',
    'bella vista norte': 'bella vista', 'saltos del guaira': 'salto del guaira',
    'curuguaty': 'villa curuguaty', 'la paloma': 'la paloma del espiritu santo',
    'yasy kany': 'yasy cany',
    'tte. 1ro. manuel irala fernand': 'tte. 1° manuel irala fernandez',
    'tte. esteban martinez': 'teniente esteban martinez',
    'gral. jose maria bruguez': 'general jose maria bruguez',
    'mariscal estigarribia': 'mariscal jose felix estigarribia',
}


def preparar(origen, catalogos, destino):
    source = json.loads(origen.read_text())
    transformer = Transformer.from_crs('EPSG:4674', 'EPSG:4326', always_xy=True)
    geometries = {}
    by_department = {}
    for feature in source['features']:
        props = feature['properties']
        geometry = make_valid(transform(transformer.transform, shape(feature['geometry'])))
        if geometry.geom_type == 'GeometryCollection':
            geometry = unary_union([g for g in geometry.geoms if g.geom_type in ('Polygon', 'MultiPolygon')])
        assert geometry.geom_type in ('Polygon', 'MultiPolygon') and geometry.is_valid and not geometry.is_empty
        key = (normalizar(props['DPTO_DESC']), normalizar(props['DIST_DESC_']))
        assert key not in geometries, key
        geometries[key] = (geometry, props['CLAVE'])
        by_department.setdefault(key[0], []).append(geometry)
    catalog = {'fuente': 'INE, Cartografía Censal 2022', 'sha256_origen': hashlib.sha256(origen.read_bytes()).hexdigest(), 'departamentos': {}, 'distritos': {}, 'pendientes': []}
    departments = {d['id']: d for d in catalogos['departamentos']}

    def guardar(tipo, item, geometry, code):
        point = geometry.representative_point()
        record = dict(item, codigo_ine=code)
        feature = {'type': 'Feature', 'properties': dict(record, referencia=[point.x, point.y]), 'geometry': mapping(geometry)}
        path = destino / tipo / (str(item['id']) + '.geojson.gz')
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_bytes(gzip.compress(json.dumps(feature, ensure_ascii=False, separators=(',', ':')).encode(), mtime=0))
        catalog[tipo][str(item['id'])] = record

    # La unión de los distritos evita divergencias entre capas del mismo departamento.
    for dept in departments.values():
        name = normalizar(dept['nombre'])
        assert name in by_department, name
        geometry = unary_union(by_department[name])
        assert geometry.is_valid
        codes = {code[:2] for (dep, _), (_, code) in geometries.items() if dep == name}
        assert len(codes) == 1
        guardar('departamentos', dept, geometry, codes.pop())
    for district in catalogos['distritos']:
        dep = normalizar(departments[district['departamento_id']]['nombre'])
        name = normalizar(district['nombre'])
        if dep == 'misiones' and name == 'san juan bautista':
            name = 'san juan bautista de las misiones'
        key = (dep, ALIAS.get(name, name))
        if key not in geometries:
            catalog['pendientes'].append(district)
            continue
        geometry, code = geometries[key]
        guardar('distritos', district, geometry, code)
    (destino / 'catalogo.json').write_text(json.dumps(catalog, ensure_ascii=False, indent=2) + '\n')
    print('Departamentos:', len(catalog['departamentos']), 'Distritos:', len(catalog['distritos']))
    print('Pendientes:', catalog['pendientes'])


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('origen', type=Path)
    parser.add_argument('catalogos', type=Path)
    parser.add_argument('destino', type=Path)
    args = parser.parse_args()
    preparar(args.origen, json.loads(args.catalogos.read_text()), args.destino)
