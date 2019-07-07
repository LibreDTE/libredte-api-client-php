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
 *  - Obtener las cesiones de un periodo
 *
 * El parámetro "consulta" puede ser:
 *  0 -> Está obligado a pagar (Consulta para el Deudor)
 *  1 -> Ha cedido  (Consulta para el Cedente)
 *  2 -> Ha adquirido (Consulta para el Cesionario)
 *
 * Para acceder a la información entregada en esta opción, debe autenticarse:
 *  - Con Rut/Clave o Certificado Digital: un contribuyente relacionado con la
 *    cesión (como Deudor, Cedente o Cesionario)
 *  - Con Rut/Clave o Certificado Digital: una persona que haya sido autorizada
 *    para representar electrónicamente a algunos de los contribuyentes antes
 *    indicados
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$desde = '2019-07-01';
$hasta = '2019-07-30'; // el "hasta" no puede ser mayor a un mes después del "desde"
$consulta = 2; // 0, 1 o 2
$formato = 'json'; // json, xml, csv o txt
$contribuyente = ''; // rut usuario para autenticarse
$contrasenia = ''; // contraseña para autenticarse
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
]; ///< Este servicio funciona tanto con firma electrónica como con RUT/clave

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener cesiones de un período desde el SII
$cesiones = $LibreDTE->post('/utilidades/sii/rtc_cesiones_periodo/'.$desde.'/'.$hasta.'/'.$consulta.'/'.$formato, [
    /*'auth'=>[
        'rut' => $contribuyente,
        'clave' => $contrasenia,
    ],*/
    'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]
]);
if ($cesiones['status']['code']!=200) {
    die('Error al obtener las cesiones desde el SII: '.$cesiones['body']."\n");
}

// guardar cesiones en un archivo
if ($formato == 'json') {
    file_put_contents(str_replace('.php', '.'.$formato, basename(__FILE__)), json_encode($cesiones['body'], JSON_PRETTY_PRINT));
} else {
    file_put_contents(str_replace('.php', '.'.$formato, basename(__FILE__)), $cesiones['body']);
}
