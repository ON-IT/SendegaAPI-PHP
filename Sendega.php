<?php
class Sendega {

	private $base = "http://smsc.sendega.com/content.asmx/Send";

	private $properties = array(
				"username" => "",
				"password" => "",
				"sender" => "ON IT AS",
				"content" => "",
				"destination" => "",
				"priceGroup" => 0,
				"contentTypeID" => 1,
				"contentHeader" => "",
				"dlrUrl" => "",
				"ageLimit" => 0,
				"extID" => "",
				"sendDate" => "",
				"refID" => "",
				"priority" => 0,
				"gwid" => 0,
				"pid" => 0,
				"dcs" => 0
				);

	function __construct($un, $pwd, $sender = ""){
		$this->properties["username"] = $un;
		$this->properties["password"] = $pwd;
		if(strlen($sender) > 0){
			$this->properties["sender"] = $sender;
		}
	}

	private function _send($content){
		$url = $this->base . "?" . http_build_query($content);
		$status = file_get_contents($url);
		print $status;
	}

	function SMS($recipient, $message, $sender = ""){
		$sms = $this->properties;
		$sms["destination"] = $recipient;
		$sms["content"] = $message;
		if(strlen($sender)){
			$sms["sender"] = $sender;
		}
		$this->_send($sms);
	}
}
?>
