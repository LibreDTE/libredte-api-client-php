<?php

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
 * Clase ApiClient para la integración con la API de LibreDTE.
 *
 * Proporciona funcionalidades para realizar peticiones HTTP a la API de LibreDTE,
 * incluyendo métodos para realizar solicitudes GET y POST.
 */
class ApiClient
{

    /**
     * La URL base de la API de LibreDTE.
     *
     * @var string
     */
    private $api_url = 'https://libredte.cl';

    /**
     * El prefijo para las rutas de la API.
     *
     * @var string
     */
    private $api_prefix = '/api';

    /**
     * Valores por defecto de la cabecera que se pasarán a cURL.
     *
     * @var array
     */
    private $headers = [
        'User-Agent' => 'LibreDTE: Cliente de API en PHP.',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    /**
     * Objeto para manejar las conexiones HTTP mediante cURL.
     *
     * @var HttpCurlClient
     */
    private $client;

    /**
     * Constructor de la clase ApiClient.
     *
     * Inicializa el cliente con las credenciales y la URL de la API. Si no se proporcionan,
     * se intentará obtener desde las variables de entorno.
     *
     * @param string|null $hash Hash de autenticación del usuario en LibreDTE.
     * @param string|null $url URL base de la API de LibreDTE.
     * @throws ApiException si el hash de autenticación no está presente.
     */
    public function __construct($hash = null, $url = null)
    {
        $hash = $hash ?: $this->env('LIBREDTE_HASH');
        if (!$hash) {
            throw new ApiException('LIBREDTE_HASH missing');
        }
        $this->headers['Authorization'] = 'Basic ' . base64_encode(
            $hash . ':X'
        );
        $this->api_url = $url ?: $this->env('LIBREDTE_URL') ?: $this->api_url;
        $this->client = new HttpCurlClient();
    }

    /**
     * Establece una cabecera para las solicitudes HTTP.
     *
     * Permite definir un valor para una cabecera específica que se incluirá en todas
     * las solicitudes HTTP realizadas por la instancia del cliente.
     *
     * @param string $name Nombre de la cabecera.
     * @param mixed $value Valor de la cabecera.
     */
    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Configura las opciones de SSL para las conexiones HTTP.
     *
     * Este método permite activar o desactivar la verificación del certificado SSL
     * del servidor.
     *
     * @param boolean $sslcheck Activar o desactivar la verificación del certificado SSL.
     */
    public function setSSL($sslcheck = true)
    {
        $this->client->setSSL($sslcheck);
    }

    /**
     * Realiza una solicitud POST a la API de LibreDTE.
     *
     * Envia datos a un recurso específico de la API utilizando el método POST.
     *
     * @param string $resource El recurso de la API a solicitar.
     * @param mixed $data Los datos a enviar en la solicitud POST.
     * @param array $headers Encabezados adicionales para la solicitud.
     * @return array Respuesta de la API.
     */
    public function post($resource, $data = null, array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);
        return $this->client->query(
            'POST',
            $this->api_url . $this->api_prefix . $resource,
            $data,
            $headers
        );
    }

    /**
     * Realiza una solicitud GET a la API de LibreDTE.
     *
     * Recupera datos de un recurso específico de la API utilizando el método GET.
     *
     * @param string $resource El recurso de la API a solicitar.
     * @param mixed $data Los datos a enviar en la solicitud GET.
     * @param array $headers Encabezados adicionales para la solicitud.
     * @return array Respuesta de la API.
     */
    public function get($resource, $data = null, array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);
        return $this->client->query(
            'GET',
            $this->api_url . $this->api_prefix . $resource,
            $data,
            $headers
        );
    }

    /**
     * Obtiene el valor de una variable de entorno.
     *
     * Este método es utilizado internamente para obtener configuraciones
     * como el hash de autenticación o la URL base de la API.
     *
     * @param string $name Nombre de la variable de entorno.
     * @return string|null Valor de la variable de entorno o null si no está definida.
     */
    private function env($name)
    {
        return function_exists('env') ? env($name) : getenv($name);
    }

}
