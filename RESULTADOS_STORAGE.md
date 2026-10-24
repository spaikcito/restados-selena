# Resultados Selena: ocho loterías y almacenamiento

Se procesan únicamente Lotto Activo, Guácharo Activo, Lotto Activo RD, La Granjita, SelvaPlus, Monje Millonario, Guacharito y Loto Activo RD INT.

Las tres páginas externas son agregadores: una descarga de cada página permite leer las ocho loterías. No se crean consultas separadas por cada lotería. El navegador solo solicita resultados al abrir o recargar la página. No hay temporizadores, tareas cron ni actualización al volver a una pestaña.

## Conectar la base persistente

1. En Vercel, abre el proyecto de resultados Selena → Storage → Create Database.
2. Elige Upstash Redis y un plan adecuado al uso de tu cuenta.
3. Vincula la base a este proyecto y al entorno Production. La integración debe agregar estas variables privadas:
   - `UPSTASH_REDIS_REST_URL`
   - `UPSTASH_REDIS_REST_TOKEN` (token con escritura, no el token de solo lectura)
4. Crea un despliegue nuevo para que las Functions reciban las variables.

No pegues credenciales en GitHub, en JavaScript ni en capturas. No hace falta ejecutar SQL: Redis crea sus claves al guardar la primera consulta correcta.

## Cómo se evita repetir consultas

- Las respuestas válidas se reutilizan hasta 60 segundos, entre usuarios e instancias, sin renovar su antigüedad al leerlas.
- Después de esos 60 segundos, solo una petición iniciada por una nueva carga de página actualiza cada fuente. La base no inicia descargas automáticamente.
- Una reserva compartida impide que varias instancias descarguen la misma fuente a la vez.
- Los sorteos se guardan sin caducidad en `selena:v3:resultados:AAAA-MM-DD:FUENTE`. Cada campo corresponde a una lotería y un horario. Una corrección actualiza ese registro sin duplicarlo.
- Las fuentes se conservan independientes: 365, Lotoven y Lotería de Hoy. Se sigue mostrando la fecha de Venezuela.
- Si una fuente falla, se pueden mostrar sus últimos resultados guardados **de hoy**, con un aviso y su hora original. Nunca se presentan los de ayer como resultados de hoy.
- La base solo guarda resultados, imágenes publicadas y fecha de consulta; no guarda las jugadas escritas en la calculadora.

En la respuesta de cada API, `almacenamiento.configurado` indica si hay credenciales y `almacenamiento.guardado` si la escritura se confirmó. `desde_historial` indica que se reutiliza una respuesta almacenada. Mientras la base no esté conectada, la web sigue funcionando con la caché temporal del servidor y CDN, pero no conserva un historial persistente.

## Ceros

El número se conserva como texto: delfín `0`, ballena `00`, carnero `01`. No se convierten `0` y `00` al mismo número al comparar fuentes, elegir imágenes o copiar resultados.
