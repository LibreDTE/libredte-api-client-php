Instalación
===========

Desde Composer:

Para obtener el cliente de la API, debe tener composer instalado, y ejecutar el siguiente comando en consola para instalar el cliente de API en PHP:

.. code-block:: shell
    composer require libredte/libredte-api-client

Desde GitHub:

Si estás utilizando la versión de GitHub, deberás instalar los paquetes necesarios para su funcionamiento. Para ello debes ejecutar lo siguiente en su lugar desde la carpeta del proyecto:

.. code-block:: shell
    composer install

.. important::
    Si el segundo paso no funciona, remueve composer.lock y vuelve a ejecutar composer install.

Requerimientos
--------------

- PHP 7.3 o superior.

- Extensiones de PHP:
    - curl.
    - ext-curl.
    - guzzlehttp.
    - phpdotenv.
    - phpdocumentor.