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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiClient::class)]
#[CoversClass(HttpCurlClient::class)]
/**
 * Método de test para generar un cobro a partir de un DTE temporal.
 */
class ObtenerCobroDocumentoTempTest extends TestCase
{
    /**
     * Variable para despliegue de resultados por consola
     *
     * @var bool
     */
    protected static $verbose;

    /**
     * Variable de instanciación del API Client.
     *
     * @var ApiClient
     */
    protected static $client;

    /**
     * RUT del emisor sin DV.
     *
     * @var int
     */
    protected static $emisor_rut;

    /**
     * Inicialización de variables y clases pre ejecución de tests.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$verbose = env('TEST_VERBOSE', false);
        self::$emisor_rut = (int)(explode('-', (string)env('LIBREDTE_RUT'))[0]);
        self::$client = new ApiClient();
    }

    /**
     * Método privado para buscar un cobro.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array Arreglo con los cobros activos asociados a DTEs.
     */
    private function _buscar(): array
    {
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
        ];
        $resource = sprintf('/dte/dte_tmps/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        return $response['body'];
    }

    /**
     * Método de test para obtener un cobro asociado a un DTE temporal.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testObtenerCobroDteTemp(): void
    {
        try {
            # Búsqueda de DTE emitido.
            $documentos = $this->_buscar();
            # Recurso a consumir.
            $resource = sprintf(
                '/dte/dte_tmps/cobro/%d/%d/%s/%d',
                $documentos[0]['receptor'],
                $documentos[0]['dte'],
                $documentos[0]['codigo'],
                self::$emisor_rut
            );
            # Se envía la solicitud http y se guarda su respuesta.
            # Se obtiene el cobro asociado con el recurso previo.
            $response = self::$client->get($resource);
            # Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            # Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);
            # Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                $temporal_folio = $documentos[0]['folio'];
                echo "\n",'test_pagos_generar_cobro_dte_temporal() temporal_folio ',$temporal_folio,"\n";
                echo "\n",'test_pagos_generar_cobro_dte_temporal() cobro_codigo ',$response['body']['codigo'],"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
