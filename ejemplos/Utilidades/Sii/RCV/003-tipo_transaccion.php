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
 *  - Enviar masivamente al SII los tipos de transacción de un DTE de compra
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-06
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$contribuyente = '76192083-9';
$periodo = 201709;
$documentos = [
    ['76187287-7', 33, 10879, 1, 1],
    ['90635000-9', 33, 40197646, 1, 1],
]; ///< Código penúltimo y último según documentación del SII para "código tipo operación" y "código impuesto"
$contrasenia = ''; // contraseña del receptor en el SII
$firma = [
    'cert' => 'firma.crt',
    'key' => 'firma.key',
]; ///< Este servicio funciona tanto con firma electrónica como con RUT/clave

// incluir autocarga de composer
require('../../../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// asignar tipo de transacción a documentos de compra
$r = $LibreDTE->post('/utilidades/sii/rcv_tipo_transaccion/'.$contribuyente.'/'.$periodo, [
    'auth'=>[
        'rut' => $contribuyente,
        'clave' => $contrasenia,
    ],
    'documentos' => $documentos,
    /*'firma' => [
        'cert-data' => file_get_contents($firma['cert']),
        'key-data' => file_get_contents($firma['key']),
    ]*/
]);
if ($r['status']['code']!=200) {
    die('Error al asignar tipos de transacción de DTE de compra: '.$r['body']."\n");
}

// mostrar resultado
print_r($r['body']);
