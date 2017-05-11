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

        //$result=file_get_contents($full_url);
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
}