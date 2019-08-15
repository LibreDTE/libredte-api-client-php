Ejemplos consumo servicios web del SII en libredte.cl
=====================================================

Los ejemplos de este directorio consumen servicios web de libredte.cl que se han
habilitado para proporcionar una interfaz de acceso alternativa a la web del SII.
De esta forma es posible hacer integraciones de manera sencilla y evitar tener
que ingresar al sitio web del SII para hacer estas acciones.

Estos ejemplos están en PHP, pero los servicios web pueden ser consumidos desde
cualquier lenguaje. No es obligatorio usar PHP.

Los servicios actualmente permiten:

1. BHE (Boleta de Honorarios Electrónica):
    - Obtener listado de boletas de honorarios electrónicas recibidas en el SII de un contribuyente (formato CSV o JSON).
    - Descargar el PDF de una boleta de honorarios electrónica.
    - Emitir boleta de honorarios electrónica.

2. BTE (Boleta de Terceros Electrónica):
    - Obtener listado de boletas de terceros electrónicas emitidas en el SII de un contribuyente (formato CSV o JSON).
    - Descargar el HTML de una boleta de terceros electrónica.

3. DTE (Documentos Tributarios Electrónicos):
    - Obtener listado de contribuyentes autorizados (formato CSV o JSON).
    - Desplegar página de consulta de estado de un envío en el SII (formato web).
    - Obtener documentos recibidos en el SII para un contribuyente (formato CSV o JSON).
    - Solicitar timbraje electrónico nuevo (descarga archivo CAF).
    - Reobtener timbraje electrónico previamente solicitado (descarga archivo CAF).
    - Obtener código de reemplazo de libros electrónicos de compra/venta (entrega JSON con código).
    - Consultar datos del contribuyente para facturación electrónica (datos privados).
    - Verificar datos de un DTE (en un sólo servicio, la verificación completa del DTE).
    - Consultar estado de un folio de un DTE (formato web).
    - Anular folio de un DTE (respuesta en formato web).

4. RCV (Registro Compra y Venta):
    - Obtener resumen de un período.
    - Obtener detalle con los DTE de un período.
    - Asignar tipo de transacción a DTE del registro de compras.
    - Asignar el resumen de boletas electrónicas emitidas (en realidad, asignar cualquier resumen).

5. RTC (Registro de Transferencias de Créditos)
    - Buscar el estado de la cesión de un DTE.
    - Buscar cesiones en un período de tiempo (deudor, cedente y cesionario).

6. MIPYME (Portal MIPYME del SII):
    - Obtener listado de documentos emitidos.
    - Descargar PDF de un documento emitido.
    - Descargar XML de un documento emitido.
    - Obtener listado de documentos recibidos.
    - Descargar PDF de un documento recibido.
    - Descargar XML de un documento recibido.

7. Otros:
    - Consultar situación tributaria (entrega JSON con datos del contribuyente).

**Importante**: estos servicios de libredte.cl realizan la comunicación directa
con el SII y sin almacenar en el servidor de libredte.cl los datos asociados a
la solicitud o respuesta generada.

¿Cómo puedo usar los servicios web?
-----------------------------------

Para poder consumir los servicios desde una aplicación propia se requiere una
cuenta autorizada en libredte.cl Primero [regístrate](https://libredte.cl/usuarios/registrar),
luego [adquiere el servicio mensual](https://tienda.sasco.cl/catalogo/i/INT1/sii-ws)
y finalmente [contáctanos](https://libredte.cl/contacto) para activar tu usuario.

Si usas la aplicación web de LibreDTE la integración ya está hecha, sólo debes
hacer los pasos anteriores y luego configurar lo siguiente en tu instancia:

```php
// configuración autenticación servicios externos
\sowerphp\core\Configure::write('proveedores.api', [
    'libredte' => 'AQUI EL HASH DE TU USUARIO',
]);
```

Sobre la firma
--------------

La firma electrónica debe estar en formato PEM, puedes enviar sólo el .crt con el certificado
y la clave o bien enviar dos archivos (.crt y .key). La firma en .p12 se puede convertir así:

    $ openssl pkcs12 -in firma.p12 -out firma.crt -nodes

Esto genera un sólo archivo, que se usa sólo con el índice cert-data en los ejemplos.
