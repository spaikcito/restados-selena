# Herramientas de resultados y cálculo

- **Historial:** en «Historial de resultados guardados», elegir fecha, lotería y, opcionalmente, número o animalito; pulsar «Consultar historial». Solo lee las claves de Redis, sin descargar fuentes. No crea registros anteriores al comienzo del guardado.
- **Buscar animalitos:** filtra las tres respuestas ya recibidas. `0` y `00` se buscan de forma exacta; los nombres admiten texto parcial y tildes opcionales.
- **Antigüedad:** cada fuente muestra su fecha y hora de consulta en Venezuela. Las tarjetas y el aviso superior identifican sorteos de horarios anteriores. Para SelvaPlus, Monje y las dos loterías de media hora se contempla su minuto de sorteo.
- **Comparación:** las tarjetas muestran las fuentes coincidentes y los números que difieren. El panel de comparación lista únicamente sorteos con la misma fecha, lotería y horario. No determina cuál fuente tiene razón.
- **Calculadora:** los botones de lotería se aplican si el texto no especifica otras. El resumen agrega todas las jugadas por lotería. «Copiar cálculo» incluye el total, los subtotales y las observaciones por exclusiones o fragmentos sin reconocer.

Las consultas a las páginas de resultados siguen realizándose una vez al abrir o recargar. La búsqueda, las selecciones de la calculadora y la comparación funcionan en el navegador. El historial solo lee la base cuando el usuario envía el formulario; repetir la misma búsqueda durante un minuto reutiliza su respuesta.

Las jugadas de la calculadora no se envían ni guardan en Redis. El botón de copia escribe únicamente el reporte solicitado en el portapapeles.

## Pruebas

```sh
node tests/calculadora-multiline.cjs
node tests/calculadora-tools.cjs
php -d disable_functions=curl_init,curl_setopt_array,curl_exec,curl_getinfo tests/store.php
```

La prueba de almacenamiento utiliza un transporte simulado, no una base real. En producción se debe confirmar además la respuesta de `historial.php?fecha=AAAA-MM-DD` con la base ya conectada.
