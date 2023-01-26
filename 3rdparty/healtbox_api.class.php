<?php

require_once __DIR__  . '/../../../../core/php/core.inc.php';
class healtbox_api {

	protected $_ip;
	protected $_url_data = '/v2/api/data/current';
	protected $_url_boost = '/v2/api/boost/';
	protected $_data;
	protected $_dataBoost;

	public function __construct($ip)
    {
       
        log::add('healtbox','info', $ip );
        $this->_data = "";
        $this->_dataBoost = "";
        $this->setip($ip);
        $this->getData();
    }

    public function setip($ip)
    {
        $this->_ip = $ip;
    }

    public function getip()
    {
        return $this->_ip;
    }


    public function getData(){
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://". $this->_ip . $this->_url_data,            
            CURLOPT_RETURNTRANSFER => true,   
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ]);
       
		$json = curl_exec($session);
		curl_close($session);
		$this->_data = json_decode($json, true);
    }
 

    public function getNbPiece(){
       
        return count($this->_data['room']);
    } 
   
    public function getNamePiece($i){
       
        log::add('healtbox','info', $i );
        return $this->_data['room'][$i]['name']);
    } 
    
   
   
   
   
   
   
    /*
    * Méthode: PUT
    * URL: https://api.healtbox.fr/public/v1/device/{deviceId}/pump
    * {deviceId} est à remplacer par le numéro unique d’association ou par le mot clef my.
    * pump peut prendre comme valeur « on », « off », et « auto »
    */
   public function putPompe(string $value){
       $data = ["pump" => $value];
       $curl = curl_init();

       curl_setopt_array($curl, [
           CURLOPT_URL => "https://api.healtbox.fr/public/v1/device/my/pump",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_CUSTOMREQUEST => "PUT",
           CURLOPT_POSTFIELDS => json_encode($data),
           CURLOPT_HTTPHEADER => [
               "X-API-TOKEN: ". $this->getApiToken(),
               "Content-Type: application/json"
           ]
       ]);

       $response = curl_exec($curl);

       curl_close($curl);
       return $response;
   }

   
}
?>