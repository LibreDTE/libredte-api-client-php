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

namespace libredte\pagos_cobros_masivos;

use libredte\api_client\ApiClient;
use libredte\api_client\ApiException;
use PHPUnit\Framework\TestCase;

abstract class AbstractPagosCobrosMasivos extends TestCase
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
     * Método para buscar un cobro.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array Arreglo que contiene el resultado de los cobros entre 2015 y hoy.
     */
    protected function listarCobros(): array
    {
        # Se crea la lista con filtros para aplicar a la búsqueda.
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
            'pagado' => false
        ];
        # Se genera el recurso a consumir.
        $resource = sprintf('/pagos/cobros/buscar/%d', self::$emisor_rut);
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post($resource, $filtros);

        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        # Si el body de la respuesta es vacío o nulo, arroja error
        # ApiException.
        if (empty($response['body'])) {
            throw new ApiException('No se encontraron cobros para la búsqueda realizada.', 404);
        }
        return $response;
    }

    /**
     * Método para emitir un DTE temporal.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array DTE temporal generado.
     */
    private function emitirDteTemp(): array
    {
        # Datos del DTE temporal a emitir.
        $datos = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                ],
                'Emisor' => [
                    'RUTEmisor' => self::$emisor_rut,
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

        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post('/dte/documentos/emitir', $datos);

        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] !== '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }

        return $response;
    }

    /**
     * Método de test para obtener un cobro a partir de un DTE temporal.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array Cobro asociado a un DTE temporal recién emitido.
     */
    protected function obtenerCobroDteTemp(): array
    {
        # Se emite un documento temporal para obtener su cobro
        $documento = $this->emitirDteTemp();

        # Se genera el recurso a consumir.
        $resource = sprintf(
            '/dte/dte_tmps/cobro/%d/%d/%s/%d',
            $documento['body']['receptor'],
            $documento['body']['dte'],
            $documento['body']['codigo'],
            self::$emisor_rut
        );
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->get($resource);
        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }

        return $response;
    }
}