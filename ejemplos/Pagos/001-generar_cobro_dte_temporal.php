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
 *  - Crear cobro para un DTE temporal y obtener URL de pago
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-05
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$dte = 33;
$codigo = '351d78bb6ecfa81e5d5181997a59e335';
$receptor = 16261063;

// incluir autocarga de composer
require('../../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// crear cobro
$cobro = $LibreDTE->get('/dte/dte_tmps/cobro/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$emisor);
if ($cobro['status']['code']!=200) {
    die('Error generar el cobro del DTE temporal: '.$cobro['body']."\n");
}
print_r($cobro['body']);
