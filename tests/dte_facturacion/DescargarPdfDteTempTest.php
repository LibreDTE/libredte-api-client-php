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
use libredte\dte_facturacion\AbstractDteFacturacion;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiClient::class)]
#[CoversClass(HttpCurlClient::class)]
/**
 * Clase de pruebas para descargar un PDF de un DTE temporal.
 */
class DescargarPdfDteTempTest extends AbstractDteFacturacion
{
    /**
     * Método de test que prueba el servicio de obtener un PDF de un DTE
     * temporal.
     *
     * @throws \libredte\api_client\ApiException
     *
     * @return void
     */
    public function testDescargarPdfDteTemp(): void
    {
        try {
            // Se emite un DTE temporal para ejecutar esta prueba.
            $emitir = $this->emitirDteTemp();
            $documento = $emitir['body'];

            // Se genera el recurso a consumir.
            $resource = sprintf(
                '/dte/dte_tmps/pdf/%d/%d/%s/%d',
                $documento['receptor'],
                $documento['dte'],
                $documento['codigo'],
                self::$emisor_rut,
            );
            // Se envía la solicitud http y se guarda su respuesta.
            $response = self::$client->get($resource);

            // Si el código http no es '200', arroja error ApiException.
            if ($response['status']['code'] !== '200') {
                throw new ApiException(
                    message: $response['body'],
                    code: (int)$response['status']['code']
                );
            }
            // Se compara el código con '200' Si no es 200, la prueba falla.
            $this->assertSame('200', $response['status']['code']);

            // Ruta base para el directorio actual (archivo ejecutándose en
            // "tests/dte_facturacion")
            $currentDir = __DIR__;

            // Nueva ruta relativa para guardar el archivo PDF en "tests/archivos"
            $targetDir = dirname($currentDir) .
            '/archivos/dte_facturacion';

            // Define el nombre del archivo PDF en el nuevo directorio
            $filename = $targetDir . '/' . sprintf(
                'LIBREDTE_%d_%d-%s.pdf',
                self::$emisor_rut,
                $documento['dte'],
                $documento['codigo']
            );

            // Verifica si el directorio existe, si no, créalo
            if (!is_dir($targetDir)) {
                mkdir(directory: $targetDir, permissions: 0777, recursive: true);
            }

            // Se genera el archivo PDF.
            file_put_contents($filename, $response['body']);

            // Se despliega en consola los resultados si verbose es true.
            if (self::$verbose) {
                echo "\n",'testDescargarPdfTemp() PDF: ',$filename,"\n";
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
