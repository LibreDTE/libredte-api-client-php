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
class EmitirDteTemporalTest extends TestCase
{
    # TODO: Revisar código de testing de DTE Temporal.
    protected static $verbose;

    protected static $client;

    protected static $emisor_rut;

    private static $datos = [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
            ],
            'Emisor' => [
                'RUTEmisor' => null, // se reemplaza al preparar la clase
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
            ],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        self::$verbose = env('TEST_VERBOSE', false);
        self::$emisor_rut = (explode('-', (string)env('LIBREDTE_RUT'))[0]);
        self::$datos['Encabezado']['Emisor']['RUTEmisor'] = env('LIBREDTE_RUT');
        self::$client = new ApiClient();
    }

    private function _buscar_temps(): array
    {
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
        ];
        $resource = sprintf('/dte/dte_tmps/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != 200) {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        return $response['body'];
    }

    public function test_dte_temp()
    {
        $lista_dtes = $this->_buscar_temps();
        if (self::$verbose) {
            echo "\n"."test_dte_buscar_temps()\n";
            foreach ($lista_dtes as $dte) {
                echo "DTE: ".'T'.$dte['dte'].'F'.$dte['codigo']."\n";
            }
        }
        $dte_temporal = $this->_emitir_dte_temporal();
        $info_dte = $this->_buscar_documento_temporal($lista_dtes);
        $this->_descargar_pdf_temp($dte_temporal);
        $cobro = $this->_buscar_cobro_asociado_dte_temp($dte_temporal);
        $this->_buscar_datos_cobro_con_estado($cobro);
        $this->_eliminar_dte_temporal($dte_temporal);
    }

    private function _buscar_documento_temporal($lista_dte): array
    {
        try {
            $documento = $lista_dte[0];

            $resource = sprintf(
                '/dte/dte_tmps/info/%d/%d/%s/%d',
                $documento['receptor'],
                $documento['dte'],
                $documento['codigo'],
                self::$emisor_rut,
            );

            $response = self::$client->get($resource);

            $this->assertTrue(true);
            if (self::$verbose) {
                $dte_id = 'T'.$documento['dte'].'F'.$documento['codigo'];
                echo "\n",'test_dte_buscar_documento_temporal() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_buscar_documento_temporal() dte_fecha ',$documento['fecha'],"\n";
                echo "\n",'test_dte_buscar_documento_temporal() dte_total ',$documento['total'],"\n";
            }
            return $response['body'];
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _emitir_dte_temporal(): array
    {
        try {
            $response = self::$client->post('/dte/documentos/emitir', self::$datos);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_dte_temp() emitir_dte_temporal ',json_encode($response['body']),"\n";
            }
            return $response['body'];
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _descargar_pdf_temp(array $dte_temp): void
    {
        $resource = sprintf(
            '/dte/dte_tmps/pdf/%d/%d/%s/%d',
            $dte_temp['receptor'],
            $dte_temp['dte'],
            $dte_temp['codigo'],
            $dte_temp['emisor'],
        );
        try {
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            $filename = __DIR__ . '/' .str_replace('.php', '.pdf', basename(__FILE__));
            file_put_contents($filename, $response['body']);
            unlink($filename);
            if (self::$verbose) {
                echo "\n",'test_dte_temp() pdf ',$filename,"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _buscar_cobro_asociado_dte_temp(array $dte_temp)
    {
        $resource = sprintf(
            '/dte/dte_tmps/cobro/%d/%d/%s/%d',
            $dte_temp['receptor'],
            $dte_temp['dte'],
            $dte_temp['codigo'],
            $dte_temp['emisor'],
        );
        try {
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_dte_temp() cobro_dte_temp ',json_encode($response['body']),"\n";
            }
            return $response['body'];
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _buscar_datos_cobro_con_estado(array $cobro)
    {
        $resource = sprintf(
            '/pagos/cobros/info/%s/%d',
            $cobro['codigo'],
            $cobro['emisor'],
        );
        try {
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_dte_temp() datos_cobro ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _eliminar_dte_temporal(array $dte_temp)
    {
        $resource = sprintf(
            '/dte/dte_tmps/eliminar/%d/%d/%s/%d',
            $dte_temp['receptor'],
            $dte_temp['dte'],
            $dte_temp['codigo'],
            $dte_temp['emisor'],
        );
        try {
            $response = self::$client->get($resource);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_dte_temp() eliminar_dte ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
