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
class AsientoContableTest extends TestCase
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

    // este es un ejemplo de una venta, puede ser cualquier tipo de asiento contable
    // las cuentas contables usadas deben existir previamente, se asignan en las variable
    // de entorno si no son los códigos acá propuestos
    private function _datos_asiento(): array
    {
        $cuenta_caja = env('TEST_LCE_CUENTA_CAJA', 1101001);
        $cuenta_ventas = env('TEST_LCE_CUENTA_VENTAS', 4101001);
        $cuenta_iva_debito = env('TEST_LCE_CUENTA_IVA_DEBITO', 2105101);
        return [
            'fecha' => date('Y-m-d'),
            'glosa' => 'Venta T33F123',
            'detalle' => [
                'debe' => [
                    // cuenta: caja
                    $cuenta_caja => 119,
                ],
                'haber' => [
                    // cuenta: ventas
                    $cuenta_ventas => 100,
                    // cuenta: iva débito
                    $cuenta_iva_debito => 19,
                ],
            ],
            'operacion' => 'I',
            // esto es opcional, pero se recomienda ya que el SII lo puede pedir
            // además que permite usar un informe que indica qué documentos no
            // tienen asientos contables asociados (para cuadraturas)
            'documentos' => ['emitidos' => [['dte' => 33, 'folio' => 123]]],
        ];
    }

    public function test_lce_crear_asiento(): void
    {
        $resource = sprintf('/lce/lce_asientos/crear/%d', self::$emisor_rut);
        $datos = $this->_datos_asiento();
        try {
            $response = self::$client->post($resource, $datos);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_lce_crear_asiento() asiento ',$response['body']['asiento'],"\n";
                echo "\n",'test_lce_crear_asiento() creado ',$response['body']['creado'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _buscar(): array
    {
        $filtros = [
            'fecha_desde' => date('Y-m-d'),
            'fecha_hasta' => date('Y-m-d'),
        ];
        $resource = sprintf('/lce/lce_asientos/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        return $response['body'];
    }

    public function test_lce_buscar_asiento(): void
    {
        try {
            $asientos = $this->_buscar();
            $this->assertTrue(true);
            if (self::$verbose) {
                echo "\n",'test_lce_buscar_asientos() n_asientos ',count($asientos),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    // En la edición de un asiento todos los campos son opcionales, se debe
    // mandar a lo menos uno. Acá por simpleza se toman los mismos datos originales
    // ya que de esta forma el asiento que se encuentre (que no necesariamente es el creado)
    // se editará y "tendrá sentido" al ser igual al creado.
    public function test_lce_editar_asiento(): void
    {
        $datos = $this->_datos_asiento();
        $datos['glosa'] = 'VENTA CON FACTURA EDITADA';
        try {
            $asientos = $this->_buscar();
            $url = sprintf(
                '/lce/lce_asientos/editar/%d/%d/%d',
                $asientos[0]['periodo'],
                $asientos[0]['asiento'],
                self::$emisor_rut
            );
            $response = self::$client->post($url, $datos);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_lce_editar_asiento() asiento ',$response['body']['asiento'],"\n";
                echo "\n",'test_lce_editar_asiento() creado ',$response['body']['creado'],"\n";
                echo "\n",'test_lce_editar_asiento() modificado ',$response['body']['modificado'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
