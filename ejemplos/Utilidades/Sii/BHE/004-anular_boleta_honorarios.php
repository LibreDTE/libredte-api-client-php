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
 *  - Anular una boleta de honorarios electrónica
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-08-15
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = '11222333-4'; // rut usuario SII que anulará la boleta
$contrasenia = ''; // contraseña del usuario en el SII
$emisor = $rut;
$boleta = 139;
$causa = 3; // =1 no se pagó la boleta, =2 no se prestó el servicio, =3 error digitación

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// anular la boleta de honorarios electrónica en SII
$anular = $LibreDTE->post('/utilidades/sii/boleta_honorarios_anular/'.$emisor.'/'.$boleta.'/'.$causa, [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
]);
if ($anular['status']['code']!=200) {
    die('Error al anular la boleta de honorarios: '.$anular['body']."\n");
}
echo 'Boleta ',$boleta,' anulada',"\n";
