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

class BuscarDocumentoEmitidoTest extends TestCase
{

    protected static $verbose;
    protected static $client;
    protected static $emisor_rut;
    protected static $email;

    public static function setUpBeforeClass(): void
    {
        self::$verbose = env('TEST_VERBOSE', false);
        self::$emisor_rut = (int)(explode('-', (string)env('LIBREDTE_RUT'))[0]);
        self::$email = env('TEST_EMAIL');
        self::$client = new ApiClient();
    }

    private function _buscar(): array
    {
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
        ];
        $resource = sprintf('/dte/dte_emitidos/buscar/%d', self::$emisor_rut);
        $response = self::$client->post($resource, $filtros);
        if ($response['status']['code'] != 200) {
            throw new ApiException($response['body'], $response['status']['code']);
        }
        return $response['body'];
    }

    public function test_dte_buscar_documento_emitido(): void
    {
        try {
            $documentos = $this->_buscar();
            $this->assertTrue(true);
            if (self::$verbose) {
                $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];
                echo "\n",'test_dte_buscar_documento_emitido() n_documentos ',count($documentos),"\n";
                echo "\n",'test_dte_buscar_documento_emitido() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_buscar_documento_emitido() dte_fecha ',$documentos[0]['fecha'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_dte_estado(): void
    {
        try {
            $documentos = $this->_buscar();
            $resource = sprintf(
                '/dte/dte_emitidos/actualizar_estado/%d/%d/%d?usarWebservice=1',
                $documentos[0]['dte'],
                $documentos[0]['folio'],
                self::$emisor_rut
            );
            $response = self::$client->get($resource);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];
                echo "\n",'test_dte_estado() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_estado() dte_estado ',$response['body']['revision_estado'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_dte_consultar(): void
    {
        try {
            $documentos = $this->_buscar();
            $filtros = [
                'emisor' => self::$emisor_rut,
                'dte' => $documentos[0]['dte'],
                'folio' => $documentos[0]['folio'],
                'fecha' => $documentos[0]['fecha'],
                'total' => $documentos[0]['total'],
            ];
            $response = self::$client->post('/dte/dte_emitidos/consultar', $filtros);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];
                echo "\n",'test_dte_consultar() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_consultar() fecha_hora_creacion ',$response['body']['fecha_hora_creacion'],"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_dte_ted(): void
    {
        try {
            $documentos = $this->_buscar();
            $resource = sprintf(
                '/dte/dte_emitidos/ted/%d/%d/%d?formato=%s',
                $documentos[0]['dte'],
                $documentos[0]['folio'],
                self::$emisor_rut,
                'xml' // png (defecto), bmp o xml
            );
            $response = self::$client->get($resource);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];
                echo "\n",'test_dte_ted() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_ted() dte_ted ',base64_decode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    public function test_dte_email(): void
    {
        try {
            $documentos = $this->_buscar();
            $resource = sprintf(
                '/dte/dte_emitidos/enviar_email/%d/%d/%d',
                $documentos[0]['dte'],
                $documentos[0]['folio'],
                self::$emisor_rut
            );
            $datos_email = [
                'emails' => [self::$email],
                'asunto' => sprintf(
                    '[LibreDTE API Client Test] Envío de DTE T%dF%d de %d',
                    $documentos[0]['dte'],
                    $documentos[0]['folio'],
                    self::$emisor_rut
                ),
                'mensaje' => sprintf(
                    'LibreDTE API Client Test: DTE ID T%dF%d de %d.',
                    $documentos[0]['dte'],
                    $documentos[0]['folio'],
                    self::$emisor_rut
                ),
                'pdf' => true,
                'cedible' => true,
                'papelContinuo' => false,
            ];
            $response = self::$client->post($resource, $datos_email);
            if ($response['status']['code'] != 200) {
                throw new ApiException($response['body'], $response['status']['code']);
            }
            $this->assertEquals(200, $response['status']['code']);
            if (self::$verbose) {
                $dte_id = 'T'.$documentos[0]['dte'].'F'.$documentos[0]['folio'];
                echo "\n",'test_dte_email() dte_id ',$dte_id,"\n";
                echo "\n",'test_dte_email() email ',implode(', ', $response['body']),"\n";
            }
        } catch (ApiException $e) {
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

}
