<?php

namespace Nuvei;
use Functions\Post\Nuvei_Config;
use Functions\Post\Nuvei_Db;
use Functions\Post\Nuvei_Post;
use Functions\Post\Nuvei_Response;
// use Functions\Settings\Checkup;

class Nuvei {

	const ADDRESS = array(
		'ADDRESS1' => 'ADDRESS1',
		'ADDRESS2' => 'ADDRESS2',
		'COUNTRY' => 'COUNTRY',
		'REGION' => 'REGION',
		'CITY' => 'CITY',
		'POSTCODE' => 'POSTCODE',
		'IPADDRESS' => 'IPADDRESS',
		'PHONE' => 'PHONE',
		'EMAIL' => 'EMAIL'
	);

	public static function buildAddressObject($address1 = null ,$address2 = null ,$country = null ,$region = null ,$city = null, $postcode = null ,$ip = null ,$phone = null ,$email = null){
		return [
			self::ADDRESS['ADDRESS1'] => $address1,
			self::ADDRESS['ADDRESS2'] => $address2,
			self::ADDRESS['COUNTRY'] => $country,
			self::ADDRESS['REGION'] => $region,
			self::ADDRESS['CITY'] => $city,
			self::ADDRESS['POSTCODE'] => $postcode,
			self::ADDRESS['IPADDRESS'] => $ip,
			self::ADDRESS['PHONE'] => $phone,
			self::ADDRESS['EMAIL'] => $email
		];
	}

	public $_mode = 'test';
	public $_config = array();
	public $_paymentParams = array();
	public $_normalizedPaymentParams = array();
	public $_responseParams = array();
	public $_normalizedResponseParams = array();
	public $_paymentURL = '';
	public $_secret = '';
	public $_hash = '';
	public $_responseHash = '';
	public $_terminal = '';
	public $_dateTime = '';
	public $Nuvei_Config;
	public $Nuvei_Post;
	public $Nuvei_Format;

	public $saveToDatabase;

	public $_dbConfig = array(
		'server' => 'localhost',
		'login' => 'root',
		'password' => 'root',
		'database' => 'myDB'
	);

	public $_txnConfig = array(
		'table' => 'transactions',
		'fieldMapping' => array(
			'TXNID' => 'id',
			'UNIQUEREF' => 'uniqueRef',
			'RESPONSECODE' => 'responseCode',
			'RESPONSETEXT' => 'responseText',
			'APPROVALCODE' => 'approvalCode',
			'DATETIME' => 'dateTime',
			'AVSRESPONSE' => 'AVSResponse',
			'CVVRESPONSE' => 'CVVResponse',
			'AMOUNT' => 'amount',
			'CURRENCY' => 'currency',
			'HASH' => 'hash',
			'STATUS' => 'status',
			'ERRORSTRING' => 'error',
			'ORDERID' => 'order_id',
			'CARDHOLDERNAME' => 'payer'
		)
	);

	public $_orderConfig = array(
		'table' => 'orders',
		'fieldMapping' => array(
			'ORDERID' => 'id',
			'TXNID' => 'txn_id',
			'AMOUNT' => 'amountPaid',
			'CURRENCY' => 'currency',
			'DATETIME' => 'paymentDate',
			'STATUS' => 'paymentStatus'
		)
	);


	public function __construct($terminal,$mode = 'test',$overrideurl = false){
		\Functions\Settings\Checkup::check();

		$this->setMode($mode);

		$this->setEnvironment($terminal);

	}

	public static function makeTerminalObject($terminal,$secret,$currency = "USD"){
		return [
			'TerminalID' => $terminal,
			'SharedSECRET' => $secret,
			'Currency' => $currency
		];
	}


	public function saveToDatabase($toggle = true){
		$this->saveToDatabase = $toggle;
	}

	public static function send($config,$params,$mode = 'test'){
		return (new Nuvei($config,$mode))->sendPayment($params);
	}

	private function alerterParams($params){
		if(!array_key_exists("ORDERID", $params)){
			$params["ORDERID"] = uniqid() . time();
		}
		if(!array_key_exists("PAYMENTTYPE", $params)){
			$params["PAYMENTTYPE"] = "card";
		}
		return $params;
	}

	public function sendPayment($params){

		$this->_paymentParams = $this->alerterParams($params);

		$this->Nuvei_Post = new Nuvei_Post($this->_paymentURL, $this->_paymentParams,$this->Nuvei_Config);
		$out = $this->Nuvei_Post->sendPayment();

		if($out['STATUS'] == false && isset($out['ERRORSTRING']) && !strpos($out['ERRORSTRING'],'#')===false){
			$error = $out['ERRORSTRING'];

			if(!strpos($error,'#AnonType_CARDNUMBER')===false){
				$out['ERRORSTRING'] = 'wrong card number (must be at least 10 digits)';
			}
		}

		if($this->saveToDatabase){
			$this->Nuvei_Db = new Nuvei_Db($this->_dbConfig, $this->_txnConfig, $this->_orderConfig);
			$this->Nuvei_Db->saveTxnAndOrder($this->Nuvei_Post->getNormalizedPaymentParams()+$out);
			$out['TXNID'] = $this->Nuvei_Db->_txnId;
		}

		return new Nuvei_Response($out,$params["PAYMENTTYPE"]);
	}

	public function setMode($mode = 'test'){
		if($mode === 1){
			$mode = "live";
		}
		if($mode != 'live'){
			$mode = "test";
		}
		$this->_mode = $mode;
	}


	public function setEnvironment($terminal){
		$this->Nuvei_Config = new Nuvei_Config($terminal,$this->_mode);
		$this->_paymentURL = $this->Nuvei_Config->getUrl();
	}

	public function setPaymentURL($URL = ''){
		$this->_paymentURL = $URL;
	}

	public function buildHash(){
		$this->setHash($this->createHash());
		return($this->_hash);
	}






	public function setDateTime($dateTime=''){
		$this->_dateTime = $dateTime;
	}

	public function getDateTime(){
		return $this->_dateTime;
	}

	public function getPaymentURL(){
		return $this->_paymentURL;
	}

	public function setHash($hash = ''){
		$this->_hash = $hash;
	}

	public function getHash(){
		return $this->_hash;
	}

	public function setSecret($secret = ''){
		$this->_secret = $secret;
	}

	public function getSecret($secret = ''){
		return $this->_secret;
	}

	public function setTerminal($terminalId = ''){
		$this->_terminalId = $terminalId;
	}

	public function getTerminal($terminalId = ''){
		return $this->_terminalId;
	}

	public function getMode(){
		return $this->_mode;
	}

	public function setPaymentParams($params = array()){
		$this->_paymentParams = $params;
	}

	public function getPaymentParams(){
		return $this->_paymentParams;
	}

	public function setNormalizedPaymentParams($normalizedPaymentParams){
		$this->_normalizedPaymentParams = $normalizedPaymentParams;
	}

	public function getNormalizedPaymentParams(){
		return $this->_normalizedPaymentParams;
	}

	public function setResponseParams($responseParams){
		$this->_responseParams = $responseParams;
	}

	public function getResponseParams(){
		return $this->_responseParams;
	}

	public function setNormalizedResponseParams($responseParams){
		$this->_normalizedResponseParams = $responseParams;
	}

	public function getNormalizedResponseParams(){
		return $this->_normalizedResponseParams;
	}

	public function setResponseHash($responseHash){
		$this->_responseHash = $responseHash;
	}

	public function getResponseHash(){
		return $this->_responseHash;
	}

	public function setCurrencyTerminalId($currency){
		$this->setTerminalId($this->readCurrencyTerminal($currency));
	}

	public function setDbConfig($dbConfig){
		$this->_dbConfig = $dbConfig + $this->_dbConfig;
	}

	public function getDbConfig(){
		$this->_dbConfig;
	}
	public function setTxnConfig($txnConfig){
		$this->_txnConfig = $txnConfig + $this->_txnConfig;
	}

	public function getTxnConfig(){
		$this->_txnConfig;
	}

	public function setOrderConfig($orderConfig){
		$this->_orderConfig = $orderConfig + $this->_orderConfig;
	}

	public function getOrderConfig(){
		$this->_orderConfig;
	}

}
