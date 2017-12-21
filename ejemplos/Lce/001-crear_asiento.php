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
 *  - Crear un asiento contable en el módulo LCE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-12-21
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$datos = [
    'fecha' => date('Y-m-d'),
    'glosa' => 'Venta T33F123',
    'detalle' => [
        'debe' => [
            1101001 => 119, // caja
        ],
        'haber' => [
            4101001 => 100, // ventas
            2105101 => 19, // iva débito
        ],
    ],
    'operacion' => 'I',
    'documentos' => ['emitidos'=>[['dte'=>33, 'folio'=>123]]], // esto es opcional, pero se recomienda ya que el SII lo puede pedir
]; // este es un ejemplo de una venta, obviamente puede ser cualquier tipo de asiento contable

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// crear asiento
$asiento = $LibreDTE->post('/lce/lce_asientos/crear/'.$emisor, $datos);
if ($asiento['status']['code']!=200) {
    die('Error al crear el asiento contable: '.$asiento['body']."\n");
}
print_r($asiento['body']);
