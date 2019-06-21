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
    private $_currentMethod = false;
    private $_currentRequest = false;

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

            $this->Request("LoginRq", [
                "IAID"=>$this->_appKey,
                "IADescription"=>$this->_appDescr,
                "UserName"=>$this->_user,
                "IAName"=>$this->_appName,
                "UserPassword"=>$this->_password
            ]);
            $this->_sessionKey = $this->Response(true);

            $this->_next = 'All';
        }
        else{
            throw new \Exception('Run SetAppInfo!!!');
        }
    }

    public function Request($method, $data){
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
            $this->_currentMethod = $method;
            $this->_currentRequest = $req;   
        }
        else{
            throw new \Exception("Log in!!!");
        }        
    }

    private function _getErrorByCode($code){
        $code = "{$code}";
        $errors = [
            "900"=> " Success! This API request is deprecated and expected to be removed soon.",
            "1000"=> " Success!",
            "1001"=> " Unknown message received.",
            "1002"=> " Connection to Fishbowl server was lost.",
            "1003"=> " Some requests had errors.",
            "1004"=> " There was an error with the database.",
            "1009"=> " Fishbowl server has been shut down.",
            "1010"=> " You have been logged off the server by an administrator.",
            "1011"=> " Not found.",
            "1012"=> " General error.",
            "1013"=> " Dependencies need to be deleted",
            "1014"=> " Unable to establish network connection.",
            "1015"=> " Your subscription date is greater than your server date.",
            "1016"=> " Incompatible database version.",
            "1100"=> " Unknown login error occurred.",
            "1109"=> " This integrated application registration key is already in use.",
            "1110"=> " A new integrated application has been added to Fishbowl. Please contact the Fishbowl administrator to approve this integrated application.",
            "1111"=> " This integrated application registration key does not match.",
            "1112"=> " This integrated application has not been approved by the Fishbowl administrator.",
            "1120"=> " Invalid username or password.",
            "1130"=> " Invalid ticket passed to Fishbowl server.",
            "1131"=> " Invalid ticket key passed to Fishbowl server.",
            "1140"=> " Initialization token is not correct type.",
            "1150"=> " Request was invalid.",
            "1161"=> " Response was invalid.",
            "1162"=> " The login limit has been reached for the server's key.",
            "1163"=> " Your API session has timed out.",
            "1164"=> " Your API session has been logged out.",
            "1200"=> " Custom field is invalid.",
            "1300"=> " Was not able to find the memo _________.",
            "1301"=> " The memo was invalid.",
            "1400"=> " Was not able to find the order history.",
            "1401"=> " The order history was invalid.",
            "1500"=> " The import was not properly formed.",
            "1501"=> " That import type is not supported.",
            "1502"=> " File not found.",
            "1503"=> " That export type is not supported.",
            "1504"=> " Unable to write to file.",
            "1505"=> " The import data was of the wrong type.",
            "1506"=> " Import requires a header.",
            "1600"=> " Unable to load the user.",
            "1601"=> " Unable to find the user.",
            "2000"=> " Was not able to find the part _________.",
            "2001"=> " The part was invalid.",
            "2002"=> " Was not able to find a unique part.",
            "2003"=> " BOM had an error on the part",
            "2100"=> " Was not able to find the product _________.",
            "2101"=> " The product was invalid.",
            "2102"=> " The product is not unique",
            "2120"=> " The kit item was invalid.",
            "2121"=> " The associated product was invalid.",
            "2200"=> " Yield failed.",
            "2201"=> " Commit failed.",
            "2202"=> " Add initial inventory failed.",
            "2203"=> " Cannot adjust committed inventory.",
            "2204"=> " Invalid quantity.",
            "2205"=> " Quantity must be greater than zero.",
            "2206"=> " Serial number _________ already committed.",
            "2207"=> " Part _________ is not an inventory part.",
            "2208"=> " Not enough available quantity in _________.",
            "2209"=> " Move failed.",
            "2210"=> " Cycle count failed.",
            "2300"=> " Was not able to find the tag number _________.",
            "2301"=> " The tag was invalid.",
            "2302"=> " The tag move failed.",
            "2303"=> " Was not able to save tag number _________.",
            "2304"=> " Not enough available inventory in tag number _________.",
            "2305"=> " Tag number _________ is a location.",
            "2400"=> " Invalid UOM.",
            "2401"=> " UOM _________ not found.",
            "2402"=> " Integer UOM _________ cannot have non-integer quantity.",
            "2403"=> " The UOM is not compatible with the part's base UOM.",
            "2404"=> " Cannot convert to the requested UOM",
            "2405"=> " Cannot convert to the requested UOM",
            "2406"=> " The quantity must be a whole number.",
            "2407"=> " The UOM conversion for the quantity must be a whole number.",
            "2500"=> " The tracking is not valid.",
            "2501"=> " Part tracking not found.",
            "2502"=> " The part tracking name is required.",
            "2503"=> " The part tracking name _________ is already in use.",
            "2504"=> " The part tracking abbreviation is required.",
            "2505"=> " The part tracking abbreviation _________ is already in use.",
            "2506"=> " The part tracking _________ is in use or was used and cannot be deleted.",
            "2510"=> " Serial number is missing.",
            "2511"=> " Serial number is null.",
            "2512"=> " Duplicate serial number.",
            "2513"=> " The serial number is not valid.",
            "2514"=> " Tracking is not equal.",
            "2515"=> " The tracking _________ was not found in location _________' or is committed to another order.",
            "2600"=> " Location _________ not found.",
            "2601"=> " Invalid location.",
            "2602"=> " Location group _________ not found.",
            "2603"=> " Default customer not specified for location _________.",
            "2604"=> " Default vendor not specified for location _________.",
            "2605"=> " Default location for part _________ not found.",
            "2606"=> " _________ is not a pickable location.",
            "2607"=> " _________ is not a receivable location.",
            "2700"=> " Location group not found.",
            "2701"=> " Invalid location group.",
            "2702"=> " User does not have access to location group _________.",
            "3000"=> " Customer _________ not found.",
            "3001"=> " Customer is invalid.",
            "3002"=> " Customer _________ must have a default main office address.",
            "3100"=> " Vendor _________ not found.",
            "3101"=> " Vendor is invalid.",
            "3300"=> " Address not found",
            "3301"=> " Invalid address",
            "4000"=> " There was an error loading PO _________.",
            "4001"=> " Unknown status _________.",
            "4002"=> " Unknown carrier _________.",
            "4003"=> " Unknown QuickBooks class _________.",
            "4004"=> " PO does not have a PO number. Please turn on the auto-assign PO number option in the purchase order module options.",
            "4005"=> " Duplicate order number _________.",
            "4006"=> " Cannot create PO with configurable parts: _________.",
            "4007"=> " The following parts were not added to the purchase order. They have no default vendor:",
            "4008"=> " Unknown type _________.",
            "4100"=> " There was an error loading SO _________.",
            "4101"=> " Unknown salesman _________.",
            "4102"=> " Unknown tax rate _________.",
            "4103"=> " Cannot create SO with configurable parts: _________.",
            "4104"=> " The sales order item is invalid: _________.",
            "4105"=> " SO does not have a SO number. Please turn on the auto-assign SO numbers option in the sales order module options.",
            "4106"=> " Cannot create SO with kit products",
            "4107"=> " A kit item must follow a kit header.",
            "4108"=> " Sales order cannot be found.",
            "4200"=> " There was an error loading BOM _________.",
            "4201"=> " Bill of materials cannot be found.",
            "4202"=> " Duplicate BOM number _________.",
            "4203"=> " The bill of materials is not up to date and must be reloaded.",
            "4204"=> " Bill of materials was not saved.",
            "4205"=> " Bill of materials is in use and cannot be deleted",
            "4206"=> " requires a raw good and a finished good, or a repair.",
            "4207"=> " This change would make this a recursive bill of materials.",
            "4210"=> " There was an error loading MO _________.",
            "4211"=> " Manufacture order cannot be found.",
            "4212"=> " No manufacture order was created. Duplicate order number _________.",
            "4213"=> " The manufacture order is not up to date and must be reloaded.",
            "4214"=> " Manufacture order was not saved.",
            "4215"=> " Manufacture order is closed and cannot be modified.",
            "4220"=> " There was an error loading WO _________.",
            "4221"=> " Work order cannot be found.",
            "4222"=> " Duplicate work order number _________.",
            "4223"=> " The work order is not up to date and must be reloaded.",
            "4224"=> " Work order was not saved.",
            "4300"=> " There was an error loading TO _________.",
            "4301"=> " Unknown status _________.",
            "4302"=> " Unknown carrier _________.",
            "4303"=> " Transfer order cannot be found.",
            "4304"=> " TO does not have a TO number. Please turn on the auto-assign TO number option in the Transfer Order module options.",
            "4305"=> " Duplicate order number _________.",
            "4306"=> " Unknown type _________.",
            "4307"=> " Transfer order was not saved.",
            "4308"=> " The transfer order is not up to date and must be reloaded.",
            "5000"=> " There was a receiving error.",
            "5001"=> " Receive ticket invalid.",
            "5002"=> " Could not find a line item for part number _________.",
            "5003"=> " Could not find a line item for product number _________.",
            "5004"=> " Not a valid receive type.",
            "5005"=> " The receipt is not up to date and must be reloaded.",
            "5006"=> " A location is required to receive this part. Part num: _________",
            "5007"=> " Cannot receive or reconcile more than the quantity ordered on a TO.",
            "5008"=> " Receipt not found _________.",
            "5100"=> " Pick invalid",
            "5101"=> " Pick not found _________.",
            "5102"=> " Pick not saved.",
            "5103"=> " An order on pick _________ has a problem.",
            "5104"=> " Pick item not found _________.",
            "5105"=> " Could not finalize pick. Quantity is not correct.",
            "5106"=> " The pick is not up to date and must be reloaded.",
            "5107"=> " The part in tag _________ does not match part _________.",
            "5108"=> " Incorrect slot for this item. Item must be placed with others for this order.",
            "5109"=> " Wrong number of serial numbers sent for pick.",
            "5110"=> " Pick items must be started to assign tag.",
            "5111"=> " Order must be picked from location group _________.",
            "5112"=> " The item must be picked from _________.",
            "5200"=> " Shipment invalid",
            "5201"=> " Shipment not found _________.",
            "5202"=> " Shipment status error",
            "5203"=> " Unable to process shipment.",
            "5204"=> " Carrier not found _________.",
            "5205"=> " The shipment _________ has already been shipped.",
            "5206"=> " Cannot ship order _________. The customer has a ship hold.",
            "5207"=> " Cannot ship order _________. The vendor has a ship hold.",
            "5300"=> " Could not load RMA.",
            "5301"=> " Could not find RMA.",
            "5400"=> " Could not take payment.",
            "5500"=> " Could not load the calendar.",
            "5501"=> " Could not find the calendar.",
            "5502"=> " Could not save the calendar.",
            "5503"=> " Could not delete the calendar.",
            "5504"=> " Could not find the calendar activity.",
            "5505"=> " Could not save the calendar activity.",
            "5506"=> " Could not delete the calendar activity.",
            "5507"=> " The start date must be before the stop date.",
            "6000"=> " Account invalid",
            "6001"=> " Discount invalid",
            "6002"=> " Tax rate invalid",
            "6003"=> " Accounting connection failed",
            "6005"=> " Accounting system not defined",
            "6006"=> " Accounting brought back a null result",
            "6007"=> " Accounting synchronization error",
            "6008"=> " The export failed",
            "6009"=> " Fishbowl and Quickbooks multiple currency features don't match",
            "6010"=> " The data validation for the export has failed.",
            "6011"=> " Accounting integration is not configured. Please reintegrate.",
            "6100"=> " Class already exists",
            "7000"=> " Pricing rule error",
            "7001"=> " Pricing rule not found",
            "7002"=> " The pricing rule name is not unique",
            "8000"=> " Unknown FOB _________.",
        ];
        if(isset($errors[$code])){
            return $errors[$code];
        }
        return "Unknown error :-(";
    }

    public function Response($fail=false){
        $result = [
            "code"=>false,
            "message"=>false,
            "is_error"=>false,
            "data"=>false
        ];
        $goodCodes = [900, 1000];
        $packed_len = stream_get_contents($this->_connection, 4); 
        if ($packed_len != '') {
            $hdr = unpack('Nlen', $packed_len);
            $_len = $hdr['len'];
            $res = json_decode(utf8_encode(stream_get_contents($this->_connection, $_len)),true);
            if($this->_currentMethod=="LoginRq" && isset($res["FbiJson"]["Ticket"]["Key"])){
                return $res["FbiJson"]["Ticket"]["Key"];
            }
            if(isset($res["FbiJson"]["FbiMsgsRs"])){
                if(isset($res["FbiJson"]["FbiMsgsRs"]["statusCode"])){
                    $result["code"]=$res["FbiJson"]["FbiMsgsRs"]["statusCode"];                
                    if(!in_array($res["FbiJson"]["FbiMsgsRs"]["statusCode"], $goodCodes)){
                        $result["is_error"] = true;
                        $result["sended_data"] = $this->_currentRequest;
                        $result["readed_data"] = $res; 
                    }
                }
                if(isset($res["FbiJson"]["FbiMsgsRs"]["statusMessage"])){
                    $result["message"] = $res["FbiJson"]["FbiMsgsRs"]["statusMessage"];
                }
                $result["message"] = $this->_getErrorByCode($result["code"]);
                $mayBeResName = preg_replace("/Rq/", "Rs", $this->_currentMethod);
                if(isset($res["FbiJson"]["FbiMsgsRs"][$this->_currentMethod])){
                    $result["data"] = $res["FbiJson"]["FbiMsgsRs"][$this->_currentMethod];
                }
                elseif(isset($res["FbiJson"]["FbiMsgsRs"][$mayBeResName])){
                    $result["data"] = $res["FbiJson"]["FbiMsgsRs"][$mayBeResName];
                }
                else{
                    $result["data"] = $res; 
                }
            }            
        }
        if($fail){
            if($result["is_error"]){
                var_dump($result);
                $this->Logout();
                throw new \Exception("[Fishbowl API JSON] {$result["code"]} : {$result["message"]}");
            }
        }
        return $result;
    }

    public function Logout(){
        fclose($this->_connection);
        $this->_next = 'SetHostInfo';
    }
}
