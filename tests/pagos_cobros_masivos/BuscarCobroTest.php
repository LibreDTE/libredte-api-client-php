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
 * Clase para ejecutar tests de búsqueda de cobros.
 */
class BuscarCobroTest extends TestCase
{
    /**
     * Variable para desplegar resultados.
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
     * RUT del emisor sin DV
     *
     * @var int
     */
    protected static $emisor_rut;

    /**
     * Función para inicializar variables y clases pre ejecución de tests.
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
     * @return array Arreglo que contiene el resultado de los cobros entre 2015 y hoy.
     */
    private function _buscar(): array
    {
        # Filtros de búsqueda.
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
            'pagado' => false,
        ];
        # Recurso a consumir.
        $resource = sprintf('/pagos/cobros/buscar/%d', self::$emisor_rut);
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post($resource, $filtros);
        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        # Si el body de la respuesta está vacío o es nulo, arroja error ApiException.
        if (empty($response['body'])) {
            throw new ApiException('No se encontraron cobros para la búsqueda realizada.', 404);
        }
        return $response['body'];
    }

    /**
     * Método de test para buscar un cobro específico.
     *
     * @return void
     */
    public function testPagosBuscarCobro(): void
    {
        try {
            # Búsqueda de un cobro
            $cobros = $this->_buscar();
            # La prueba tendrá éxito si la búsqueda funciona.
            $this->assertTrue(true);
            # Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",'test_pagos_buscar_cobros() n_cobros ',count($cobros),"\n";
                echo "\n",'test_pagos_buscar_cobros() codigo_codigo ',$cobros[0]['codigo'],"\n";
                echo "\n",'test_pagos_buscar_cobros() cobros_fecha ',$cobros[0]['fecha'],"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Método de test para obtener información específica de un cobro.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testPagosInfoCobro(): void
    {
        try {
            # Búsqueda de cobros.
            $cobros = $this->_buscar();
            # Recurso a consumir.
            $resource = sprintf(
                '/pagos/cobros/info/%s/%d',
                $cobros[0]['codigo'],
                self::$emisor_rut
            );
            # Se envía la solicitud http y se guarda su respuesta.
            $response = self::$client->get($resource);
            # Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            # Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);
            # Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",'test_pagos_info_cobro() cobro_codigo ',$response['body']['codigo'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_fecha ',$response['body']['fecha'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_total ',$response['body']['total'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_pagado ',$response['body']['pagado'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_medio ',$response['body']['medio'],"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Método de test para pagar un cobro específico.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testPagosPagarCobro(): void
    {
        # Datos para la petición http.
        $datos = ['fecha' => date('Y-m-d'), 'medio' => 'transferencia'];
        try {
            # Búsqueda de un cobro.
            $cobros = $this->_buscar();
            # Recurso a consumir.
            $resource = sprintf(
                '/pagos/cobros/pagar/%s/%d',
                $cobros[0]['codigo'],
                self::$emisor_rut
            );
            # Se envía la solicitud http y se guarda su respuesta.
            $response = self::$client->post($resource, $datos);
            # Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            # Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);
            # Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",'test_pagos_info_cobro() cobro_codigo ',$response['body']['codigo'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_fecha ',$response['body']['fecha'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_total ',$response['body']['total'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_pagado ',$response['body']['pagado'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_medio ',$response['body']['medio'],"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
