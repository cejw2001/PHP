<?php
require_once '../assets/PHPMailer/class.phpmailer.php';
require_once '../assets/PHPMailer/class.smtp.php';

/**
 * @author Daniel Floriano	 
 *
 * Classe utilizada para utilização por outras classes do portal
 */
class Util {

	private static $unidades = array("um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove", "dez", "onze", "doze","treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
	private static $dezenas = array("dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
	private static $centenas = array("cem", "duzentos", "trezentos", "quatrocentos", "quinhentos","seiscentos", "setecentos", "oitocentos", "novecentos");
	private static $milhares = array(array("text" => "mil", "start" => 1000, "end" => 999999, "div" => 1000),
									 array("text" => "milhão", "start" => 1000000, "end" => 1999999, "div" => 1000000),
								     array("text" => "milhões", "start" => 2000000, "end" => 999999999, "div" => 1000000),
								     array("text" => "bilhão", "start" => 1000000000, "end" => 1999999999, "div" => 1000000000),
								     array("text" => "bilhões", "start" => 2000000000, "end" => 2147483647, "div" => 1000000000));

    const MIN = 0.01;
    const MAX = 2147483647.99;
    const MOEDA = " inteiro";
    const MOEDAS = " inteiros";
    const CENTAVO = " centésimo";
    const CENTAVOS = " centésimos";

	public function numberToExt($valor = 0, $bolExibirMoeda = true, $bolPalavraFeminina = false ) {
        $singular = null;
        $plural = null;
 
        if ($bolExibirMoeda) {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        } else {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezessete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
        if ($bolPalavraFeminina) {
            if ($valor == 1) {
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            } else {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas","quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
        }
        $z = 0;
        $valor = number_format( $valor, 2, ".", "." );
        $inteiro = explode( ".", $valor );
 
        for ( $i = 0; $i < count( $inteiro ); $i++ ) {
            for ( $ii = strlen( $inteiro[$i] ); $ii < 3; $ii++ ) {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }
 
        // $fim identifica onde se deve dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count($inteiro) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
        for ( $i = 0; $i < count( $inteiro ); $i++ ) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count( $inteiro ) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ( $valor == "000")
                $z++;
            elseif ( $z > 0 )
                $z--;   
            if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) )
                $r .= ( ($z > 1) ? "de " : "") . $plural[$t];
            if ( $r )
				$rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
        $rt = substr($rt, 1);
		
		if (substr($rt,0,6) == 'um mil')
			return str_replace(array('um mil', 'milhão'), array('mil', 'um milhão'), ($rt ? trim( $rt ) : "zero"));
		else 
			return str_replace(array('milhão'), array('um milhão'), ($rt ? trim( $rt ) : "zero"));
    }
	
	public function percentToExt($number, $moeda = true) {
		if ($number >= self::MIN && $number <= self::MAX) {
			$value = self::conversionR((int) $number);
			if ($moeda) {
				if (floor($number) == 1) {
				$value.= self::MOEDA;
				} else if (floor($number) > 1)
				$value.= self::MOEDAS;
			}

			$decimals = self::extractDecimals($number);
			if ($decimals > 0.00) {
				$decimals = round($decimals * 100);
				if (!empty($value))
					$value.= " e ".self::conversionR($decimals);
				else 
					$value.= self::conversionR($decimals);
				if ($moeda) {
					if ($decimals == 1) {
						$value.= self::CENTAVO;
					} else if ($decimals > 1)
						$value.= self::CENTAVOS;
				}
			}
		}
		return str_replace('um mil', 'mil', trim($value));
	}

	private static function extractDecimals($number) {
		return $number - floor($number);
	}

	private static function conversionR($number) {
		if (in_array($number, range(1, 19))) {
			$value = self::$unidades[$number - 1];
		} else if (in_array($number, range(20, 90, 10))) {
			$value = self::$dezenas[floor($number / 10) - 1];
		} else if (in_array($number, range(21, 99))) {
			$value = self::$dezenas[floor($number / 10) - 1]." e ".self::conversionR($number % 10);
		} else if (in_array($number, range(100, 900, 100))) {
			$value = self::$centenas[floor($number / 100) - 1]." ";
		} else if (in_array($number, range(101, 199))) {
			$value = ' cento e '.self::conversionR($number % 100);
		} else if (in_array($number, range(201, 999))) {
			$value = self::$centenas[floor($number / 100) - 1]." e ".self::conversionR($number % 100);
		} else {
			foreach(self::$milhares as $item) {
				if ($number >= $item['start'] && $number <= $item['end']) {
					$value = self::conversionR(floor($number / $item['div']))." ".$item['text']." ".self::conversionR($number % $item['div']);
					break;
				}
			}
		}
		if(!isset($value))
			$value=false;
		return $value;
	}
	
	/**
	 * @param mixed $val, 
	 * @return string, 	 
	 * 
	 * Função que recebe um array e o converte para json, realizando os tratamentos pertinentes para caracteres especiais
	 */	
	public function jsonEncode($val) {
        $encoded = json_encode($val);
        $unescaped = preg_replace_callback('/(?<!\\\\)\\\\u(\w{4})/',
            function ($matches) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
				}, $encoded
            );
        return $unescaped;
    }	
	
	/**
	 * @param mixed $val, 
	 * @return string, 	 
	 * 
	 * Função que recebe um json e o converte para array, realizando os tratamentos pertinentes para nulos
	 */	
	public function jsonDecode($val) {
		str_replace('', ' ', $val);
        $encoded = json_decode($val, true);
        return $encoded;
    }		
	
	/**
	 * @param int $tipo
	 * @param int $num
	 * @return string 
	 *
	 * Função que formata um número de contrato ou CNPJ aplicando as respectivas máscaras
	 */
	public function formatMask ($tipo, $num){
		if ($tipo == 2 && !in_array($num, array('',0))){ //CNPJ
			$cnpj = sprintf("%014s", $num);
			$str = preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/", "$1.$2.$3/$4-$5", $cnpj);
		} else if ($tipo == 5 && !in_array($num, array('','0'))) { //Moeda
			$str = substr($num, 0, 3) == 'R$ ' ? $num : 'R$ ' . number_format($num, 2, ',', '.');
		} else if ($tipo == 7 && !in_array($num, array('',0))) { //Percentual
			$str = number_format($num, 2, ',', '.') . '%';
		} 
		return $str;
	}
	
	/**
	 * @param array $array1
	 * @param array $array2
	 * @return boolean
	 *
	 * Função que verifica se cada um dos elementos de array1 estão em array2
	 */
	function busca($array1, $array2){
		$cont = 0;
		for ($i = 0; $i < sizeof($array2); $i++)
			if (in_array($array2[$i], $array1)) 
				$cont += 1;	
		if ($cont > 0)
			return true;
		else
			return false;
	}		
	
	/**
	 * @param int $integer
	 * @param boolean $upcase	 
	 * @return string	 
	 *
	 * Função que recebe um número inteiro e o converte para numeral romano
	 */
	public function romano($integer, $upcase = true) {
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1);
        while($integer > 0) 
            foreach($table as $rom=>$arb) 
                if($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
        return $return;
    } 
	
	/**
	 * @param string $remetente
	 * @param string $destinatario
	 * @param string $copia
	 * @param string $assunto
	 * @param string $corpo	 
	 *
	 * @return boolean
	 * Função que envia um email para o endereco informado
	 */	
	public function enviarEmail($remetente, $destinatario, $copia, $assunto, $corpo, $anexos = array()){
		$mail = new PHPMailer;
		$mail -> isSMTP();
		$mail -> isHTML();
		$mail -> Host = 'xxxxxxxxxx';
		$mail -> Port = 99;
		$mail -> CharSet = 'utf-8';
		$mail -> setFrom($remetente);
		
		foreach ($destinatario as $endereco) 
			$mail -> addAddress($endereco);
		
		foreach ($copia as $cc) 
			$mail -> addCC($cc);		
			
		foreach ($anexos as $pathAnexo)
			$mail -> addAttachment($pathAnexo);
			
		$mail -> Body = $corpo;		 
		$mail -> Subject = $assunto;
		
		
		if ($mail->send())
			return true; 
		else
			return false;
	}
	
	/**
	 * @param array $data
	 * @param string $url
	 *
	 * @return string
	 * Função que envia uma requisição post via cURL
	 */			
	public function enviaPostAPI($data, $url) {

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $this -> jsonEncode($data));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}

	/**
	 * @param int $ano
	 * @param string $form
	 *
	 * @return string
	 *
	 * Função que calcula a data da páscoa para um ano passado como parâmetro
	 */		
	public function dataPascoa($ano=false, $form="d/m/Y") {
		$ano=$ano?$ano:date("Y");
		
		if ($ano<1583) { 
			$A = ($ano % 4);
			$B = ($ano % 7);
			$C = ($ano % 19);
			$D = ((19 * $C + 15) % 30);
			$E = ((2 * $A + 4 * $B - $D + 34) % 7);
			$F = (int)(($D + $E + 114) / 31);
			$G = (($D + $E + 114) % 31) + 1;
			return date($form, mktime(0,0,0,$F,$G,$ano));
		} else {
			$A = ($ano % 19);
			$B = (int)($ano / 100);
			$C = ($ano % 100);
			$D = (int)($B / 4);
			$E = ($B % 4);
			$F = (int)(($B + 8) / 25);
			$G = (int)(($B - $F + 1) / 3);
			$H = ((19 * $A + $B - $D - $G + 15) % 30);
			$I = (int)($C / 4);
			$K = ($C % 4);
			$L = ((32 + 2 * $E + 2 * $I - $H - $K) % 7);
			$M = (int)(($A + 11 * $H + 22 * $L) / 451);
			$P = (int)(($H + $L - 7 * $M + 114) / 31);
			$Q = (($H + $L - 7 * $M + 114) % 31) + 1;
			return date($form, mktime(0,0,0,$P,$Q,$ano));
		}
	}

	/**
	 * @param int $ano
	 * @param string $form
	 *
	 * @return string
	 *
	 * Função que calcula a data da terça-feira de carnaval para um ano passado como parâmetro
	 */	
	public function dataCarnaval($ano=false, $form="d/m/Y") {
		$ano=$ano?$ano:date("Y");		
		$a=explode("/", $this -> dataPascoa($ano));
		return date($form, mktime(0,0,0,$a[1],$a[0]-47,$a[2]));
	}
	
	/**
	 * @param int $ano
	 * @param string $form
	 *
	 * @return string
	 *
	 * Função que calcula a data da segunda-feira de carnaval para um ano passado como parâmetro
	 */	
	public function dataCarnaval2($ano=false, $form="d/m/Y") {
		$ano=$ano?$ano:date("Y");	
		$a=explode("/", $this -> dataPascoa($ano));
		$data = date($form, mktime(0,0,0,$a[1],$a[0]-47,$a[2]));
		
		$dateTime = DateTime::createFromFormat($form, $data);
		$dateTime -> modify('-1 day');
		$d = $dateTime -> format('d/m/Y');

		return $d;
	}

	/**
	 * @param int $ano
	 * @param string $form
	 *
	 * @return string
	 *
	 * Função que calcula a data de Corpus Christi para um ano passado como parâmetro
	 */	
	public function dataCorpusChristi($ano=false, $form="d/m/Y") {
		$ano=$ano?$ano:date("Y");
		$a=explode("/", $this -> dataPascoa($ano));
		return date($form, mktime(0,0,0,$a[1],$a[0]+60,$a[2]));
	}

	/**
	 * @param int $ano
	 * @param string $form
	 *
	 * @return string
	 *
	 * Função que calcula a data da sexta-feira santa para um ano passado como parâmetro
	 */	
	public function dataSextaSanta($ano=false, $form="d/m/Y") {
		$ano=$ano?$ano:date("Y");
		$a=explode("/", $this -> dataPascoa($ano));
		return date($form, mktime(0,0,0,$a[1],$a[0]-2,$a[2]));
	} 
	
	/**
	 * @param string $strData
	 * @param int $qtdDias
	 *
	 * @return string
	 *
	 * Função que calcula a data somada a uma quantidade de dias úteis passada como parâmetro
	 */		
	public function somarDiasUteis($strData,$qtdDias) {

		# Caso seja informado uma data do MySQL do tipo DATETIME - aaaa-mm-dd 00:00:00
		# Transforma para DATE - aaaa-mm-dd
	    $strData = substr($strData,0,10);

		# Se a data estiver no formato brasileiro: dd/mm/aaaa
		# Converte-a para o padrão americano: aaaa-mm-dd

		if ( preg_match("@/@",$strData) == 1 )
			$strData = implode("-", array_reverse(explode("/",$strData)));

		# chama a funcao que calcula a pascoa	
		$pascoa_dt = $this -> dataPascoa(date('Y'));
		$aux_p = explode("/", $pascoa_dt);
		$aux_dia_pas = $aux_p[0];
		$aux_mes_pas = $aux_p[1];
		$pascoa = "$aux_mes_pas"."-"."$aux_dia_pas"; // crio uma data somente como mes e dia
		
		# chama a funcao que calcula a terça-feira de carnaval	
		$carnaval_dt = $this -> dataCarnaval(date('Y'));
		$aux_carna = explode("/", $carnaval_dt);
		$aux_dia_carna = $aux_carna[0];
		$aux_mes_carna = $aux_carna[1];
		$carnaval = "$aux_mes_carna"."-"."$aux_dia_carna"; 
		
		# chama a funcao que calcula a segunda-feira de carnaval		
		$carnaval_dt = $this -> dataCarnaval2(date('Y'));
		$aux_carna = explode("/", $carnaval_dt);
		$aux_dia_carna = $aux_carna[0];
		$aux_mes_carna = $aux_carna[1];
		$carnaval2 = "$aux_mes_carna"."-"."$aux_dia_carna"; 

		# chama a funcao que calcula corpus christi	
		$CorpusChristi_dt = $this -> dataCorpusChristi(date('Y'));
		$aux_cc = explode("/", $CorpusChristi_dt);
		$aux_cc_dia = $aux_cc[0];
		$aux_cc_mes = $aux_cc[1];
		$Corpus_Christi = "$aux_cc_mes"."-"."$aux_cc_dia"; 

		# chama a funcao que calcula a sexta feira santa	
		$sexta_santa_dt = $this -> dataSextaSanta(date('Y'));
		$aux = explode("/", $sexta_santa_dt);
		$aux_dia = $aux[0];
		$aux_mes = $aux[1];
		$sexta_santa = "$aux_mes"."-"."$aux_dia"; 

		$feriados = array("01-01", $carnaval2, $carnaval, $sexta_santa, $pascoa, $Corpus_Christi, "04-21", "05-01", "06-12" ,"07-09" , "07-16", "09-07", "10-12", "11-02", "11-15"/*, "12-24"*/, "12-25"/*, "12-31"*/);
		
	    $array_data = explode('-', $strData);
	    $count_days = 0;
	    $int_qtd_dias_uteis = 0;

		while ( $int_qtd_dias_uteis < $qtdDias ) {
			$count_days++;
			$day = date('m-d',strtotime('+'.$count_days.'day',strtotime($strData))); 
			
			if(($dias_da_semana = gmdate('w', strtotime('+'.$count_days.' day', gmmktime(0, 0, 0, $array_data[1], $array_data[2], $array_data[0]))) ) != '0' && $dias_da_semana != '6' && !in_array($day,$feriados))
				$int_qtd_dias_uteis++;

		}
		
		return gmdate('d/m/Y',strtotime('+'.$count_days.' day',strtotime($strData)));
	}
	
	/**
	 * @param string $datIni (YYYY-mm-dd)
	 * @param string $datFim (dd/mm/YYYY)
	 *
	 * @return int
	 *
	 * Função que calcula a difereça em dias úteis entre duas datas passadas como parâmetro, desde que sejam dias úteis
	 */
	public function difDiasUteis($datIni, $datFim) {
		
		$i = 0;
		if ($datIni == '0000-00-00')
			return 0;
		
		while(true) {
			$datAux = $this -> somarDiasUteis($datIni, $i);	
			
			if ((strtotime($datAux) > strtotime($datFim)) or ($i >= 1000))
				break;
			else
				$i++;
		}
		return ($i-1);	
	}
	
	/**
	 * @param string $base64File
	 * @return string
	 *
	 * Função que converte um arquivo docx para PDF
	 */	
	public function docxToPDF($base64File) {
		
		exec("java -jar /srv/www/htdocs/portal/assets/java/Converter.jar $base64File", $ret, $status);
		return $ret[0];
	}
}
?>