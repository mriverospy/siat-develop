# Proceso para Ajustes de Respuestas

En caso de que se requiera realizar ajustes/modificaciones a los valores de respuestas cargados en el SIAT, es necesario hacerlos en la base de datos.

El proceso se debe hacer de la siguiente forma:
1) Identificar la Pregunta y el Identificador de Pregunta (ID) para la cual es necesario ajuster los valores, desde el SIAT, ir a Mantenimiento > Grupo de Preguntas
2) Ubicar al Grupo de Pregunta que contiene la Pregunta y hacer click en "Listar Preguntas en este Grupo de Preguntas" (botón de color amarillo)
3) Ubicar la Pregunta y anotar su ID, cada pregunta se lista en esta pagina en el formato "NOMBRE_PREGUNTA (ID: ID_PREGUNTA)"
4) Conectarse a la base de datos utilizando un cliente MySQL y utilizar los queries disponibles más abajo para listar, actualizar y/o borrar los valores

## Consultar valores de respuestas y total de respuestas por ID de pregunta
Este query muestra todos los valores de respuestas cargadas para una pregunta y la cantidad de respuestas que contiene cada valor.
```sql
SELECT respuesta_texto, COUNT(*) AS total_respuestas
FROM respuestas
WHERE pregunta_id = 62
GROUP BY respuesta_texto
ORDER BY respuesta_texto
```

## Actualizar el valor de una respuesta y cambiar a otro valor
Este query muestra cómo cambiar el valor de una respuesta por otro valor, en el ejemplo de abajo, se cambian todas los valores cargados como "Valor A" a "Valor B".
```sql
UPDATE respuestas
SET respuesta_texto = 'Valor B'
WHERE pregunta_id = 62 AND respuesta_texto = 'Valor A'
```
# Borrar valores de respuestas (por ser erróneas o porque ya no corresponden)
Este query muestra cómo borrar valores específicos de las respuestas de una pregunta. En el ejemplo de abajo, se borran todos los valores de respuestas que tienen el valor "No Aplica".
```sql
DELETE FROM respuestas
WHERE pregunta_id = 62 AND respuesta_texto = 'No Aplica'
```
