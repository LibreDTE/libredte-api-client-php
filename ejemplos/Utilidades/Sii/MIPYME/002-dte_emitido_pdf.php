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
 *  - Obtener el PDF de un DTE emitido en el Portal MIPYME del SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-02-03
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = '16261063-5'; // RUT para login en SII
$contrasenia = ''; // contraseña para login en SII
$contribuyente = '76499550-3'; // contribuyente que se desea consultar en SII
$dte = 34; // código del DTE o bien el código del PDF en el SII (ver listado de documentos recibidos)
$folio = 100; // si se indica el código del DTE, se debe indicar folio. Si se indica código del PDF se puede omitir el folio

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener PDF del DTE emitido en el Portal MIPYME del SII
$pdf = $LibreDTE->post('/utilidades/sii/mipyme_dte_emitido_pdf/'.$contribuyente.'/'.$dte.'/'.$folio, [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
]);
if ($pdf['status']['code']!=200) {
    die('Error al obtener el PDF del DTE emitido en Portal MIPYME del SII: '.$pdf['body']."\n");
}

// guardar datos en el disco
file_put_contents(str_replace('.php', '.pdf', basename(__FILE__)), $pdf['body']);
