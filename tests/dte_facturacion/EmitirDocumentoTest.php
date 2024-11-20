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
class EmitirDocumentoTest extends TestCase
{
    protected static $verbose;

    protected static $client;

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
            ]
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        self::$verbose = env('TEST_VERBOSE', false);
        self::$datos['Encabezado']['Emisor']['RUTEmisor'] = env('LIBREDTE_RUT');
        self::$client = new ApiClient();
    }

    public function test_dte_facturar()
    {
        $dte_temporal = $this->_emitir_dte_temporal();
        #$dte_emitido = $this->_generar_dte_emitido($dte_temporal); // AKA: dte real
        #$this->_descargar_pdf($dte_emitido);
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
                echo "\n",'test_dte_facturar() dte_temporal ',json_encode($response['body']),"\n";
            }
            return $response['body'];
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }

    }

    private function _generar_dte_emitido(array $dte_temporal): array
    {
        try {
            $response = self::$client->post('/dte/documentos/generar', $dte_temporal);
            if ($response['status']['code'] != '200') {
                throw new ApiException($response['body'], (int)$response['status']['code']);
            }
            $this->assertSame('200', $response['status']['code']);
            if (self::$verbose) {
                echo "\n",'test_dte_facturar() dte_emitido ',json_encode($response['body']),"\n";
            }
            return $response['body'];
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    private function _descargar_pdf(array $dte_emitido): void
    {
        $resource = sprintf(
            '/dte/dte_emitidos/pdf/%d/%d/%d',
            $dte_emitido['dte'],
            $dte_emitido['folio'],
            $dte_emitido['emisor'],
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
                echo "\n",'test_dte_facturar() pdf ',$filename,"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
