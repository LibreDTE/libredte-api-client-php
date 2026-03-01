<?php

declare(strict_types=1);

/**
 * LibreDTE: Cliente de API en PHP - Pruebas Unitarias.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o modificarlo
 * bajo los términos de la GNU Lesser General Public License (LGPL) publicada
 * por la Fundación para el Software Libre, ya sea la versión 3 de la Licencia,
 * o (a su elección) cualquier versión posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero SIN
 * GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o de APTITUD
 * PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de la GNU Lesser General
 * Public License (LGPL) para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la GNU Lesser General Public License
 * (LGPL) junto a este programa. En caso contrario, consulte
 * <http://www.gnu.org/licenses/lgpl.html>.
 */

use libredte\api_client\ApiClient;
use libredte\api_client\ApiException;
use libredte\api_client\HttpCurlClient;
use libredte\pagos_cobros_masivos\AbstractPagosCobrosMasivos;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiClient::class)]
#[CoversClass(HttpCurlClient::class)]
/**
 * Clase de test para generar un cobro de un documento emitido (real).
 */
class ObtenerCobroDocumentoEmitidoTest extends AbstractPagosCobrosMasivos
{
    /**
     * Método de test para obtener un cobro asociado a un DTE emitido.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testObtenerCobroDteEmitido(): void
    {
        try {
            // Búsqueda de DTE emitido.
            $documentos = $this->listarDteEmitidos();
            // Recurso a consumir.
            $resource = sprintf(
                '/dte/dte_emitidos/cobro/%d/%d/%d',
                $documentos['body'][0]['dte'],
                $documentos['body'][0]['folio'],
                self::$emisor_rut
            );
            // Se envía la solicitud http y se guarda su respuesta.
            // Se obtiene el cobro asociado con el recurso previo.
            $response = self::$client->get($resource);
            // Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] != '200') {
                throw new ApiException(
                    message: $response['body'],
                    code: (int)$response['status']['code']
                );
            }
            // Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);
            // Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",
                'testObtenerCobroDteEmitido() Cobro: ',
                json_encode(
                    $response['body']
                ),"\n";
            }
        } catch (ApiException $e) {
            // Si falla, desplegará el mensaje y error en el siguiente formato:
            // [ApiException codigo-http] mensaje]
            $this->fail(sprintf(
                '[ApiException %d] %s',
                $e->getCode(),
                $e->getMessage()
            ));
        }
    }
}
