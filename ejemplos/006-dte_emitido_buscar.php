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
 *  - Buscar DTE emitidos
 * Acá se usó sólo el filtro 'desde' y 'hasta' pero hay más filtros en:
 *   https://doc.libredte.cl/api/#!/DteEmitidos/post_dte_dte_emitidos_buscar_emisor
 * Si se requiere el detalle de cada DTE se puede consultar con:
 *   https://doc.libredte.cl/api/#!/DteEmitidos/get_dte_dte_emitidos_info_dte_folio_emisor
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-11
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = 76192083;
$desde = date('Y-m-01');
$hasta = date('Y-m-d');

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// hacer la búsqueda de los DTEs
$datos = [
    'fecha_desde' => $desde,
    'fecha_hasta' => $hasta,
];
$buscar = $LibreDTE->post('/dte/dte_emitidos/buscar/'.$rut, $datos);
if ($buscar['status']['code']!=200) {
    die('Error al realizar la búsqueda de DTEs emitidos: '.$buscar['body']."\n");
}
print_r($buscar['body']);
