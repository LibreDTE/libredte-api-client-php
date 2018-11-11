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

/**
 * Ejemplo que muestra los pasos para:
 *  - Descargar el HTML de una boleta de terceros electrónica.
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-11-11
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = '76192083-9';
$contrasenia = ''; // contraseña del receptor en el SII
$boleta = 'C76501677001686BA9A56'; // código de barras de la boleta

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener HTML de la boleta de honorario recibida en el SII
$html = $LibreDTE->post('/utilidades/sii/boleta_terceros_html/'.$boleta, [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
]);
if ($html['status']['code']!=200) {
    die('Error al obtener el HTML de la boleta de terceros desde el SII: '.$html['body']."\n");
}

// guardar datos en el disco
file_put_contents(str_replace('.php', '.html', basename(__FILE__)), $html['body']);
