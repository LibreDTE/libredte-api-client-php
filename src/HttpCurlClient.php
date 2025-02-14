<?php

declare(strict_types=1);

/**
 * LibreDTE: Cliente de API en PHP.
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

namespace libredte\api_client;

/**
 * Clase HttpCurlClient para realizar consultas HTTP utilizando cURL.
 *
 * Esta clase proporciona una interfaz para realizar peticiones HTTP, como GET
 * y POST, utilizando cURL. Ofrece configuración de SSL y manejo de errores de
 * cURL.
 */
class HttpCurlClient
{
    /**
     * Indica si se debe validar el certificado SSL del servidor.
     *
     * @var boolean
     */
    private $sslcheck = true;

    /**
     * Historial de errores de las consultas HTTP mediante cURL.
     *
     * @var array
     */
    private $errors = [];

    /**
     * Devuelve los errores ocurridos en las peticiones HTTP.
     *
     * Este método devuelve un array con los errores generados por cURL en las
     * peticiones HTTP realizadas.
     *
     * @return array Lista de errores de cURL.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Devuelve el último error ocurrido en una petición HTTP.
     *
     * Este método devuelve el último error generado por cURL en una petición
     * HTTP.
     *
     * @return string Descripción del último error de cURL.
     */
    public function getLastError(): string
    {
        return $this->errors[count($this->errors) - 1];
    }

    /**
     * Configura las opciones de SSL para las peticiones HTTP.
     *
     * Este método permite activar o desactivar la verificación del certificado
     * SSL del servidor.
     *
     * @param boolean $sslcheck Activar o desactivar la verificación del
     * certificado SSL.
     */
    public function setSSL(bool $sslcheck = true): void
    {
        $this->sslcheck = $sslcheck;
    }

    /**
     * Realiza una solicitud HTTP a una URL.
     *
     * Este método ejecuta una petición HTTP utilizando cURL y devuelve la
     * respuesta.
     * Soporta varios métodos HTTP como GET, POST, PUT, DELETE, etc.
     *
     * @param string $method Método HTTP a utilizar.
     * @param string $url URL a la que se realiza la petición.
     * @param mixed $data Datos a enviar en la petición.
     * @param array $headers Cabeceras HTTP a enviar.
     * @return array|false Respuesta HTTP o false en caso de error.
     */
    public function query(
        string $method,
        string $url,
        mixed $data = [],
        array $headers = []
    ): array|bool {
        // preparar datos
        if ($data && $method != 'GET') {
            if (isset($data['@files'])) {
                $files = $data['@files'];
                unset($data['@files']);
                $data = ['@data' => json_encode($data)];
                foreach ($files as $key => $file) {
                    $data[$key] = $file;
                }
            } else {
                $data = json_encode($data);
                $headers['Content-Length'] = strlen($data);
            }
        }
        // inicializar curl
        $curl = curl_init();
        // asignar método y datos dependiendo de si es GET u otro método
        if ($method == 'GET') {
            if (is_array($data)) {
                $data = http_build_query($data);
            }
            if ($data) {
                $url = sprintf("%s?%s", $url, $data);
            }
        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }
        // asignar cabecera
        foreach ($headers as $key => &$value) {
            $value = $key.': '.$value;
        }
        // asignar cabecera
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_values($headers));
        // realizar consulta a curl recuperando cabecera y cuerpo
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslcheck);
        $response = curl_exec($curl);
        if (!$response) {
            $this->errors[] = curl_error($curl);
            return false;
        }
        $headers_size = curl_getinfo(handle: $curl, option: CURLINFO_HEADER_SIZE);
        // cerrar conexión de curl
        curl_close($curl);
        // entregar respuesta de la solicitud
        $response_headers = $this->parseResponseHeaders(
            substr(
                string: $response,
                offset: 0,
                length: $headers_size
            )
        );
        $body = substr($response, $headers_size);
        $json = json_decode(json: $body, associative: true);
        return [
            'status' => $this->parseResponseStatus($response_headers[0]),
            'header' => $response_headers,
            'body' => $json !== null ? $json : $body,
        ];
    }

    /**
     * Método que procesa y convierte la cabecera en texto plano a un arreglo
     * asociativo.
     *
     * Convierte las cabeceras HTTP dadas en texto plano a un arreglo
     * asociativo. Si una cabecera aparece más de una vez, su valor será un
     * arreglo con todos sus valores.
     *
     * @param string $headers_txt Cabeceras HTTP en formato de texto plano.
     * @return array Arreglo asociativo con las cabeceras procesadas.
     */
    private function parseResponseHeaders(string $headers_txt): array
    {
        $headers = [];
        $lineas = explode("\n", $headers_txt);
        foreach ($lineas as &$linea) {
            $linea = trim($linea);
            if (!isset($linea[0])) {
                continue;
            }
            if (strpos($linea, ':')) {
                list($key, $value) = explode(
                    separator: ':',
                    string: $linea,
                    limit: 2
                );
            } else {
                $key = 0;
                $value = $linea;
            }
            $key = trim(strval($key));
            $value = trim($value);
            if (!isset($headers[$key])) {
                $headers[$key] = $value;
            } elseif (!is_array($headers[$key])) {
                $aux = $headers[$key];
                $headers[$key] = [$aux, $value];
            } else {
                $headers[$key][] = $value;
            }
        }
        return $headers;
    }

    /**
     * Método que procesa la línea de estado de la respuesta HTTP y extrae
     * información útil.
     *
     * Extrae el protocolo, el código de estado y el mensaje del estado de la
     * línea de respuesta HTTP.
     * Útil para entender y manejar la respuesta HTTP.
     *
     * @param array|string $response_line Línea de respuesta HTTP.
     * @return array Arreglo con información del estado, incluyendo protocolo,
     * código y mensaje.
     */
    private function parseResponseStatus(array|string $response_line): array
    {
        if (is_array($response_line)) {
            $response_line = $response_line[count($response_line) - 1];
        }
        $parts = explode(separator: ' ', string: $response_line, limit: 3);
        return [
            'protocol' => $parts[0],
            'code' => $parts[1],
            'message' => !empty($parts[2]) ? $parts[2] : null,
        ];
    }
}
