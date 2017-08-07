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
 *  - Obtener listado de contribuyentes autorizados a facturar electrónicamente (formato CSV o JSON).
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$dia = date('Y-m-d');
$formato = 'csv'; // csv o json
$certificacion = 0; // =1 certificación, =0 producción

// incluir autocarga de composer
require('../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener datos de contribuyentes
$datos = $LibreDTE->get('/utilidades/sii/contribuyentes_autorizados/'.$dia.'?formato='.$formato.'&certificacion='.$certificacion);
if ($datos['status']['code']!=200) {
    die('Error al obtener lo datos de los contribuyentes: '.$datos['body']."\n");
}

// guardar datos en el disco
if ($formato=='csv') {
    file_put_contents(str_replace('.php', '.csv', basename(__FILE__)), $datos['body']);
} else {
    file_put_contents(str_replace('.php', '.json', basename(__FILE__)), json_encode($datos['body'], JSON_PRETTY_PRINT));
}
