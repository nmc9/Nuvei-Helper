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
			if($paymenttype == 'force'){
				throw new \Exception("Error Processing Request", 1);
			}
			if($paymenttype == 'check' || $paymenttype == 'ach'){
				$this->parseAchResponse($re);
			}else{
				$this->parseCardResponse($re);
			}
		}catch(\Exception $e){
			$this->setResponseType(self::EXCEPTION);
		}
	}

	public function get_data(){
		if($this->responseType == self::EXCEPTION){
			return null;
		}
		return $this->response;
	}

	public function get_message(){
		return $this->error_message;
	}

	public function isSuccess(){
		return $success;
	}

	private function setError($error_message){
		$this->error_message = $error_message;
	}

	public static function FAILURE($error_message){
		$failure = new self([],'force');
		$failure->setError($error_message);
		return $failure;
	}

	private function parseAchResponse($re){
		$error_message = isset($re['RESPONSETEXT']) ? $re["RESPONSETEXT"] : false;
		if(isset($re['RESPONSECODE'])){
			if(isset($re['RESPONSECODE']) && $re['RESPONSECODE'] === "E"){
				$this->setResponseType(self::ACH_SUCCESS);
			}else{
				$this->setResponseType(self::ACH_ERROR,$error_message);
			}
		}else{
			$this->parseErrorResponse($re,$error_message);
		}
	}

	private function parseCardResponse($re){
		$error_message = isset($re['RESPONSETEXT']) ? $re["RESPONSETEXT"] : false;
		if(isset($re['RESPONSECODE'])){
			switch ($re['RESPONSECODE']) {
				case "A":
				$this->setResponseType(self::CARD_SUCCESS);
				break;

				case "D":
				case "E":
				case "R":
				$this->setResponseType(self::CARD_ERROR,$error_message);
				break;

				default:
				$this->parseErrorResponse($re,$error_message);
				break;
			}
		}else{
			$this->parseErrorResponse($re,$error_message);
		}
	}
	private function parseErrorResponse($re,$fallbackError = false){
		if(!isset($re['ERRORSTRING'])){
			throw new \Exception("Error Processing Request", 1);
		}
		$this->setResponseType(self::ERROR,$fallbackError ?: $re['ERRORSTRING']);
	}

	private function setResponseType($type,$error_message = false){
		$this->responseType = $type;
		if($type == 1 || $type == 2){
			$this->success = true;
		}else{
			$this->error_message = $error_message;
		}
	}

}
