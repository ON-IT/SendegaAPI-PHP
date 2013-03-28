<?php
class Sendega 
{

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

	function __construct($un, $pwd, $sender = "")
	{
		if(strlen($un) == 0 || strlen($pwd) == 0)
		{
			throw new ArgumentMissingException("Username or password is missing");
		}
		$this->properties["username"] = $un;
		$this->properties["password"] = $pwd;
		if(strlen($sender) > 0){
			$this->properties["sender"] = urlencode($sender);
		}
	}

	private function handleResponse($response)
	{
		$status = simplexml_load_string($response);
		if($status->Success == 'true')
			return $status->MessageID;
		throw new SendegaException($status);
	}

	private function call($content)
	{
		$url = $this->base . "?" . http_build_query($content);
		$status = file_get_contents($url);
		return $this->handleResponse($status);
	}

	function SendDeliveryReport($url)
	{
		$this->properties["dlrUrl"] = $url;
	}

	function SMS($recipient, $message, $sender = "")
	{
		$sms = $this->properties;
		$sms["destination"] = $recipient;
		$sms["content"] = utf8_decode($message);
		if(strlen($sender) > 0){
			$sms["sender"] = $sender;
		}
		return $this->call($sms);
	}

	function MMS($recipient, $message, $attachment = "", $sender = "")
	{
		$mms = $this->properties;
		$mms["destination"] = $recipient;
		$mms["contentTypeID"] = 3;
		$mms["ContentHeader"] = $message;

		if(strlen($attachment) > 0)
		{
			$file = tempnam("tmp", "zip");
			$zip = new ZipArchive();
			$zip->open($file, ZipArchive::OVERWRITE);
			$zip->addFile($attachment);
			$zip->close();
			$encoded = base64_encode(file_get_contents($file));
			unlink($file);
			$mms["content"] = base64_encode($encoded);
		}
		
		if(strlen($sender) > 0)
			$mms["sender"] = $sender;

		if(!is_numeric($mms["sender"]))
			throw new SendegaException("Alphanumeric sender is not valid for MMS", 1028);

		return $this->call($mms);
	}

	function Bookmark($recipient, $description, $url, $sender = "")
	{
		$bm = $this->properties;
		$bm["destination"] = $recipient;
		$bm["contentTypeID"] = 0;
		$bm["contentHeader"] = $url;
		$bm["content"] = $description;
		
		if(strlen($sender) > 0)
			$bm["sender"] = $sender;
		
		return $this->call($bm);
	}
}

/* Exceptions */

class ArgumentMissingException extends Exception 
{
}

class SendegaException extends Exception
{
	function __construct($status = null, $code = 0)
	{
		if($code == 0)
		{
			$this->message = $status->ErrorMessage;
			$this->code = $status->ErrorNumber;
		} else {
			$this->message = $status;
			$this->code = $code;
		}
	}
}
?>
