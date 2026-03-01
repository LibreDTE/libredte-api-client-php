Pruebas unitarias
=================

.. important::
  Al ejecutar pruebas, deberás tener configuradas las variables de entorno necesarias en el archivo test.env. Favor de duplicar test.env-dist, cambiar su nombre a test.env y rellenar las variables necesarias.

Antes de empezar, debes configurar las siguientes variables de entorno:

.. code-block:: shell
    LIBREDTE_URL="https://libredte.cl"
    LIBREDTE_HASH="hash-libredte"
    LIBREDTE_RUT="66666666-6"

Para ejecutar las pruebas unitarias se necesita tener instaladas las dependencias de composer, y para hacer todas las pruebas, ejecutar lo siguiente:

.. code-block:: shell
    ./vendor/bin/phpunit

También es posible ejecutar una prueba específica indicando el test. Ejemplo:

.. code-block:: shell
    ./vendor/bin/phpunit --filter testEmitirDteTemp --no-coverage
