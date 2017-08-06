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
 *  - Guardar cambios en un cobro masivo programado a un receptor
 *
 * Esto permite aplicar un descuento o modificar la cantidad en el listado de items.
 * Por ejemplo, sirve para actualizar la cantidad de algo que se está cobrando (ej:
 * consumo de GB en una cuenta de hosting).
 *
 * Se puede editar todo el cobro, o sea los campos:
 *  - dte: código del DTE, ej: 33 (factura electrónica afecta)
 *  - dte_real: =1 emite DTE real, =0 emite cotización
 *  - siguiente: fecha siguiente cobro AAAA-MM-DD
 *  - activo: 1 o 0
 *  - observacion: text
 *  - items: arreglo de item con campos:
 *    - descripcion: string (máx 1000 chars)
 *    - cantidad: real
 *    - descuento: real
 *    - descuento_tipo (% o $)
 *
 * IMPORTANTE: para eliminar un item marcarlo con cantidad = 0. No es posible eliminar
 * vía servicios web items obligatorios.
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-05
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$cobro_masivo = 'hosting';
$receptor = 16261063;
$items = [
    [
        'item_codigo' => 'hosting1',
        'cantidad' => 0,
        'descuento' => 15,
        'descuento_tipo' => '%',
    ],
    [
        'item_codigo' => 'consumo-gb',
        'cantidad' => 10, // si es 0, se elimina el item sólo si el item es opcional en el cobro
    ],
];
$referencias = [
    [
        'documento' => 801,
        'folio' => 123,
        'fecha' => date('Y-m-d'),
        'descripcion' => 'Esto es una referencia',
    ],
    [
        'documento' => 801,
        'folio' => 345,
        'fecha' => false, // si es false se elimina la referencia
    ],
];

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// guardar cambios al item (los datos pasados son un ejemplo, podrían no haber
// items o referencias y si modificar el siguiente cobro)
$guardar = $LibreDTE->post('/pagos/cobro_masivo_programados/guardar', [
    'emisor' => $emisor,
    'cobro_masivo_codigo' => $cobro_masivo,
    'receptor' => $receptor,
    'items' => $items,
    'referencias' => $referencias,
]);
if ($guardar['status']['code']!=200) {
    die('Error al guardar el cobro '.$cobro_masivo.' de '.$receptor.': '. $guardar['body']);
}
echo $guardar['body']."\n";
