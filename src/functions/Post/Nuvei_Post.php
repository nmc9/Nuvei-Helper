<?php
namespace Functions\Post;

Class Nuvei_Post{

	var $_paymentURL ;
	var $_paymentParams ;
	var $_xml ;
	var $_terminal ;
	var $_postHash ;
	var $_postDateTime ;
	var $_normalizedPaymentParams ;
	var $_normalizedPaymentReponse ;
	var $Nuvei_Config ;

	public function __construct($paymentURL,$paymentParams,$Nuvei_Config){
		$this->_paymentURL = $paymentURL;
		$this->_paymentParams = $paymentParams;
		$this->_postDateTime = date('d-m-Y:H:i:s').':000';
		$this->Nuvei_Config = $Nuvei_Config;
	}

	public function sendPayment(){
		$curl = new Nuvei_Curl();

		//get the config
		$this->_terminal = $this->Nuvei_Config->getTerminal();


		//hash
		$hash = new Nuvei_Hash($this->_paymentParams,$this->_terminal,$this->_postDateTime);

		//get xml for post
		$format = new Nuvei_Format($this->Nuvei_Config,$this->_paymentParams,$this->_terminal,$hash->getPostHash(),$this->_postDateTime);
		$this->_normalizedPaymentParams = $format->getNormalizedPaymentParams();
		$this->_xml = $format->getXML();


		//send
		$request = $curl->curlXmlRequest($this->_paymentURL,$this->_xml);

		$format->setPaymentResponse($request);
		$normalizedPaymentReponse = $format->normalizePaymentReponse();

		$normalizedPaymentReponse['STATUS'] = $hash->controlResponseHash($normalizedPaymentReponse);
		$this->_normalizedPaymentReponse = $normalizedPaymentReponse;
		return $normalizedPaymentReponse;

	}

	public function getXML(){
		return $this->_xml;
	}

	public function getNormalizedPaymentReponse(){
		return $this->_normalizedPaymentReponse;
	}

	public function getNormalizedPaymentParams(){
		return $this->_normalizedPaymentParams;
	}

}
