<?php
namespace Functions\Post;

class Nuvei_Response{


	protected $success = false;
	protected $responseType;
	protected $response;

	protected $error_message = false;

	const ACH_SUCCESS = 2;
	const ACH_ERROR = 4;
	const CARD_ERROR = 3;
	const CARD_SUCCESS = 1;
	const ERROR = 0;
	const EXCEPTION = -1;


	public function __construct($re,$paymenttype){
		$this->response = $re;
		try{

			if($paymenttype == 'check' || $paymenttype == 'ach'){
				$this->parseAchResponse($re);
			}else{
				$this->parseCardResponse($re);
			}
		}catch(Exception $e){
			$this->setResponseType(self::EXCEPTION);
		}
	}

	private function parseAchResponse($re){
		if(isset($re['RESPONSECODE'])){
			if(isset($re['RESPONSECODE']) && $re['RESPONSECODE'] === "E"){
				$this->setResponseType(self::ACH_SUCCESS);
			}else{
				$this->error_message = $re['RESPONSETEXT'];
				$this->setResponseType(self::ACH_ERROR);
			}
		}else{
			$this->parseErrorResponse($re);
		}
	}

	private function parseErrorResponse($re){
		if(!isset($re['ERRORSTRING'])){
			throw new Exception("Error Processing Request", 1);
		}
		$this->error_message = $re['ERRORSTRING'];
		$this->setResponseType(self::ERROR);
	}

	private function parseCardResponse($re){
		if(isset($re['RESPONSECODE']) && $re['RESPONSECODE'] === "A"){
			$this->setResponseType(self::CARD_SUCCESS);
		}else{
			$this->parseErrorResponse($re);
		}
	}

	private function setResponseType($type){
		$this->responseType = $type;
		if($type == 1 || $type == 2){
			$this->success = true;
		}
	}

}
