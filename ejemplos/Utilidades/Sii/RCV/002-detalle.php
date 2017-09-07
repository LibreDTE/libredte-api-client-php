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
 *  - Obtener el detalle de un período desde el RCV del SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$contribuyente = '76192083-9';
$operacion = 'COMPRA'; // 'COMPRA' o 'VENTA'
$periodo = 201709;
$dte = 33;
$estado = 'REGISTRO'; // Si es 'COMPRA': 'REGISTRO', 'PENDIENTE', 'NO_INCLUIR' o 'RECLAMADO'
$formato = 'json'; // csv o json (json entrega información extra)
$contrasenia = ''; // contraseña del receptor en el SII
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
]; ///< Este servicio funciona tanto con firma electrónica como con RUT/clave

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener detalle del registro de compra venta desde el SII
$detalle = $LibreDTE->post('/utilidades/sii/rcv_detalle/'.$contribuyente.'/'.$operacion.'/'.$periodo.'/'.$dte.'/'.$estado.'?formato='.$formato, [
    'auth'=>[
        'rut' => $contribuyente,
        'clave' => $contrasenia,
    ],
    /*'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]*/
]);
if ($detalle['status']['code']!=200) {
    die('Error al obtener el detalle del RCV: '.$detalle['body']."\n");
}

// guardar datos en el disco
if ($formato=='csv') {
    file_put_contents(str_replace('.php', '.csv', basename(__FILE__)), $detalle['body']);
} else {
    file_put_contents(str_replace('.php', '.json', basename(__FILE__)), json_encode($detalle['body'], JSON_PRETTY_PRINT));
}
