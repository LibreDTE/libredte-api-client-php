<?php

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

// asignar zona horaria de PHP
date_default_timezone_set('America/Santiago');

// dependencias de composer
require_once __DIR__ . '/../vendor/autoload.php';

// cargar variables de entorno
$dotenv = \Dotenv\Dotenv::createMutable(__DIR__, 'test.env');
try {
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die($e->getMessage());
} catch (\Dotenv\Exception\InvalidFileException $e) {
    die($e->getMessage());
}

/**
 * Función que carga una variable de entorno o su valor por defecto
 * @param varname Variable que se desea consultar
 * @param default Valor por defecto de la variable
 */
function env($varname, $default = null)
{
    if (isset($_ENV[$varname])) {
        return $_ENV[$varname];
    }
    $value = getenv($varname);
    if ($value !== false) {
        return $value;
    }
    return $default;
}
