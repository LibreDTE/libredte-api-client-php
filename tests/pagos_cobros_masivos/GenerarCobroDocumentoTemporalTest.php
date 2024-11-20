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
class GenerarCobroDocumentoTemporalTest extends TestCase
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
        ];
        $resource = sprintf('/dte/dte_tmps/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        return $response['body'];
    }

    public function test_pagos_generar_cobro_documento_temporal(): void
    {
        try {
            $documentos = $this->_buscar();
            $resource = sprintf(
                '/dte/dte_tmps/cobro/%d/%d/%s/%d',
                $documentos[0]['receptor'],
                $documentos[0]['dte'],
                $documentos[0]['codigo'],
                self::$emisor_rut
            );
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                $temporal_folio = $documentos[0]['folio'];
                echo "\n",'test_pagos_generar_cobro_dte_temporal() temporal_folio ',$temporal_folio,"\n";
                echo "\n",'test_pagos_generar_cobro_dte_temporal() cobro_codigo ',$response['body']['codigo'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
