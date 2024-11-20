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
class CobroMasivoTest extends TestCase
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
            'siguiente_desde' => date('Y-m-d'),
            'siguiente_hasta' => date('Y-m-d'),
            'activo' => true,
        ];
        $resource = sprintf('/pagos/cobro_masivo_programados/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        if (empty($response['body'])) {
            throw new ApiException('No se encontraron cobros masivos programados para la búsqueda realizada.', 404);
        }
        return $response['body'];
    }

    public function test_pagos_buscar_cobro_masivo_programado(): void
    {
        try {
            $cobros = $this->_buscar();
            $this->assertTrue(true);
            if (self::$verbose) {
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() n_cobros ',count($cobros),"\n";
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Prueba unitaria que muestra los pasos para:
     *  - Guardar cambios en un cobro masivo programado a un receptor
     *
     * Esto permite aplicar un descuento o modificar la cantidad en el listado
     * de items. Por ejemplo, sirve para actualizar la cantidad de algo que se
     * está cobrando (ej: consumo de GB en una cuenta de hosting).
     *
     * Se puede editar todo el cobro, o sea los campos:
     *  - dte: código del DTE, ej: 33 (factura electrónica afecta)
     *  - dte_real: =1 emite DTE real, =0 emite cotización
     *  - siguiente: fecha siguiente cobro AAAA-MM-DD
     *  - activo: 1 o 0
     *  - observacion: texto
     *  - items: arreglo de item con campos:
     *    - descripcion: string (máx 1000 chars)
     *    - cantidad: real
     *    - descuento: real
     *    - descuento_tipo (% o $)
     * - referencias: arreglo de referencias con campos:
     *    - documento
     *    - folio
     *    - fecha
     *    - descripcion
     *
     * IMPORTANTE: para eliminar un item marcarlo con cantidad = 0.
     * No es posible eliminar vía servicios web items obligatorios.
     */
    public function test_pagos_guardar_cobro_masivo_programado(): void
    {

        try {
            $cobros = $this->_buscar();
            $resource = '/pagos/cobro_masivo_programados/guardar';
            $datos = [
                // datos obligatorios
                'emisor' => self::$emisor_rut,
                'cobro_masivo_codigo' => $cobros[0]['masivo_codigo'],
                'receptor' => explode('-', $cobros[0]['rut'])[0],
                // datos que varían, acá se guarda una referencia
                // pero podría ser modificados los items o la fecha de siguiente cobro
                'referencias' => [
                    [
                        'documento' => 801,
                        'folio' => 123,
                        'fecha' => date('Y-m-d'), // =false se elimina la referencia
                        'descripcion' => 'REF A OC',
                    ],
                ],
            ];
            $response = self::$client->post($resource, $datos);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() resultado ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Prueba unitaria que muestra los pasos para:
     *  - Emitir un cobro masivo, emitiendo y enviando por correo, cada uno de
     *    los documentos generados (si así está configurado)
     */
    public function test_pagos_emitir_cobro_masivo_programado(): void
    {
        try {
            $cobros = $this->_buscar();
            $resource = sprintf(
                '/pagos/cobro_masivo_programados/emitir/%s/%d/%d',
                $cobros[0]['masivo_codigo'],
                explode('-', $cobros[0]['rut'])[0],
                self::$emisor_rut
            );
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() resultado ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
