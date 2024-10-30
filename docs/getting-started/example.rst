Ejemplo
=======

El siguiente es un ejemplo básico de cómo obtener un documento DTE emitido usando el cliente de API de LibreDTE:

.. code-block:: php
    <?php

    # Definición de directorio autoload. Necesario si se usa la versión de GitHub.
    require_once __DIR__ . '/vendor/autoload.php';

    # Importación de biblioteca de LibreDTE
    use libredte\api_client\ApiClient;

    # Instanciación de cliente de API
    $client = new ApiClient();

    # RUT del emisor sin Dígito Verificador.
    $emisor_rut = 12345678;
    # Filtros a aplicar en la búsqueda de DTEs.
    $filtros = [
        'fecha_desde' => '2024-10-25',
        'fecha_hasta' => date('Y-m-d'),
    ];

    # Recurso a consumir.
    $resource = sprintf('/dte/dte_tmps/buscar/%d', $emisor_rut);

    # Se efectua la solicitud HTTP y se guarda la respuesta. En esta variable están el estado, cuerpo, etc.
    $response = $client->post($resource, $filtros);

    echo "Status: ".$response['status']['code']."\n";

    if ($response['status']['code'] != 200) {
        echo $response['body']."\n";
    } else {
        $documentos = $response['body'];
        $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];

        echo "\nDTEs Temporales: \n";
        echo "\n",'N DOCUMENTOS: ',count($documentos),"\n";
        echo "\n",'DTE ID: ',$dte_id,"\n";
        echo "\n",'DTE FECHA: ',$documentos[0]['fecha'],"\n";
    }

Desgloce de ejemplo
-------------------

Antes de probar, integrar y/o utilizar el cliente de API, necesitas haber definido previamente las variables de entorno.

.. seealso::
    Para más información sobre este paso, referirse al la guía en Configuración.

Se empieza por importar e instanciar el cliente de API.

.. code-block:: php
    # Definición de directorio autoload. Necesario si se usa la versión de GitHub.
    require_once __DIR__ . '/vendor/autoload.php';

    # Importación de biblioteca de LibreDTE
    use libredte\api_client\ApiClient;

    # Instanciación de cliente de API
    $client = new ApiClient();

Luego, se definen las variables a utilizar.

.. code-block:: php
    # RUT del emisor sin Dígito Verificador.
    $emisor_rut = 12345678;
    # Filtros a aplicar en la búsqueda de DTEs.
    $filtros = [
        'fecha_desde' => '2015-01-01',
        'fecha_hasta' => date('Y-m-d'),
    ];

Más adelante, se arma el recurso a utilizar, se consume, y se obtiene su respuesta HTTP.

.. code-block:: php
    # Recurso a consumir.
    $resource = sprintf('/dte/dte_tmps/buscar/%d', $emisor_rut);

    # Se efectua la solicitud HTTP y se guarda la respuesta.
    $response = $client->post($resource, $filtros);

``$response`` contiene toda la información de la respuesta HTTP, desde el cuerpo hasta el código de estado.

Por último, se despliega en consola el resultado. Si el código de la respuesta HTTP no es 200, se mostrará el mensaje de error. Si es 200, se desplegarán los documentos consultados.

.. code-block:: php
    echo "Status: ".$response['status']['code']."\n";

    if ($response['status']['code'] != 200) {
        echo $response['body']."\n";
    } else {
        $documentos = $response['body'];
        $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];

        echo "\nDTEs Temporales: \n";
        echo "\n",'N DOCUMENTOS: ',count($documentos),"\n";
        echo "\n",'DTE ID: ',$dte_id,"\n";
        echo "\n",'DTE FECHA: ',$documentos[0]['fecha'],"\n";
    }

.. important::
    Este ejemplo solo funciona con DTEs temporales.


.. seealso::
    Para saber más sobre los parámetros posibles y el cómo consumir las API, referirse a la `documentación de LibreDTE. <https://developers.libredte.cl/>`_
