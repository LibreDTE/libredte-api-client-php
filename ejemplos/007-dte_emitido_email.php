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
 *  - Enviar un DTE emitido por correo electrónico
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-10-03
 */

// datos a utilizar
$url = 'https://libredte.cl';
$hash = '';
$emisor = 76192083;
$dte = 33;
$folio = 442;
$datos = [
    'emails' => ['cliente@example.com'],
    'asunto' => 'Envío de factura',
    'mensaje' => 'Esta es su factura',
    'pdf' => true,
    'cedible' => true,
    'papelContinuo' => false,
];

// incluir autocarga de composer
require('../vendor/autoload.php');

// crear cliente
$LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($hash, $url);

// enviar email
$envio = $LibreDTE->post('/dte/dte_emitidos/enviar_email/'.$dte.'/'.$folio.'/'.$emisor, $datos);
if ($envio['status']['code']!=200) {
    die('Error al enviar el correo del DTE emitido: '.$envio['body']."\n");
}
echo $envio['body']."\n";
