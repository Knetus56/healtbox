<?php
class healtbox_api {

	protected $_url_data = 'https://iam-api.dss.husqvarnagroup.net/api/v3/';
	protected $_url_boost = 'https://amc-api.dss.husqvarnagroup.net/v1/';
	protected $_ip;
	protected $_data;
	protected $_dataBoost;

	public function __construct($ip)
    {
        $this->setip($ip);
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
            CURLOPT_URL => "http://". $this->getip()."/v2/api/data/current",            
            CURLOPT_RETURNTRANSFER => true,   
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ]);
       
		$json = curl_exec($session);
		curl_close($session);
		return json_decode($json, true);
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