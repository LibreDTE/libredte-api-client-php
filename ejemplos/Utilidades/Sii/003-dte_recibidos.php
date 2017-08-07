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
 *  - Obtener los documentos recibidos en el SII de un contribuyente (formato CSV o JSON).
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$receptor = '76192083-9';
$desde = date('Y-m-01');
$hasta = date('Y-m-d');
$formato = 'csv'; // csv o json
$certificacion = 0; // =1 certificación, =0 producción
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
];

// incluir autocarga de composer
require('../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener dte recibidos en el SII
$recibidos = $LibreDTE->post('/utilidades/sii/dte_recibidos/'.$receptor.'/'.$desde.'/'.$hasta.'?formato='.$formato.'&certificacion='.$certificacion, [
    'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]
]);
if ($recibidos['status']['code']!=200) {
    die('Error al obtener documentos recibibos en el SII: '.$recibidos['body']."\n");
}

// guardar datos en el disco
if ($formato=='csv') {
    file_put_contents(str_replace('.php', '.csv', basename(__FILE__)), $recibidos['body']);
} else {
    file_put_contents(str_replace('.php', '.json', basename(__FILE__)), json_encode($recibidos['body'], JSON_PRETTY_PRINT));
}
