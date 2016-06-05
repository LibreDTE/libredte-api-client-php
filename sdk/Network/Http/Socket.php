<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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

namespace sasco\LibreDTE\SDK\Network\Http;

/**
 * Clase para manejar conexiones HTTP
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2016-01-15
 */
class Socket
{

    protected static $methods = ['get', 'put', 'patch', 'delete', 'post']; ///< Métodos HTTP soportados
    protected static $header = [
        'User-Agent' => 'SowerPHP Network_Http_Socket',
        //'Content-Type' => 'application/x-www-form-urlencoded',
    ]; ///< Cabeceras por defecto
    protected static $errors = []; ///< Arrglo para errores de cURL

    /**
     * Método para ejecutar una solicitud a una URL, es la función que realmente
     * contiene las implementaciones para ejecutar GET, POST, PUT, DELETE, etc
     * @param method Método HTTP que se requiere ejecutar sobre la URL
     * @param url URL donde se enviarán los datos
     * @param data Datos que se enviarán
     * @param header Cabecera que se enviará
     * @param sslv3 =true se fuerza sslv3, por defecto es false
     * @return Respusta HTTP (cabecera y cuerpo)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-12-18
     */
    public static function __callStatic($method, $args)
    {
        if (!isset($args[0]) or !in_array($method, self::$methods))
            return false;
        $method = strtoupper($method);
        $url = $args[0];
        $data = isset($args[1]) ? $args[1] : [];
        $header = isset($args[2]) ? $args[2] : [];
        $sslv3 = isset($args[3]) ? $args[3] : false;
        $sslcheck = isset($args[4]) ? $args[4] : true;
        // inicializar curl
        $curl = curl_init();
        // asignar método y datos dependiendo de si es GET u otro método
        if ($method=='GET') {
            if (is_array($data))
                $data = http_build_query($data);
            if ($data) $url = sprintf("%s?%s", $url, $data);
        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        // asignar cabecera
        $header = array_merge(self::$header, $header);
        foreach ($header as $key => &$value) {
            $value = $key.': '.$value;
        }
        // asignar cabecera
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // realizar consulta a curl recuperando cabecera y cuerpo
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $sslcheck);
        if ($sslv3) {
            curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        }
        $response = curl_exec($curl);
        if (!$response) {
            self::$errors[] = curl_error($curl);
            return false;
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // cerrar conexión de curl y entregar respuesta de la solicitud
        $header = self::parseHeader(substr($response, 0, $header_size));
        curl_close($curl);
        return [
            'status' => self::parseStatus($header[0]),
            'header' => $header,
            'body' => substr($response, $header_size),
        ];
    }

    /**
     * Método que procesa la cabecera en texto plano y la convierte a un arreglo
     * con los nombres de la cabecera como índices y sus valores.
     * Si una cabecera aparece más de una vez, por tener varios valores,
     * entonces dicha cabecerá tendrá como valor un arreglo con todos sus
     * valores.
     * @param header Cabecera HTTP en texto plano
     * @return Arreglo asociativo con la cabecera
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-03
     */
    private static function parseHeader($header)
    {
        $headers = [];
        $lineas = explode("\n", $header);
        foreach ($lineas as &$linea) {
            $linea = trim($linea);
            if (!isset($linea[0])) continue;
            if (strpos($linea, ':')) {
                list($key, $value) = explode(':', $linea, 2);
            } else {
                $key = 0;
                $value = $linea;
            }
            $key = trim($key);
            $value = trim($value);
            if (!isset($headers[$key])) {
                $headers[$key] = $value;
            } else if (!is_array($headers[$key])) {
                $aux = $headers[$key];
                $headers[$key] = [$aux, $value];
            } else {
                $headers[$key][] = $value;
            }
        }
        return $headers;
    }

    /**
     * Método que procesa la línea de respuesta y extrae el protocolo, código de
     * estado y el mensaje del estado
     * @param response_line
     * @return Arreglo con índices: protocol, code, message
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-03
     */
    private static function parseStatus($response_line)
    {
        if (is_array($response_line)) {
            $response_line = $response_line[count($response_line)-1];
        }
        list($protocol, $status, $message) = explode(' ', $response_line, 3);
        return [
            'protocol' => $protocol,
            'code' => $status,
            'message' => $message,
        ];
    }

    /**
     * Método que entrega los errores ocurridos
     * @return Arreglo con los strings de los errores de cURL
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-15
     */
    public static function getErrors()
    {
        return self::$errors;
    }

    /**
     * Método que entrega el último error de cURL
     * @return Arreglo con los strings de los errores de cURL
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-15
     */
    public static function getLastError()
    {
        return self::$errors[count(self::$errors)-1];
    }

}
