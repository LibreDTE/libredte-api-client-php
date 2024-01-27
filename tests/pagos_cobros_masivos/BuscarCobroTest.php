<?php

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

use PHPUnit\Framework\TestCase;
use libredte\api_client\ApiClient;
use libredte\api_client\ApiException;

class BuscarCobroTest extends TestCase
{

    protected static $verbose;
    protected static $client;
    protected static $emisor_rut;

    public static function setUpBeforeClass(): void
    {
        self::$verbose = env('TEST_VERBOSE', false);
        self::$emisor_rut = (int)(explode('-', (string)env('LIBREDTE_RUT'))[0]);
        self::$client = new ApiClient();
    }

    private function _buscar(): array
    {
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
            'pagado' => false,
        ];
        $resource = sprintf('/pagos/cobros/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != 200) {
            throw new ApiException($response['body'], $response['status']['code']);
        }
        if (empty($response['body'])) {
            throw new ApiException('No se encontraron cobros para la búsqueda realizada.', 404);
        }
        return $response['body'];
    }

    public function test_pagos_buscar_cobro(): void
    {
        try {
            $cobros = $this->_buscar();
            $this->assertTrue(true);
            if (self::$verbose) {
                echo "\n",'test_pagos_buscar_cobros() n_cobros ',count($cobros),"\n";
                echo "\n",'test_pagos_buscar_cobros() codigo_codigo ',$cobros[0]['codigo'],"\n";
                echo "\n",'test_pagos_buscar_cobros() cobros_fecha ',$cobros[0]['fecha'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_pagos_info_cobro(): void
    {
        try {
            $cobros = $this->_buscar();
            $resource = sprintf(
                '/pagos/cobros/info/%s/%d',
                $cobros[0]['codigo'],
                self::$emisor_rut
            );
            $response = self::$client->get($resource);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_pagos_info_cobro() cobro_codigo ',$response['body']['codigo'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_fecha ',$response['body']['fecha'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_total ',$response['body']['total'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_pagado ',$response['body']['pagado'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_medio ',$response['body']['medio'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_pagos_pagar_cobro(): void
    {
        $datos = ['fecha' => date('Y-m-d'), 'medio' => 'transferencia'];
        try {
            $cobros = $this->_buscar();
            $resource = sprintf(
                '/pagos/cobros/pagar/%s/%d',
                $cobros[0]['codigo'],
                self::$emisor_rut
            );
            $response = self::$client->post($resource, $datos);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_pagos_info_cobro() cobro_codigo ',$response['body']['codigo'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_fecha ',$response['body']['fecha'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_total ',$response['body']['total'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_pagado ',$response['body']['pagado'],"\n";
                echo "\n",'test_pagos_info_cobro() cobro_medio ',$response['body']['medio'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

}
