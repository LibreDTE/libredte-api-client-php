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
use libredte\pagos_cobros_masivos\AbstractPagosCobrosMasivos;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiClient::class)]
#[CoversClass(HttpCurlClient::class)]
/**
 * Clase para ejecutar tests a los servicios de cobros masivos.
 */
class EmitirCobroMasivoProgramadoTest extends AbstractPagosCobrosMasivos
{
    /**
     * Prueba unitaria que muestra los pasos para:
     *  - Emitir un cobro masivo, emitiendo y enviando por correo, cada uno de
     *    los documentos generados (si así está configurado)
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testEmitirCobroMasivoProgramado(): void
    {
        try {
            // Búsqueda de cobros masivos programados.
            $cobros = $this->listarCobrosMasivosProgramados();
            // Recurso a consumir.
            $resource = sprintf(
                '/pagos/cobro_masivo_programados/emitir/%s/%d/%d',
                $cobros['body'][0]['masivo_codigo'],
                explode('-', $cobros['body'][0]['rut'])[0],
                self::$emisor_rut
            );
            // Se envía la solicitud http y se guarda su respuesta.
            $response = self::$client->get($resource);
            // Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] != '200') {
                throw new ApiException(
                    message: $response['body'],
                    code: (int)$response['status']['code']
                );
            }
            // Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);
            // Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",
                'testEmitirCobroMasivoProgramado() Cobro: ',
                json_encode(
                    $response['body']
                ),"\n";
            }
        } catch (ApiException $e) {
            // Si falla, desplegará el mensaje y error en el siguiente formato:
            // [ApiException codigo-http] mensaje]
            $this->fail(sprintf(
                '[ApiException %d] %s',
                $e->getCode(),
                $e->getMessage()
            ));
        }
    }
}
