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
 *  - Emitir DTE temporal
 *  - Generar DTE real a partir del temporal
 *  - Obtener PDF a partir del DTE real
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-15
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$dte = [
    'Encabezado' => [
        'IdDoc' => [
            'TipoDTE' => 33,
        ],
        'Emisor' => [
            'RUTEmisor' => '76192083-9',
        ],
        'Receptor' => [
            'RUTRecep' => '66666666-6',
            'RznSocRecep' => 'Persona sin RUT',
            'GiroRecep' => 'Particular',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago',
        ],
    ],
    'Detalle' => [
        [
            'NmbItem' => 'Producto 1',
            'QtyItem' => 2,
            'PrcItem' => 1000,
        ],
    ],
];

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);
// $LibreDTE->setSSL(false, false); ///< segundo parámetro =false desactiva verificación de SSL

// crear DTE temporal
$emitir = $LibreDTE->post('/dte/documentos/emitir', $dte);
if ($emitir['status']['code']!=200) {
    die('Error al emitir DTE temporal: '.$emitir['body']."\n");
}

// crear DTE real
$generar = $LibreDTE->post('/dte/documentos/generar', $emitir['body']);
if ($generar['status']['code']!=200) {
    die('Error al generar DTE real: '.$generar['body']."\n");
}

// obtener el PDF del DTE
$generar_pdf = $LibreDTE->post('/dte/documentos/generar_pdf', ['xml'=>$generar['body']['xml']]);
if ($generar_pdf['status']['code']!=200) {
    die('Error al generar PDF del DTE: '.$generar_pdf['body']."\n");
}

// guardar PDF en el disco
file_put_contents(str_replace('.php', '.pdf', basename(__FILE__)), $generar_pdf['body']);
