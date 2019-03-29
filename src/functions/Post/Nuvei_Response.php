<?php
namespace Functions\Post;

class Nuvei_Response{


	protected $success = false;
	protected $responseType;
	protected $response = [];
	protected $code;

	protected $error_message = false;
	protected $card = false;

	const ACH_SUCCESS = 2;
	const ACH_ERROR = 4;
	const CARD_ERROR = 3;
	const CARD_SUCCESS = 1;
	const ERROR = 0;
	const EXCEPTION = -1;


	public function __construct($re,$paymenttype,$card = false){
		$this->response = $re;
		$this->card = $card != null ? $card : false;
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

	public function get_code(){
		return $this->code;
	}

	public function get_data(){
		if($this->responseType == self::EXCEPTION){
			return [];
		}
		return $this->response;
	}

	public function get_card(){
		return $this->card;
	}

	public function get_message(){
		return $this->error_message;
	}

	public function isSuccess(){
		return $this->success;
	}

	private function get_value($key){
		if($this->responseType > 0  && isset($this->response[$key])){
			return $this->response[$key];
		}
		return null;
	}

	public function get_unique_id(){
		return $this->get_value('UNIQUEREF');
	}

	public function get_response_code(){
		return $this->get_value('RESPONSECODE');
	}

	public function get_response_text(){
		return $this->get_value('RESPONSETEXT');
	}

	public function get_approval_code(){
		return $this->get_value('APPROVALCODE');
	}

	public function get_datetime(){
		return $this->get_value('DATETIME');
	}

	public function get_hash(){
		return $this->get_value('HASH');
	}

	public function get_cvv_response(){
		if($this->responseType == self::CARD_SUCCESS){
			return $this->get_value('CVVRESPONSE');
		}
	}

	public function get_avs_response(){
		if($this->responseType == self::CARD_SUCCESS){
			return $this->get_value('AVSRESPONSE');
		}
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
				$this->setResponseType(self::ACH_SUCCESS,false,$re['RESPONSECODE']);
			}else{
				$this->setResponseType(self::ACH_ERROR,$error_message,$re['RESPONSECODE']);
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
				$this->setResponseType(self::CARD_SUCCESS,false,$re['RESPONSECODE']);
				break;

				case "D":
				case "E":
				case "R":
				$this->setResponseType(self::CARD_ERROR,$error_message,$re['RESPONSECODE']);
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

	private function setResponseType($type,$error_message = false,$code = 999){
		$this->responseType = $type;
		$this->code = $code;
		if($type == 1 || $type == 2){
			$this->success = true;
		}else{
			$this->error_message = $error_message;
		}
	}

}
