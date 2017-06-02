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
 *  - Descargar el PDF de un DTE emitido
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-04-19
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = 76192083;
$dte = 33;
$folio = 42;
$papelContinuo = 0; // =75 ó =80 para papel contínuo
$copias_tributarias = 1;
$copias_cedibles = 1;
$cedible = (int)(bool)$copias_cedibles; // =1 genera cedible, =0 no genera cedible

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// descargar PDF
$opciones = '?papelContinuo='.$papelContinuo.'&copias_tributarias='.$copias_tributarias.'&copias_cedibles='.$copias_cedibles.'&cedible='.$cedible;
$pdf = $LibreDTE->get('/dte/dte_emitidos/pdf/'.$dte.'/'.$folio.'/'.$rut.$opciones);
if ($pdf['status']['code']!=200) {
    die('Error al descargar el PDF del DTE emitido: '.$pdf['body']."\n");
}
file_put_contents('005-dte_emitido_pdf.pdf', $pdf['body']);
