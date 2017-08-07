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
 *  - Solicitar timbraje electrónico nuevo (descarga archivo CAF).
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = '76192083-9';
$dte = 33;
$cantidad = 5; // si la cantidad es superior a la cantidad autorizada disponible, se obtiene el máximo posible
$certificacion = 1; // =1 certificación, =0 producción
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
];

// incluir autocarga de composer
require('../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// solicitar CAF al SII
$caf = $LibreDTE->post('/utilidades/sii/caf_solicitar/'.$emisor.'/'.$dte.'/'.$cantidad.'?certificacion='.$certificacion, [
    'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]
]);
if ($caf['status']['code']!=200) {
    die('Error al realizar el timbraje en el SII: '.$caf['body']."\n");
}

// guardar datos en el disco
file_put_contents(str_replace('.php', '.xml', basename(__FILE__)), $caf['body']);
