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
 * Clase para un cliente de APIs REST
 * Permite manejar solicitudes y respuestas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2016-01-15
 */
class Rest
{

    /** @var array $methods Métodos HTTP soportados */
    protected $methods = ['get', 'put', 'patch', 'delete', 'post'];
    /** @var array $config Configuración para el cliente REST */
    protected $config;
    /** @var array $header Cabecera que se enviará */
    protected $header;
    /** @var array $errors Errores de la consulta REST */
    protected $errors = [];

    /**
     * Constructor del cliente REST
     * @param array $config Arreglo con la configuración del cliente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-02
     */
    public function __construct($config = [])
    {
        // cargar configuración de la solicitud que se hará
        if (!is_array($config))
            $config = ['base'=>$config];
        $this->config = array_merge([
            'base' => '',
            'user' => null,
            'pass' => 'X',
        ], $config);
        // crear cabecera para la solicitud que se hará
        $this->header['User-Agent'] = 'SowerPHP Network_Http_Rest';
        $this->header['Content-Type'] = 'application/json';
        if ($this->config['user']!==null) {
            $this->header['Authorization'] = 'Basic '.base64_encode(
                $this->config['user'].':'.$this->config['pass']
            );
        }
    }

    /**
     * Método que asigna la autenticación para la API REST
     * @param string $user Usuario (o token) con el que se está autenticando
     * @param string $pass Contraseña con que se está autenticando (se omite si se usa token)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-02
     */
    public function setAuth($user, $pass = 'X')
    {
        $this->config['user'] = $user;
        $this->config['pass'] = $pass;
        $this->header['Authorization'] = 'Basic '.base64_encode(
            $this->config['user'].':'.$this->config['pass']
        );
    }

    /**
     * Método para realizar solicitud al recurso de la API
     * @param string $method Nombre del método que se está ejecutando
     * @param array $args Argumentos para el método de \sasco\LibreDTE\SDK\Network\Http\Socket
     * @return array|boolean Arreglo con la respuesta HTTP (índices: status, header y body)
     *                       $params = [
     *                           'status' => (integer) Código (estado) de respuesta HTTP.
     *                           'header' => (array) Cabeceras de la respuesta HTTP.
     *                           'body'   => (string) Cuerpo de la respuesta HTTP.
     *                       ]
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-06-04
     */
    public function __call($method, $args)
    {
        if (!isset($args[0]) or !in_array($method, $this->methods))
            return false;
        $resource = $args[0];
        $data = isset($args[1]) ? $args[1] : [];
        $header = isset($args[2]) ? $args[2] : [];
        $sslv3 = isset($args[3]) ? $args[3] : false;
        $sslcheck = isset($args[4]) ? $args[4] : true;
        if ($data and $method!='get') {
            if (isset($data['@files'])) {
                $files = $data['@files'];
                unset($data['@files']);
                $data = ['@data' => json_encode($data)];
                foreach ($files as $key => $file)
                    $data[$key] = $file;
            } else {
                $data = json_encode($data);
                $header['Content-Length'] = strlen($data);
            }
        }
        $response = Socket::$method(
            $this->config['base'].$resource,
            $data,
            array_merge($this->header, $header),
            $sslv3,
            $sslcheck
        );
        if ($response === false) {
            $this->errors[] = Socket::getLastError();
            return false;
        }
        $body = json_decode($response['body'], true);
        return [
            'status' => $response['status'],
            'header' => $response['header'],
            'body' => $body!==null ? $body : $response['body'],
        ];
    }

    /**
     * Método que entrega los errores ocurridos al ejecutar la consulta a REST
     * @return array Arreglo con los errores
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-15
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
