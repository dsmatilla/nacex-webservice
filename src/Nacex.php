<?php

namespace dsmatilla\NacexWebservice;

class Nacex {

    private $user;
    private $pass;
    private $dev;

    public function __construct($user, $pass, $dev = FALSE) {
        $this->user = $user;
        $this->pass = strtoupper(md5($pass)); // Don't ask
        $this->dev = $dev;
    }

    public function __destruct() {
        unset($this->user);
        unset($this->pass);
        unset($this->idagencia);
        unset($this->idcliente);
        unset($this->dev);
    }

    private function call($method, $data) {
        if ($this->dev) {
            $url = "http://193.16.153.113/nacex_ws/ws"; //DEV
        } else {
            $url = "http://gprs.nacex-webservice.com:80/nacex_ws/ws"; //PROD
        }

        $aux = array();
        if ($data) {
            foreach ($data as $clave => $valor) {
                $aux[] = $clave . "=" . urlencode($valor);
            }
        }
        $data_string = implode("|", $aux);

        $full_url = $url . "?method=" . $method . "&user=" . $this->user . "&pass=" . $this->pass . "&data=" . $data_string;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = utf8_encode(curl_exec($ch));
        curl_close($ch);

        $ret = explode("|", $result);

        if ($ret[0] == "ERROR") {
            throw new \Exception($ret[1], '400');
        }

        return $ret;
    }

    public function getPueblos($cp) {
        $data = Array('cp' => $cp, 'inc_cp' => 'N');
        return $this->call('getPueblos', $data);
    }

    public function getAgencia($cp) {
        $data = Array('cp' => $cp);
        return $this->call('getAgencia', $data);
    }

    public function getInfoEnvio($albaran, $tipo = 'E') {
        $aux = explode("/", $albaran);
        $data = Array('del' => $aux[0], 'num' => $aux[1], 'tipo' => $tipo);
        return $this->call('getInfoEnvio', $data);
    }

    public function putExpedicion($data) {
		$d['del_cli']=$data['idagencia'];
		$d['num_cli']=$data['idcliente'];
		if($data['kilos'] > 2 AND $data['plusbag']) $data['plusbag'] = FALSE; // Ask Lorenzo
		if($data['plusbag']) {
			$d['tip_ser']="04";
			$d['tip_env']="1";
		} else {
			$d['tip_ser']="26";
			$d['tip_env']="2";
		}
		$d['tip_cob']="T";
		$d['bul']="001";
		$d['kil']=sprintf("%09.3f", $data['kilos']);

		$d['nom_ent']=utf8_decode($data['dto_nombre']);
		$d['dir_ent']=utf8_decode($data['dto_direccion']);
		$d['pais_ent']=$data['dto_pais'];
		$d['cp_ent']=$data['dto_cp'];
		$d['pob_ent']=utf8_decode($data['dto_poblacion']);
		$d['tel_ent']=$data['dto_telefono'];

		$d['nom_rec']=utf8_decode($data['rte_nombre']);
		$d['dir_rec']=substr(utf8_decode($data['rte_direccion']), 0, 45);
		$d['pais_rec']=$data['rte_pais'];
		$d['cp_rec']=$data['rte_cp'];
		$d['pob_rec']=utf8_decode($data['rte_poblacion']);
		$d['tel_rec']=$data['rte_telefono'];

		return $this->call('putExpedicion', $d);
	}

    public function cancelarExpedicion($codigo) {
		$data=Array('expe_codigo' => $codigo);
		return $this->call('cancelExpedicion', $data);
	}

    public function getEtiqueta($expedicion) {
		$data=Array('codExp' => $expedicion, 'modelo' => 'IMAGEN');
		$ret=$this->call('getEtiqueta', $data);
		return $ret;
	}
}