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
 *  - Agregar un resumen al registro de ventas (ejemplo de boletas)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-12-21
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$contribuyente = '76192083-9';
$periodo = 201811;
$operacion = 'VENTA';
$resumen = [
    'det_tipo_doc' => 39,
    'det_nro_doc' => 25,
    'det_mnt_neto' => 100000,
    'det_mnt_iva' => 19000,
    'det_mnt_total' => 169000,
    'det_mnt_exe' => 50000,
];
$contrasenia = ''; // contraseña del receptor en el SII
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
]; ///< Este servicio funciona tanto con firma electrónica como con RUT/clave

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// asignar resumen de operacion de venta (en este ejemplo de boletas)
$r = $LibreDTE->post('/utilidades/sii/rcv_set_resumen/'.$contribuyente.'/'.$periodo.'/'.$operacion, [
    'auth'=>[
        'rut' => $contribuyente,
        'clave' => $contrasenia,
    ],
    'resumen' => $resumen,
    /*'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]*/
]);
if ($r['status']['code']!=200) {
    die('Error al asignar el resumen: '.$r['body']."\n");
}
