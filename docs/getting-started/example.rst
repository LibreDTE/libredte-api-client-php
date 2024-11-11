Ejemplo
=======

Ejemplo de Generar un DTE temporal
-------------------------------

Antes de probar, integrar y/o utilizar el cliente de API, necesitas haber definido previamente las variables de entorno.

.. seealso::
    Para más información sobre este paso, referirse al la guía en Configuración.

El siguiente es un ejemplo básico de cómo emitir un DTE usando el cliente de API de LibreDTE:

.. code-block:: php
    <?php

    # Definición de directorio autoload. Necesario si se usa la versión de GitHub.
    require_once __DIR__ . '/vendor/autoload.php';

    # Importación de biblioteca de LibreDTE
    use libredte\api_client\ApiClient;

    # Instanciación de cliente de API
    $client = new ApiClient();
    # RUT del emisor, con DV.
    $emisor_rut = '12345678-9';

    # Datos del DTE temporal a crear.
    $datos = [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
            ],
            'Emisor' => [
                'RUTEmisor' => $emisor_rut,
            ],
            'Receptor' => [
                'RUTRecep' => '60803000-K',
                'RznSocRecep' => 'Servicio de Impuestos Internos (SII)',
                'GiroRecep' => 'Administración Pública',
                'Contacto' => '+56 2 3252 5575',
                'CorreoRecep' => 'facturacionmipyme@sii.cl',
                'DirRecep' => 'Teatinos 120',
                'CmnaRecep' => 'Santiago',
            ],
        ],
        'Detalle' => [
            [
                //'IndExe' => 1, // para items exentos
                'NmbItem' => 'Asesoría de LibreDTE',
                'QtyItem' => 1,
                'PrcItem' => 1000,
            ],
        ],
        'Referencia' => [
            [
                'TpoDocRef' => 801,
                'FolioRef' => 'OC123',
                'FchRef' => '2015-10-01',
            ]
        ],
    ];

    # Recurso a consumir.
    $resource = '/dte/documentos/emitir';

    # Se efectua la solicitud HTTP y se guarda la respuesta.
    $response = $client->post($resource, $datos);

    # Código del response
    echo "Status: ".$response['status']['code']."\n";

    # Despliegue del body.
    if ($response['status']['code'] != 200) {
        echo $response['body']."\n"; # Si falla, el body contendrá el mensaje de error.
    } else {
        echo "\nDTEs Temporales: \n";
        echo "\n",'FACTURAR DTE TEMP: ',json_encode($response['body']),"\n";
    }

.. seealso::
    Para saber más sobre los parámetros posibles y el cómo consumir las API, referirse a la `documentación de LibreDTE. <https://developers.libredte.cl/>`_
