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
 *  - Emitir una boleta de terceros electrónica
 *  - Descargar el HTML de la boleta de terceros electrónica emitida
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-08-15
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = '76192083-9'; // rut usuario SII que emitirá la boleta
$contrasenia = ''; // contraseña del usuario en el SII
$boleta = [
    'Encabezado' => [
        'IdDoc' => [
            'FchEmis' => '2019-08-14', // opcional (default: día actual)
        ],
        'Emisor' => [
            'RUTEmisor' => '76192083-9',
        ],
        'Receptor' => [
            'RUTRecep' => '66666666-6',
            'RznSocRecep' => 'Receptor generico',
            'DirRecep' => 'Santa Cruz',
            'CmnaRecep' => 'Santa Cruz',
        ],
    ],
    'Detalle' => [
        [
            'NmbItem' => 'Prueba integracion LibreDTE 1',
            'MontoItem' => 50,
        ],
        [
            'NmbItem' => 'Prueba integracion LibreDTE 2',
            'MontoItem' => 100,
        ],
    ],
];

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// emitir la boleta de terceros electrónica en SII
$boleta_resultado = $LibreDTE->post('/utilidades/sii/boleta_terceros_emitir/', [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
    'boleta' => $boleta,
]);
if ($boleta_resultado['status']['code']!=200) {
    die('Error al emitir la boleta de terceros: '.$boleta_resultado['body']."\n");
}
print_r($boleta_resultado['body']);

// bonus: bajar el HTML (es igual al ejemplo 002)
$html = $LibreDTE->post('/utilidades/sii/boleta_terceros_html/'.$boleta_resultado['body']['Encabezado']['IdDoc']['CodigoBarras'], [
    'auth'=>[
        'rut' => $rut,
        'clave' => $contrasenia,
    ],
]);
if ($html['status']['code']!=200) {
    die('Error al obtener el HTML de la boleta de terceros desde el SII: '.$html['body']."\n");
}
file_put_contents(str_replace('.php', '.html', basename(__FILE__)), $html['body']);
