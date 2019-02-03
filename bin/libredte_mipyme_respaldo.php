#!/usr/bin/php
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

require('../vendor/autoload.php');

/**
 * Comando para realizar un respaldo de los datos del Portal MIPYME del SII
 *
 * Modo de uso:
 *  $ ./libredte_mipyme_respaldo.php --usuario=XYZ --clave=XYZ --contribuyente=XYZ --hash=XYZ
 *
 * Si se omite --contribuyente se usa el --usuario como contribuyente
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-02-03
 */
class LibreDTE_MIPYME_Respaldo extends LibreDTE_Base_Command
{

    protected $options = [
        'short' => '',
        'long' => [
            'usuario:',
            'clave:',
            'contribuyente::',
            'salida::',
        ],
    ]; ///< Opciones que usa este comando

    protected $documentos = [
        'Factura Electronica' => 33,
        'Factura Exenta Electronica' => 34,
        'Liquidacion Factura Electronica' => 43,
        'Factura de Compra Electronica' => 46,
        'Nota de Debito Electronica' => 56,
        'Nota de Credito Electronica' => 61,
        'Guia de Despacho Electronica' => 52,
        'Factura de Exportacion Electronica' => 110,
        'Nota de Debito de Exportacion Electronica' => 111,
        'Nota de Credito de Exportacion Electronica' => 112,
    ]; ///< Codigos de documentos a partir de la glosa

    /**
     * Método principal del comando para respaldar, lanza los otros métodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    public function main()
    {
        $this->respaldarDocumentosEmitidos();
        $this->respaldarDocumntosRecibidos();
        $this->respaldarIECV();
    }

    /**
     * Método que valida y asigna los valores por defecto a los parámetros
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    protected function parseArgs()
    {
        parent::parseArgs();
        if (empty($this->args['usuario']) or empty($this->args['clave'])) {
            $this->error('Debe indicar --usuario y --clave del usuario que se autenticará en el SII');
        }
        if (empty($this->args['contribuyente'])) {
            $this->args['contribuyente'] = $this->args['usuario'];
        }
        if (empty($this->args['salida'])) {
            $this->args['salida'] = 'libredte_mipyme_respaldo_'.$this->args['contribuyente'];
        }
        if (!is_dir($this->args['salida'])) {
            mkdir($this->args['salida'], 0755, true);
            mkdir($this->args['salida'].'/emitidos', 0755, true);
            mkdir($this->args['salida'].'/recibidos', 0755, true);
            mkdir($this->args['salida'].'/iecv', 0755, true);
        }
    }

    /**
     * Método que respalda los documentos emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    private function respaldarDocumentosEmitidos()
    {
        // obtener DTE emitidos en el Portal MIPYME del SII
        $this->log('Descargando documentos emitidos');
        $emitidos = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_emitidos/'.$this->args['contribuyente'], [
            'auth'=>[
                'rut' => $this->args['usuario'],
                'clave' => $this->args['clave'],
            ],
        ]);
        if ($emitidos['status']['code']!=200) {
            $this->log(' [error] Al obtener DTE emitidos en Portal MIPYME del SII: '.$emitidos['body']);
            return;
        }
        file_put_contents($this->args['salida'].'/emitidos_'.$this->args['contribuyente'].'.json', json_encode($emitidos['body'], JSON_PRETTY_PRINT));
        // descargar PDF y XML de los documentos
        $n_docs = count($emitidos['body']);
        $i = 0;
        foreach ($emitidos['body'] as $doc) {
            $i++;
            $this->log('Descargando PDF y XML '.$this->num($i).'/'.$this->num($n_docs).' '.$doc['dte'].' #'.$doc['folio']);
            // descargar PDF
            $pdf = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_emitido_pdf/'.$this->args['contribuyente'].'/'.$doc['codigo'], [
                'auth'=>[
                    'rut' => $this->args['usuario'],
                    'clave' => $this->args['clave'],
                ],
            ]);
            if ($pdf['status']['code']!=200) {
                $this->log(' [error] Al obtener el PDF '.$doc['codigo'].': '.$pdf['body']);
            } else {
                file_put_contents($this->args['salida'].'/emitidos/dte_'.$this->args['contribuyente'].'_T'.$this->getCodigoDTE($doc['dte']).'F'.$doc['folio'].'.pdf', $pdf['body']);
            }
            // descargar XML
            $xml = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_emitido_xml/'.$this->args['contribuyente'].'/'.$this->getCodigoDTE($doc['dte']).'/'.$doc['folio'], [
                'auth'=>[
                    'rut' => $this->args['usuario'],
                    'clave' => $this->args['clave'],
                ],
            ]);
            if ($xml['status']['code']!=200) {
                $this->log(' [error] Al obtener el XML '.$doc['codigo'].': '.$xml['body']);
            } else {
                file_put_contents($this->args['salida'].'/emitidos/dte_'.$this->args['contribuyente'].'_T'.$this->getCodigoDTE($doc['dte']).'F'.$doc['folio'].'.xml', $xml['body']);
            }
        }
    }

    /**
     * Método que respalda los documentos recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    private function respaldarDocumntosRecibidos()
    {
        // obtener DTE recibidos en el Portal MIPYME del SII
        $this->log('Descargando documentos recibidos');
        $recibidos = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_recibidos/'.$this->args['contribuyente'], [
            'auth'=>[
                'rut' => $this->args['usuario'],
                'clave' => $this->args['clave'],
            ],
        ]);
        if ($recibidos['status']['code']!=200) {
            $this->log(' [error] Al obtener DTE recibidos en Portal MIPYME del SII: '.$recibidos['body']);
            return;
        }
        file_put_contents($this->args['salida'].'/recibidos_'.$this->args['contribuyente'].'.json', json_encode($recibidos['body'], JSON_PRETTY_PRINT));
        // descargar PDF y XML de los documentos
        $n_docs = count($recibidos['body']);
        $i = 0;
        foreach ($recibidos['body'] as $doc) {
            $i++;
            $this->log('Descargando PDF y XML '.$this->num($i).'/'.$this->num($n_docs).' '.$doc['dte'].' #'.$doc['folio']);
            // descargar PDF
            $pdf = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_recibido_pdf/'.$this->args['contribuyente'].'/'.$doc['rut'].'-'.$doc['dv'].'/'.$doc['codigo'], [
                'auth'=>[
                    'rut' => $this->args['usuario'],
                    'clave' => $this->args['clave'],
                ],
            ]);
            if ($pdf['status']['code']!=200) {
                $this->log(' [error] Al obtener el PDF '.$doc['codigo'].': '.$pdf['body']);
            } else {
                file_put_contents($this->args['salida'].'/recibidos/dte_'.$doc['rut'].'-'.$doc['dv'].'_T'.$this->getCodigoDTE($doc['dte']).'F'.$doc['folio'].'.pdf', $pdf['body']);
            }
            // descargar XML
            $xml = $this->LibreDTE->post('/utilidades/sii/mipyme_dte_recibido_xml/'.$this->args['contribuyente'].'/'.$doc['rut'].'-'.$doc['dv'].'/'.$this->getCodigoDTE($doc['dte']).'/'.$doc['folio'], [
                'auth'=>[
                    'rut' => $this->args['usuario'],
                    'clave' => $this->args['clave'],
                ],
            ]);
            if ($xml['status']['code']!=200) {
                $this->log(' [error] Al obtener el XML '.$doc['codigo'].': '.$xml['body']);
            } else {
                file_put_contents($this->args['salida'].'/recibidos/dte_'.$doc['rut'].'-'.$doc['dv'].'_T'.$this->getCodigoDTE($doc['dte']).'F'.$doc['folio'].'.xml', $xml['body']);
            }
        }
    }

    /**
     * Método que respalda la información electrónica de compra y venta (IECV)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    private function respaldarIECV()
    {
        // TODO: pendiente (aun no está disponible el servicio web)
    }

    /**
     * Método que genera un log para el comando en pantalla y en archivo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    private function log($msg)
    {
        $msg = date('Y-m-d H:i:s').' => '.$msg."\n";
        // mostrar en pantalla
        echo $msg;
        // guardar en archivo
        $fd = fopen($this->args['salida'].'/respaldo.log', 'a+');
        fwrite($fd, $msg);
        fclose($fd);
    }

    /**
     * Método que entrega el codigo del DTE a partir de su glosa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    private function getCodigoDTE($glosa)
    {
        return isset($this->documentos[$glosa]) ? $this->documentos[$glosa] : 0;
    }

}

// lanzar comando
exit((new LibreDTE_MIPYME_Respaldo())->main());

/**
 * Clase base para todos los comandos
 * @todo Se mantiene en este archivo por ser el único comando, si hubiesen más se separará
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-02-03
 */
abstract class LibreDTE_Base_Command
{

    protected $LibreDTE; ///< Objeto con el cliente a los servicios web de LibreDTE
    protected $args = []; ///< Argumentos que se pasaron al comando

    /**
     * Constructor del comando que procesa argumentos pasados por la línea de comandos
     * y además que crea el cliente de LibreDTE para los servicios web
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    public function __construct()
    {
        $this->parseArgs();
        $this->LibreDTE = new \sasco\LibreDTE\SDK\LibreDTE($this->args['hash'], $this->args['url']);
    }

    /**
     * Método que procesa los argumentos pasados por la terminal
     * Este procesamiento es básico, se debe extender en el comando
     * este método para hacer validaciones o valores por defecto
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    protected function parseArgs()
    {
        if (!isset($this->options['short'])) {
            $this->options['short'] = '';
        }
        if (!isset($this->options['long'])) {
            $this->options['long'] = [];
        }
        if (!in_array('url:', $this->options['long'])) {
            $this->options['long'][] = 'url::';
        }
        if (!in_array('hash:', $this->options['long'])) {
            $this->options['long'][] = 'hash::';
        }
        $this->args = getopt($this->options['short'], $this->options['long']);
        if (!isset($this->args['url'])) {
            $this->args['url'] = 'https://libredte.cl';
        }
        if (!isset($this->args['hash'])) {
            $this->args['hash'] = '';
        }
    }

    /**
     * Método que muestra un error
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    protected function error($msg, $code = 1)
    {
        echo $msg,"\n";
        exit($code);
    }

    /**
     * Método que formatea un número
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-03
     */
    protected function num($n, $d = 0)
    {
        return number_format($n, $d, ',', '.');
    }

}
