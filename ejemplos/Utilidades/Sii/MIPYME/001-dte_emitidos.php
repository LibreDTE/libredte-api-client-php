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
 *  - Obtener listado de DTE emitidos en el Portal MIPYME del SII
 * No existe el límite de 20 documentos que tiene el SII :)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-02-03
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = '16261063-5'; // RUT para login en SII
$contrasenia = ''; // contraseña para login en SII
$contribuyente = '76499550-3'; // contribuyente que se desea consultar en SII
$filtros = [
    'RUT_RECP' => '',  // rut sin DV
    'FOLIO' => '',
    'RZN_SOC' => '',
    'FEC_DESDE' => '',
    'FEC_HASTA' => '',
    'TPO_DOC' => '',
    'ESTADO' => '',
]; // filtros para hacer la búsqueda en SII, sin filtros se busca todo, sin límite de 20 DTE :)

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// obtener DTE emitidos en el Portal MIPYME del SII
$emitidos = $LibreDTE->post('/utilidades/sii/mipyme_dte_emitidos/'.$contribuyente, [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
    'filtros' => $filtros,
]);
if ($emitidos['status']['code']!=200) {
    die('Error al obtener DTE emitidos en Portal MIPYME del SII: '.$emitidos['body']."\n");
}

// guardar datos en el disco
file_put_contents(str_replace('.php', '.json', basename(__FILE__)), json_encode($emitidos['body'], JSON_PRETTY_PRINT));
