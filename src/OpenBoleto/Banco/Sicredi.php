<?php

/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright (C) 2013 Estrada Virtual
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace OpenBoleto\Banco;

use OpenBoleto\BoletoAbstract;

/**
 * Classe boleto Sicredi S/A.
 *
 * @package    OpenBoleto
 * @author     Raphael Paez <http://github.com/paezraphael>
 * @copyright  Copyright (c) 2017 Acesbyte
 * @license    MIT License
 * @version    1.0
 */
class Sicredi extends BoletoAbstract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '748';

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     * @var string
     */
    protected $logoBanco = 'sicredi.jpg';


    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = array('1');

    protected $inicioNossoNumero = '';

    protected $byteIdt = '2';

    public function setbyteIdt($byteIdt)
    {
        $this->byteIdt = $byteIdt;
        return $this;
    }
    // Byte de Identificação do cedente 1 - Cooperativa; 2 a 9 - Cedente
    public function getbyteIdt()
    {
        return $this->byteIdt;
    }

    /**
     * Define a inicio nosso numero
     *
     * @param string $inicio_nosso_numero
     * @return BoletoAbstract
     */
    public function setinicioNossoNumero($inicioNossoNumero)
    {

        $this->inicioNossoNumero = $inicioNossoNumero;
        return $this;
    }

    /**
     * Retorna a $inicioNossoNumero
     *
     * @return string
     */
    public function getinicioNossoNumero()
    {
        return $this->inicioNossoNumero;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numero =  str_pad($this->getSequencial(), 5, 0, STR_PAD_LEFT);
        $nnum = $this->inicioNossoNumero. $this->byteIdt . $numero;
        $dv = $this->getDigitoVerificadorNossoNumero($nnum);
        return $nnum.$dv;
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    public function getCampoLivre(){
        return static::zeroFill($this->getAgencia(), 4) .
            static::zeroFill($this->getCarteira(), 2) .
            substr(substr($this->getNossoNumero(false),2),0,-1) .
            static::zeroFill($this->getConta(), 8) .
            '0';
    }

    public function getNumeroFebraban()
    {
        return self::zeroFill($this->getCodigoBanco(), 3) . $this->getMoeda(). $this->getDigitoVerificador().  $this->getFatorVencimento() . $this->getValorZeroFill()   . $this->getCampoLivreCodigo() ;
    }

    public function getCampoLivreCodigo(){
        return static::zeroFill($this->getAgencia(), 4) .
            static::zeroFill($this->getCarteira(), 2) .
            substr(substr($this->getNossoNumero(false),2),0,-1) .
            static::zeroFill(substr($this->getConta(),0,-1), 7).
            '0';
    }


    /**
     * Define nomes de campos específicos do boleto do Sicredi
     *
     * @return array
     */
    public function getViewVars()
    {
        return array(
            'mostra_cip' => false,
            'byteIdt' => $this->getbyteIdt(),
            'inicioNossoNumero' => $this->getinicioNossoNumero(),
        );
    }


    /**
    +     * Calcúlo do dígito verificador do nosso Número
    +     *
    +     * @param  string $numero
    +     * @return int
    +     */
    static function getDigitoVerificadorNossoNumero($numero) {
        $modulo = self::modulo11($numero,9);
        // esta rotina sofrer algumas alterações para ajustar no layout do SICREDI
        $digito = 11 - $modulo['resto'];
        if ($digito > 9 ) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        $r_div = (int)($modulo['soma']/11);
        $digito = ($modulo['soma'] - ($r_div * 11));
        return $digito;
    }

}
