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
 *  - Verificar los datos y situación actual de un DTE cualquiera
 * Permite verificar cualquier DTE, aunque no sea emitido por quien
 * hace la consulta. Esto es útil, por ejemplo, para verificar DTE
 * antes de hacer cesión.
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-21
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = '';
$receptor = '';
$dte = 33;
$folio = 0;
$fecha = '';
$total = 0;
$firma_dte = ''; // nodo SignatureValue del DTE (sin saltos de línea ni espacios)
$certificacion = 0; // =1 certificación, =0 producción

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// realizar la verificación del DTE
$verificacion = $LibreDTE->post('/utilidades/sii/dte_verificar?certificacion='.$certificacion, [
    'emisor' => $emisor,
    'receptor' => $receptor,
    'dte' => $dte,
    'folio' => $folio,
    'fecha' => $fecha,
    'total' => $total,
    'firma' => $firma_dte,
]);
if ($verificacion['status']['code']!=200) {
    die('Error al realizar la verificacion: '.$verificacion['body']."\n");
}
print_r($verificacion['body']);
