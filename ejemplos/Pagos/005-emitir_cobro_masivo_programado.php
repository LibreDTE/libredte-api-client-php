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
 *  - Emitir un cobro masivo, emitiendo y enviando por correo, cada uno de los
 *    documentos generados (si así está configurado)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-05
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$cobro_masivo = 'hosting';
$fecha = date('Y-m-d');

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// buscar los cobros programados activos que tiene el cobro masivo en la fecha indicada
$cobros = $LibreDTE->get('/pagos/cobro_masivos/programados/'.$cobro_masivo.'/'.$emisor.'?activo=1&siguiente='.$fecha);
if ($cobros['status']['code']!=200) {
    die('Error al recuperar los cobros masivos programados: '.$cobros['body']."\n");
}

// si no hay cobros nada que hacer
if (empty($cobros['body'])) {
    die('No hay cobros programados de '.$cobro_masivo.' para el día '.$fecha."\n");
}

// procesar cada cobro masivo programado
$error = [];
$ok = [];
foreach ($cobros['body'] as $cobro) {
    // el cobro de cada receptor puede ser modificado antes de ser emitido.
    // No es obligatorio hacer este paso, pero sirve en caso que se quiera aplicar un
    // descuento o modificar la cantidad en el listado de items.
    // Más detalles sobre esto en ejemplo: 006-guardar_cobro_masivo_programado.php
    $guardar = $LibreDTE->post('/pagos/cobro_masivo_programados/guardar', [
        'emisor' => $emisor,
        'cobro_masivo_codigo' => $cobro_masivo,
        'receptor' => $cobro['receptor'],
        'items' => [
            [
                'item_codigo' => 'hosting1',
                'cantidad' => 1,
                'descuento' => 25,
                'descuento_tipo' => '%',
            ],
            [
                'item_codigo' => 'consumo-gb',
                'cantidad' => 100,
            ],
        ],
    ]);
    if ($guardar['status']['code']!=200) {
        $error[] = 'Error al guardar el cobro '.$cobro_masivo.' de '.$cobro['receptor'].': '. $guardar['body'];
        continue;
    }
    // emitir el cobro (esto es lo obligatorio, lo que realmente emite el cobro)
    $emision = $LibreDTE->get('/pagos/cobro_masivo_programados/emitir/'.$cobro_masivo.'/'.$cobro['receptor'].'/'.$emisor);
    if ($emision['status']['code']!=200) {
        $error[] = 'Error al emitir el cobro '.$cobro_masivo.' de '.$cobro['receptor'].': '. $emision['body'];
        continue;
    }
    $ok[] = 'Cobro '.$cobro_masivo.' de '.$cobro['receptor'].' emitido';
}

// resultado
print_r($ok);
print_r($error);
