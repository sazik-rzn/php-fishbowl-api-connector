<?php 
/**
 * Fishbowl API connector by Sazonov V. (sazik.rzn@gmail.com)
 */
class FishbowlAPIJSON {
    private $_host;
    private $_port;

    private $_appKey;
    private $_appName;
    private $_appDescr;

    private $_user;
    private $_password;

    private $_connection;

    private $_sessionKey = false;

    private $_next = 'SetHostInfo';

    public function SetHostInfo($host, $port){
        $this->_host = $host;
        $this->_port = $port;
        $this->_connection = fsockopen($this->_host, $this->_port);
        $this->_next = 'SetAppInfo';
    }

    public function SetAppInfo($name, $key, $description){
        if($this->_next == 'SetAppInfo'){
            $this->_appName = $name;
            $this->_appKey = $key;
            $this->_appDescr = $description;
            $this->_next = 'Login';
        }
        else{
            throw new \Exception('Run SetHostInfo!!!');
        }
    }

    public function Login($name, $pwd){
        if($this->_next == 'Login'){
            $this->_user = $name;
            $this->_password = base64_encode(md5($pwd, true));

            $this->_sessionKey = $this->Request("LoginRq", [
                "IAID"=>$this->_appKey,
                "IADescription"=>$this->_appDescr,
                "UserName"=>$this->_user,
                "IAName"=>$this->_appName,
                "UserPassword"=>$this->_password
            ]);

            $this->_next = 'All';
        }
        else{
            throw new \Exception('Run SetAppInfo!!!');
        }
    }

    public function Request($method, $data){
        $res = false;
        if(($this->_sessionKey!==false && $this->_next==='All')||$method=="LoginRq"){
            $req = [
                "FbiJson"=>[
                    "Ticket"=>[
                        "Key"=>(($this->_sessionKey!==false)?$this->_sessionKey:"")
                    ],
                    "FbiMsgsRq"=>[
                        $method=>$data
                    ]
                ]
            ];

            $reqJson = json_encode($req);
            $len = strlen($reqJson);
            $packed = pack("N", $len);
            fwrite($this->_connection, $packed, 4);
            fwrite($this->_connection, $reqJson);
            $packed_len = stream_get_contents($this->_connection, 4); 
            if ($packed_len != '') {
                $hdr = unpack('Nlen', $packed_len);
                $_len = $hdr['len'];
                $res = json_decode(utf8_encode(stream_get_contents($this->_connection, $_len)),true);
                if($method=="LoginRq" && isset($res["FbiJson"]["Ticket"]["Key"])){
                    return $res["FbiJson"]["Ticket"]["Key"];
                }
                if(isset($res["FbiJson"]["FbiMsgsRs"]["statusCode"]) && $res["FbiJson"]["FbiMsgsRs"]["statusCode"]!==1000){
                    var_dump([
                        'req'=>$req,
                        'res'=>$res
                    ]);
                    $message = "Fishbowl API ERR:{$res["FbiJson"]["FbiMsgsRs"]["statusCode"]}  {$res["FbiJson"]["FbiMsgsRs"]["statusMessage"]}";
                    throw new \Exception($message);
                }
                $mayBeResName = preg_replace("/Rq/", "Rs", $method);
                if(isset($res["FbiJson"]["FbiMsgsRs"][$method])){
                    $res = $res["FbiJson"]["FbiMsgsRs"][$method];
                }
                elseif(isset($res["FbiJson"]["FbiMsgsRs"][$mayBeResName])){
                    $res = $res["FbiJson"]["FbiMsgsRs"][$mayBeResName];
                }
            }
        }
        else{
            throw new \Exception("Log in!!!");
        }        
        return $res;
    }

    private function _getResponse(){}

    public function Logout(){
        fclose($this->_connection);
        $this->_next = 'SetHostInfo';
    }
}
