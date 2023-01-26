<?php
class healtbox_api {

	protected $_ip;
	protected $_url_data = '/v2/api/data/current';
	protected $_url_boost = '/v2/api/boost/';
	protected $_data;
	protected $_dataBoost;

	public function __construct($ip)
    {
        $this->setip($ip);
        $this->getData();
    }

    public function setip($ip)
    {
        $this->_IP = $ip;
    }

    public function getip()
    {
        return $this->_IP;
    }


    public function getData(){
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://". $this->getip().$this->_url_data,            
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