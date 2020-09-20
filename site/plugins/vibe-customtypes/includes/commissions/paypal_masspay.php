<?php

  
  class Masspay_Paypal {

		public function __construct($username, $password, $signature) {
			$this->username = urlencode($username);
			$this->password = urlencode($password);
			$this->signature = urlencode($signature);
			$this->version = urlencode("51.0");
			$this->api = "https://api-3t.paypal.com/nvp";

			//The functions can be modified but need to be urlencoded
			$this->type = urlencode("EmailAddress");
			$this->currency = urlencode("USD");
			$this->subject = urlencode("Instant Paypal Payment");
		}

		public function pay($email, $amount, $note="Instant Payment") {
			$string = "&EMAILSUBJECT=".$this->subject."&RECEIVERTYPE=".$this->type."&CURRENCYCODE=".$this->currency;
			$string .= "&L_EMAIL0=".urlencode($email)."&L_Amt0=".urlencode($amount)."&L_NOTE0=".urlencode($note);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->api);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			$request = "METHOD=MassPay&VERSION=".$this->version."&PWD=".$this->password."&USER=".$this->username."&SIGNATURE=".$this->signature."$string";

			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			$httpResponse = curl_exec($ch);
			if(!$httpResponse) {
				exit("MassPay failed: ".curl_error($ch).'('.curl_errno($ch).')');
			}

			$httpResponseArray = explode("&", $httpResponse);
			$httpParsedResponse = array();
			foreach ($httpResponseArray as $i => $value) {
				$tempArray = explode("=", $value);
				if(sizeof($tempArray) > 1) {
					$httpParsedResponse[$tempArray[0]] = $tempArray[1];
				}
			}

			if((0 == sizeof($httpParsedResponse)) || !array_key_exists('ACK', $httpParsedResponse)) {
				exit("Invalid HTTP Response for POST request($request) to ".$this->api);
			}

			return $httpParsedResponse;
		}

	}
?>