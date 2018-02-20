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
 *  - Modificar asiento contable
 *  - Inclue marcar un asiento como anulado
 * La respuesta entrega el asiento completo después de ser modificado
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-02-20
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$periodo = 2018;
$asiento = 1;
$datos = [
    'fecha' => date('Y-m-d'),
    'glosa' => 'Venta T33F123',
    'detalle' => [
        'debe' => [
            1101001 => 1190, // caja
        ],
        'haber' => [
            4101001 => 1000, // ventas
            2105101 => 190, // iva débito
        ],
    ],
    'anulado' => 0, // 0= no anulado, 1= anulado
    'operacion' => 'I',
    'documentos' => ['emitidos'=>[['dte'=>33, 'folio'=>1234]]],
]; // este es un ejemplo de una venta, obviamente puede ser cualquier tipo de asiento contable
   // (los campos son son opcionales ya que es edición, pero al menos se debe mandar uno)

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// editar asiento
$asiento = $LibreDTE->post('/lce/lce_asientos/editar/'.$periodo.'/'.$asiento.'/'.$emisor, $datos);
if ($asiento['status']['code']!=200) {
    die('Error al editar el asiento contable: '.$asiento['body']."\n");
}
print_r($asiento['body']);
