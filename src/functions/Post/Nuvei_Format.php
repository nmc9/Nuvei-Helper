<?php
namespace Functions\Post;

use \Nuvei\Nuvei;

class Nuvei_Format{
	protected $_paymentParams;
	protected $_paymentResponse;
	protected $_normalizedPaymentReponse;
	protected $_normalizedPaymentParams;
	protected $_dateTime;
	protected $_terminal;
	protected $_postedHash;
	protected $_postDateTime;
	protected $_xml;
	// protected $Nuvei_Config;

	protected $paymenttype;

	public function __construct($Nuvei_Config,$paymentParams,$terminal,$postHash,$postDateTime){
		$this->Nuvei_Config = $Nuvei_Config;
		$this->_paymentParams = $paymentParams;
		$this->_postHash = $postHash;
		$this->_terminal = $terminal;
		$this->_postDateTime = $postDateTime;
		$this->paymenttype = $paymentParams["PAYMENTTYPE"];
		if($this->paymenttype == "check" || $this->paymenttype == "ach"){
			$this->prepareAchPaymentParameter();
		}else{
			$this->prepareCardPaymentParameter();
		}
		$this->preparePaymentXML();

	}

	public function setPaymentParams($paymentParams){
		$this->_paymentParams = $paymentParams;
	}

	public function getPaymentParams(){
		return $this->_paymentParams;
	}
	public function setXML($xml){
		$this->_xml = $xml;
	}

	public function getXML(){
		return $this->_xml;
	}

	public function setNormalizedPaymentParams($paymentParams){
		$this->_normalizedPaymentParams = $paymentParams;
	}

	public function getNormalizedPaymentParams(){
		return $this->_normalizedPaymentParams;
	}

	public function setTerminal($terminal){
		$this->_terminal = $terminal;
	}

	public function getTerminal(){
		return $this->_terminal;
	}

	public function setPostHash($hash){
		$this->_postHash = $hash;
	}
	public function getPostHash($hash){
		return $this->_postHash;
	}

	public function setDateTime($dateTime){
		$this->_dateTime = $dateTime;
	}

	public function getDateTime(){
		return $this->_dateTime;
	}

	public function setPaymentResponse($paymentResponse){
		$this->_paymentResponse = $paymentResponse;
	}

	public function getPaymentResponse(){
		return $this->_paymentResponse;
	}

	public function normalizePaymentReponse(){
		$this->_normalizedPaymentReponse = $this->XMLToArray($this->_paymentResponse);
		if(isset($this->_normalizedPaymentReponse["DATETIME"])  && $this->_normalizedPaymentReponse["DATETIME"] === array()){
			$this->_normalizedPaymentReponse["DATETIME"] = date('Y-m-d\TH:i:s');
		}
		return $this->_normalizedPaymentReponse;
	}

	private function preparePaymentXML(){
		$xmlStructure = $this->Nuvei_Config->readFields($this->paymenttype);

		$out = '<?xml version="'.$xmlStructure['XMLHeader']['version'].'" encoding="'.$xmlStructure['XMLHeader']['encoding'].'"?>';

		$out .= '<'.$xmlStructure['XMLEnclosureTag'].'>';

		$params = $this->_normalizedPaymentParams;

		foreach($params as $key=>$param){
			if($key == "CUSTOM"){
				foreach ($param as $custom_key => $custom_param) {
					$tag = strtoupper($custom_key);
					$out .= '<CUSTOMFIELD NAME="'.$tag.'">'.$custom_param.'</CUSTOMFIELD>';
				}
			}else{
				$tag = strtoupper($key);
				$out .= '<'.$tag.'>'.$param.'</'.$tag.'>';
			}
		}
		$out .= '</'.$xmlStructure['XMLEnclosureTag'].'>';

		$this->_xml = $out;
		return $out;
	}

	private function cleanExpiryDate($month = '',$year = ''){
		if(strlen($year)>2){
			$year = substr($year, 2,2);
		}

		$date = $month.$year;

		return $date;
	}

	private function cleanCardNumber($cardNumber = ''){
		$cardNumber = str_replace('-' , '', $cardNumber);
		$cardNumber = str_replace(' ' , '', $cardNumber);

		return $cardNumber;
	}

	public function getCardType($cardNumber){
		$rcardtype = $this->Nuvei_Config->getCardType($cardNumber);
		return strtoupper($rcardtype);
	}

	private function prepareCardPaymentParameter(){
		$params = $this->_paymentParams;
		$this->_terminal = $this->_terminal;
		$out = array();

		$out['ORDERID'] = $params['ORDERID'];
		$out['TERMINALID'] = $this->_terminal['TerminalID'];
		$out['AMOUNT'] = $params['AMOUNT'];

		$out['DATETIME'] = $this->_postDateTime;
		$out['CARDNUMBER'] = $this->cleanCardNumber($params['CARDNUMBER']);
		$out['CARDTYPE'] = $this->getCardType($params['CARDNUMBER']);
		$out['CARDEXPIRY'] = $this->cleanExpiryDate($params['MONTH'],$params['YEAR']);
		$out['CARDHOLDERNAME'] = $params['CARDHOLDERNAME'];

		$out['HASH'] = $this->_postHash;
		$out['CURRENCY'] = $params['CURRENCY'];
		$out['TERMINALTYPE'] = 2;
		$out['TRANSACTIONTYPE'] = 7;
		$out['CVV'] = $params['CVV'];

		$this->prepareAddress($out,$params);
		$customs = $this->getCustoms($params);
		if($customs){
			$out['CUSTOM'] = $customs;
		}
		$this->_normalizedPaymentParams = $out;
		return $out;
	}

	public function prepareAddress(&$output,$params){
		if(isset($params['ADDRESS'])){
			$addressObject = $params['ADDRESS'];
			foreach (Nuvei::ADDRESS as $ak) {
				if($addressObject[$ak] != null){
					$output[$ak] = $addressObject[$ak];
				}
			}
		}
	}



	private function prepareAchPaymentParameter(){
		$params = $this->_paymentParams;
		$this->_terminal = $this->_terminal;
		$out = array();



		$out['ORDERID'] = $params['ORDERID'];
		$out['TERMINALID'] = $this->_terminal['TerminalID'];
		$out['AMOUNT'] = $params['AMOUNT'];
		$out['CURRENCY'] = $params['CURRENCY'];
		$out['DATETIME'] = $this->_postDateTime;
		$out['TERMINALTYPE'] = 2;
		$out['SEC_CODE'] = "WEB";
		$out['ACCOUNT_TYPE'] = $params['ACCOUNT_TYPE'];
		$out['ACCOUNT_NUMBER'] = $params['ACCOUNT_NUMBER'];
		$out['ROUTING_NUMBER'] = $params['ROUTING_NUMBER'];
		$out['ACCOUNT_NAME'] = $params['ACCOUNT_NAME'];

		if(isset($params['CHECK_NUMBER'])){
			$out['CHECK_NUMBER'] = $params['CHECK_NUMBER'] ;
		}

		// $out['ADDRESS1'] = "7th Avenu, 77";
		// $out['ADDRESS2'] = "5th Avenu, 13";
		// $out['CITY'] = "NEW YORK";
		// $out['REGION'] = "A1";
		// $out['POSTCODE'] = "117898";
		// $out['COUNTRY'] = "US";
		// $out['PHONE'] = "9563343234";
		// $out['IPADDRESS'] = "192.168.0.1";

		// $out['EMAIL'] = "test@GlobalOnePay.com";
		// $out['DESCRIPTION'] = "test";
		if(isset($params["DL_NUMBER"])){
			$out['DL_STATE'] = $params['DL_STATE'];
			$out['DL_NUMBER'] = $params['DL_NUMBER'];
		}

		$out['HASH'] = $this->_postHash;

		$this->_normalizedPaymentParams = $out;

		return $out;

	}

	private function getCustoms($params){
		$customs = [];
		foreach ($params as $key => $value) {
			if(substr($key, 0, 7) === "CUSTOM_"){
				$customs[substr($key,7)] = $value;
			}
		}
		return $customs;
	}

	public function XMLToArray($xml,$main_heading = '') {
		$deXml = simplexml_load_string($xml);
		$deJson = json_encode($deXml);

		$xml_array = json_decode($deJson,TRUE);

		if (! empty($main_heading)){
			$returned = $xml_array[$main_heading];
			return $returned;
		}else
		{
			return $xml_array;
		}
	}

}
