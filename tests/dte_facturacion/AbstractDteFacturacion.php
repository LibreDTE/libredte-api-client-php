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

namespace libredte\dte_facturacion;

use libredte\api_client\ApiClient;
use libredte\api_client\ApiException;
use PHPUnit\Framework\TestCase;

abstract class AbstractDteFacturacion extends TestCase
{
/**
     * Variable para desplegar resultados.
     *
     * @var bool
     */
    protected static $verbose;

    /**
     * Variable instancia del API Client.
     *
     * @var ApiClient
     */
    protected static $client;

    /**
     * RUT del emisor a utilizar.
     *
     * @var int
     */
    protected static $emisor_rut;

    /**
     * Datos para producir el DTE temporal.
     *
     * @var array
     */
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

    /**
     * Inicialización de variables y clases pre ejecución de tests.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$verbose = (bool)env('TEST_VERBOSE', 'false');
        self::$emisor_rut = (explode('-', (string)env('LIBREDTE_RUT'))[0]);
        self::$datos['Encabezado']['Emisor']['RUTEmisor'] = env('LIBREDTE_RUT');
        self::$client = new ApiClient();
    }

    /**
     * Método para listar DTEs temporales.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array Listado de DTEs temporales entre 2015 y hoy.
     */
    protected function listarDteTemp(): array
    {
        # Se crea el filtro a utilizar, en este caso fechas de búsqueda.
        $filtros = [
            'fecha_desde' => '2015-01-01',
            'fecha_hasta' => date('Y-m-d'),
        ];
        # Se genera el recurso a consumir.
        $resource = sprintf('/dte/dte_tmps/buscar/%d', self::$emisor_rut);
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post($resource, $filtros);

        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] !== '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }

        # Retorna la respuesta http.
        return $response;
    }

    /**
     * Método para emitir un DTE temporal.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array DTE temporal generado.
     */
    protected function emitirDteTemp(): array
    {
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post('/dte/documentos/emitir', self::$datos);

        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] !== '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }

        # Retorna la respuesta http.
        return $response;
    }
}