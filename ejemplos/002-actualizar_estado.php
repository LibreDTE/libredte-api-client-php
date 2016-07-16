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
 *  - Emitir DTE temporal
 *  - Generar DTE real a partir del temporal
 *  - Obtener PDF a partir del DTE real
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-07-15
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$rut = 76192083;
$dte = 33;
$folio = 42;
$metodo = 1; // =1 servicio web, =0 correo

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

# consultar estado de dte emitido
$estado = $LibreDTE->get('/dte/dte_emitidos/actualizar_estado/'.$dte.'/'.$folio.'/'.$rut.'?usarWebservice='.$metodo);
if ($estado['status']['code']!=200) {
    die('Error al obtener el estado del DTE emitido: '.$estado['body']."\n");
}
print_r($estado['body']);
