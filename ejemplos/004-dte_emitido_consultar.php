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
 *  - Consultar por un DTE emitido, además verifica la fecha y el monto total (similar a lo que hace el SII)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-05
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = 76192083;
$dte = 33;
$folio = 394;
$fecha = '2017-08-04';
$total = 6731;
$getXML = 0;

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener el PDF del DTE
$datos = [
    'emisor' => $rut,
    'dte' => $dte,
    'folio' => $folio,
    'fecha' => $fecha,
    'total' => $total,
];
$consultar = $LibreDTE->post('/dte/dte_emitidos/consultar?getXML='.$getXML, $datos);
if ($consultar['status']['code']!=200) {
    die('Error al realizar la consulta del DTE emitido: '.$consultar['body']."\n");
}
print_r($consultar['body']);
