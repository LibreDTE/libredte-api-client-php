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
 *  - Buscar entre los cobros de un contribuyente
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-06-02
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$filtros = [
    // filtrar por fecha emisión del cobro
    'fecha_desde' => date('Y-m-01'),
    'fecha_hasta' => date('Y-m-d'),
    // filtrar por receptor (RUT sin DV):
    //'receptor' => 55666777,
    // filtar por estado según vencimiento (el valor debe ser siempre true):
    //'vencidos' => true,
    //'vencen_hoy' => true,
    //'vigentes' => true,
    //'sin_vencimiento' => true,
    // filtrar por estado pagado o pendiente:
    //'pagado' => true, // =true o =false (sólo pagados o sólo pendientes de pago, no asignar para buscar todo)
];

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// crear DTE temporal
$buscar = $LibreDTE->post('/pagos/cobros/buscar/'.$emisor, $filtros);
if ($buscar['status']['code']!=200) {
    die('Error al realizar la búsqueda de los cobros: '.$buscar['body']."\n");
}
print_r($buscar['body']);
