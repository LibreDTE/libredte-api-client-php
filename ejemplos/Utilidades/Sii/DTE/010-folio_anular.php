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
 *  - Anular un folios en el SII (respuesta en formato web).
 *
 * Parámetros del servicio web (permite anular rango de folios):
 *  - EMISOR
 *  - DTE
 *  - FOLIO_ANULAR_INICIAL
 *  - FOLIO_ANULAR_FINAL
 *  - FOLIO_CAF_INICIAL
 *  - FOLIO_CAF_FINAL
 *
 * Los campos FOLIO_ANULAR_FINAL, FOLIO_CAF_INICIAL y FOLIO_CAF_FINAL son
 * opcionales, el SII no está verificando si se pasan los del inicio y fin del
 * CAF. Para anular un rango bastaría pasar FOLIO_ANULAR_INICIAL y
 * FOLIO_ANULAR_FINAL. De todas formas, siempre se recomienda incluir los folios
 * inicial y final del CAF para cumplir al 100% con lo que pide el SII.
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-07-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = '76192083-9';
$dte = 33;
$folio = 207;
$certificacion = 1; // =1 certificación, =0 producción
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
];

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// anular folio en el SII
$web = $LibreDTE->post('/utilidades/sii/folio_anular/'.$emisor.'/'.$dte.'/'.$folio.'?certificacion='.$certificacion, [
    'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]
]);
if ($web['status']['code']!=200) {
    die('Error al anular el folio en el SII: '.$web['body']."\n");
}

// guardar datos en el disco
file_put_contents(str_replace('.php', '.html', basename(__FILE__)), $web['body']);
