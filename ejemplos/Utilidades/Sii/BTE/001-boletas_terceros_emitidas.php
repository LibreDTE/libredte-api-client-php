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
 *  - Obtener listado de boletas de terceros electrónicas emitidas en el SII de un contribuyente (formato CSV o JSON).
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-11-11
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = '76192083-9';
$periodo = 201808; // "Ym" o sólo "Y"
$formato = 'csv'; // csv o json
$contrasenia = ''; // contraseña del emisor en el SII

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener boletas de terceros emitidas en el SII
$emitidas = $LibreDTE->post('/utilidades/sii/boletas_terceros_emitidas/'.$emisor.'/'.$periodo.'?formato='.$formato, [
    'auth'=>[
        'rut' => $emisor,
        'clave' => $contrasenia,
    ],
]);
if ($emitidas['status']['code']!=200) {
    die('Error al obtener boletas de terceros emitidas en el SII: '.$emitidas['body']."\n");
}

// guardar datos en el disco
if ($formato=='csv') {
    file_put_contents(str_replace('.php', '.csv', basename(__FILE__)), $emitidas['body']);
} else {
    file_put_contents(str_replace('.php', '.json', basename(__FILE__)), json_encode($emitidas['body'], JSON_PRETTY_PRINT));
}
