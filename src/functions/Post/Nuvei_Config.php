<?php
namespace Functions\Post;

class Nuvei_Config{

	public $mode = 'test';
	private $config_dir = "/../../data/";
	private $terminal = [
		'TerminalID' => '',
		'SharedSECRET' => '',
		'currency', 'USD'
	];


	public function __construct($terminal,$mode){
		//parent::__construct($mode);
		$this->terminal = $terminal;
		$this->mode = $mode;
	}

	public function readConfigData($configFile){
		$file = file_get_contents(__DIR__ . $this->config_dir . $configFile . ".json");
		$data = json_decode($file,1);
		return $data;
	}

	public function getUrl(){
		return $this->readConfigData('Url')[$this->mode];
	}


	public function readTestCards(){
		$cards = $this->readConfigData('TestCards');
		return $cards;
	}

	public function getTerminal(){
		return $this->terminal;
	}

	public function readFields($paymenttype = "card"){
		if($paymenttype == "check" || $paymenttype == "ach"){
			return $this->readConfigData('ACHFields');
		}else{
			return $this->readConfigData('Fields');
		}
	}

	public function readCurrencyTerminal($currency = 'USD'){

		$currency = strtolower($currency);

		$terminals = $this->getTerminal();

		$terminals = $terminals[$this->mode];

		$rterminal = array();

		$multiCurrencyTerminal = array();

		foreach($terminals as $terminal):
			if(strtolower($terminal['Currency']) == $currency):
				$rterminal = $terminal;
			endif;

			if(strtolower($terminal['Currency']) == 'MCP'):
				$multiCurrencyTerminal = $terminal;
			endif;


		endforeach;

		if($rterminal==''):
			$rterminal = $multiCurrencyTerminal;
		endif;

		return $rterminal;
	}

	public function readVendorTestCard($vendor = ''){

		$vendor = strtolower($vendor);

		$cards = $this->readTestCards();

		$rcard = array();

		foreach($cards as $card):
			if(strtolower($card['Vendor']) == $vendor):
				$rcard = $card;
			endif;
		endforeach;

		return $rcard;
	}

	private function cleanCardNumber($cardNumber = ''){
		$cardNumber = str_replace('-' , '', $cardNumber);
		$cardNumber = str_replace(' ' , '', $cardNumber);

		return $cardNumber;
	}

	public function getCardType($cardNumber){
		$cardNumber = $this->cleanCardNumber($cardNumber);

		$cardsPatterns = $this->readConfigData('CardTypes');

		$rcardtype = 'UNKNOWN';

		foreach($cardsPatterns as $cardPattern):

			$pattern = $cardPattern['Pattern'];

			if($pattern != 'unknown'):
				$pattern = '/'.$pattern.'/';
				if(preg_match($pattern, $cardNumber)):
					$rcardtype = $cardPattern['Vendor'];
				endif;
			endif;

		endforeach;

		return strtoupper($rcardtype);
	}
}
