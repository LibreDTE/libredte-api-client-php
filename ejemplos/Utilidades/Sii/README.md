Ejemplos consumo servicios web del SII en libredte.cl
=====================================================

Los ejemplos de este directorio consumen servicios web de libredte.cl que se han
habilitado para proporcionar una interfaz de acceso alternativa a la web del SII.
De esta forma es posible hacer integraciones de manera sencilla y evitar tener
que ingresar al sitio web del SII para hacer estas acciones.

Los servicios actualmente permiten:

1. BHE (boleta honorarios electrónica):
    - Obtener listado de boletas de honorarios electrónicas recibidas en el SII de un contribuyente (formato CSV o JSON).
    - Descargar el PDF de una boleta de honorarios electrónica.

2. DTE (documentos tributarios electrónicos):
    - Obtener listado de contribuyentes autorizados (formato CSV o JSON).
    - Desplegar página de consulta de estado de un envío en el SII (formato web).
    - Obtener documentos recibidos en el SII para un contribuyente (formato CSV o JSON).
    - Solicitar timbraje electrónico nuevo (descarga archivo CAF).
    - Reobtener timbraje electrónico previamente solicitado (descarga archivo CAF).
    - Obtener código de reemplazo de libros electrónicos de compra/venta (entrega JSON con código).
    - Consultar situación tributaria (entrega JSON con datos del contribuyente).

3. RCV (registro compra venta):
    - Obtener resumen de un período.
    - Obtener detalle con los DTE de un período.
    - Asignar tipo de transacción a DTE del registro de compras.

Para poder consumir los servicios desde una aplicación propia se requiere una
cuenta autorizada en libredte.cl Primero [regístrate](https://libredte.cl/usuarios/registrar)
y luego [contáctanos](https://libredte.cl/contacto) para activarla.

Si usas la aplicación web de LibreDTE la integración ya está hecha, sólo debes
[contactarnos](https://libredte.cl/contacto) y solicitar que se active tu cuenta
de libredte.cl para que puedas empezar a usar estos servicios en tu instancia de
la aplicación.

**Importante**: estos servicios de libredte.cl realizan la comunicación directa
con el SII y sin almacenar en el servidor de libredte.cl los datos asociados a
la solicitud o respuesta generada.

Sobre la firma
--------------

La firma electrónica debe estar en formato PEM, puedes enviar sólo el .crt con el certificado
y la clave o bien enviar dos archivos (.crt y .key). La firma en .p12 se puede convertir así:

    $ openssl pkcs12 -in firma.p12 -out firma.crt -nodes

Esto genera un sólo archivo, que se usa sólo con el índice cert-data en los ejemplos.
