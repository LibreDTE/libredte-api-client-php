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
 * Clase para ejecutar tests a los servicios de cobros masivos.
 */
class CobroMasivoProgramadoTest extends TestCase
{
    /**
     * Variable para desplegar resultados.
     *
     * @var bool
     */
    protected static $verbose;

    /**
     * Variable de instanciación de API Client.
     *
     * @var ApiClient
     */
    protected static $client;

    /**
     * RUT del emisor sin DV a utilizar.
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
     * Método privado para listar cobros masivos programados.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return array Arreglo con los cobros masivos programados.
     */
    private function _buscar(): array
    {
        # Filtros para la petición http.
        $filtros = [
            'siguiente_desde' => date('Y-m-d'),
            'siguiente_hasta' => date('Y-m-d'),
            'activo' => true,
        ];
        # Recurso a consumir.
        $resource = sprintf('/pagos/cobro_masivo_programados/buscar/%d', self::$emisor_rut);
        # Se envía la solicitud http y se guarda su respuesta.
        $response = self::$client->post($resource, $filtros);
        # Si el código http no es '200', arroja error ApiException.
        if ($response['status']['code'] != '200') {
            throw new ApiException($response['body'], (int)$response['status']['code']);
        }
        # Si el body de la respuesta es vacío o nulo, arroja error ApiException.
        if (empty($response['body'])) {
            throw new ApiException('No se encontraron cobros masivos programados para la búsqueda realizada.', 404);
        }
        return $response['body'];
    }

    /**
     * Método para buscar un cobro masivo programado.
     *
     * @return void
     */
    public function testPagosBuscarCobroMasivoProgramado(): void
    {
        try {
            # Se listan cobros masivos programados.
            $cobros = $this->_buscar();
            # La prueba tendrá éxito si la búsqueda funciona.
            $this->assertTrue(true);
            # Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() n_cobros ',count($cobros),"\n";
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_buscar_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
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
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testPagosGuardarCobroMasivoProgramado(): void
    {

        try {
            # Búsqueda de cobros masivos.
            $cobros = $this->_buscar();
            # Recurso a consumir.
            $resource = '/pagos/cobro_masivo_programados/guardar';
            # Datos del cobro masivo.
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
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
                echo "\n",'test_pagos_guardar_cobro_masivo_programado() resultado ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Prueba unitaria que muestra los pasos para:
     *  - Emitir un cobro masivo, emitiendo y enviando por correo, cada uno de
     *    los documentos generados (si así está configurado)
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testPagosEmitirCobroMasivoProgramado(): void
    {
        try {
            # Búsqueda de cobros masivos programados.
            $cobros = $this->_buscar();
            # Recurso a consumir.
            $resource = sprintf(
                '/pagos/cobro_masivo_programados/emitir/%s/%d/%d',
                $cobros[0]['masivo_codigo'],
                explode('-', $cobros[0]['rut'])[0],
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
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() masivo_codigo ',$cobros[0]['masivo_codigo'],"\n";
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() rut ',$cobros[0]['rut'],"\n";
                echo "\n",'test_pagos_emitir_cobro_masivo_programado() resultado ',json_encode($response['body']),"\n";
            }
        } catch (ApiException $e) {
            # Si falla, desplegará el mensaje y error en el siguiente formato:
            # [ApiException codigo-http] mensaje]
            $this->fail(sprintf('[ApiException %d] %s', $e->getCode(), $e->getMessage()));
        }
    }
}
