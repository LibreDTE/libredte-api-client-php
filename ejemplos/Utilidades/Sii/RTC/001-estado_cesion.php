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
 *  - Obtener el estado de una cesión
 *
 * El parámetro "clave" es opcional, si no se indica, cuando se consulta por un
 * DTE al que no tiene acceso el usuario autenticado sólo se indicará si está o
 * no cedido. Si se indica la clave o el usuario autentica tiene permiso para
 * acceder al DTE se mostrará toda la información de cesión.
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$contribuyente = '76192083-9';
$dte = 33;
$folio = 612;
$clave = ''; // la clave de la cesión es opcional
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
]; ///< Este servicio funciona sólo con firma electrónica

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener estado de la cesión desde el SII
$estado = $LibreDTE->post('/utilidades/sii/rtc_cesion_estado/'.$contribuyente.'/'.$dte.'/'.$folio.'/'.$clave, [
    'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]
]);
if ($estado['status']['code']!=200) {
    die('Error al obtener el estado de la cesión: '.$estado['body']."\n");
}

// mostrar estado
print_r($estado['body']);
