<?php

declare(strict_types=1);

/**
 * LibreDTE: Cliente de API en PHP.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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

namespace libredte\api_client;

/**
 * Clase ApiException para la gestión de excepciones en el cliente de la API
 * de LibreDTE.
 *
 * Esta clase extiende la clase Exception estándar de PHP y se utiliza para
 * manejar errores específicos que pueden ocurrir durante las interacciones
 * con la API de LibreDTE.
 * Las instancias de ApiException pueden incluir información adicional
 * relevante para los errores de la API.
 */
class ApiException extends \Exception
{
    // Aquí van los métodos y propiedades de la clase
}
