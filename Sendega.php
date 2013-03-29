<?php
interface iLogger
{
	public function Log(array $message);
}

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

	public $Fake = false;
	private $Logger = null;

	function __construct($un, $pwd, $sender)
	{
		if(strlen($un) == 0 || strlen($pwd) == 0)
		{
			throw new ArgumentMissingException("Username or password is missing");
		}
		$this->properties["username"] = $un;
		$this->properties["password"] = $pwd;
		if(strlen($sender) > 0){
			$this->properties["sender"] = $sender;
		}
	}

	private function handleResponse($response, $content)
	{
		$status = simplexml_load_string($response);
		if($status->Success == 'true')
		{
			$content["messageID"] = $status->MessageID;
			$content["status"] = true;
			$this->Log($content);
			return $status->MessageID;
		}

		$content["status"] = false;
		$this->Log($content);
		throw new SendegaException($status);
	}

	private function call($content)
	{
		$url = $this->base . "?" . http_build_query($content);
		$status = file_get_contents($url);
		return $this->handleResponse($status, $content);
	}

	private function Log(array $msg)
	{
		if($this->Logger != null && $this->Logger instanceof iLogger)
		{
			unset($msg["username"]);
			unset($msg["password"]);
			$this->Logger->Log($msg);
		}
	}

	function SetLogger($L)
	{
		$this->Logger = $L;
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
			$mms["content"] = $encoded;
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

	function Lookup($number, $lang = "no")
	{
		$base = "http://smsc.sendega.com/ExtraServices/NumberEnquiry.asmx/GetSubscriberInformation";
		$params = array(
				"username" => $this->properties["username"],
				"password" => $this->properties["password"],
				"msisdn" => $number,
				"outputLanguage" => $lang
				);
		$response = file_get_contents($base . "?" . http_build_query($params));
		$xml = simplexml_load_string($response);
		return new Info($xml, $number);
	}
}

class Info {
	public $Name = "";
	public $LastName = "";
	public $Address1 = "";
	public $Address2 = "";
	public $Zip = "";
	public $City = "";
	public $CompanyNo = "";
	public $Lat = 0;
	public $Long = 0;
	public $Birthdate = null;
	public $Number = "";

	function __construct($xml, $number)
	{
		if($xml->Success == "false")
			throw new SendegaException("No hit for " . $number, 9999);

		$this->Number = $number;
		if($xml->Persons->SubscriberInformationPerson->count() > 0)
		{
			$this->map($xml->Persons->SubscriberInformationPerson);
		} 
		elseif($xml->Companies->SubscriberInformationCompany->count() > 0)
		{
			$this->map($xml->Companies->SubscriberInformationCompany);
		}
	}

	private function map($xml)
	{
		if(strlen($xml->Name) > 0)
			$this->Name = (string)$xml->Name;
		else
			$this->Name = (string)$xml->FirstName;
		$this->LastName = (string)$xml->LastName;
		$this->CompanyNo = (string)$xml->OrganizationNumber;
		$this->Birthdate = (string)$xml->Birthdate;
		$this->Address1 = (string)$xml->Addresses->Address[0]->Address1;
		$this->Address2 = (string)$xml->Addresses->Address[0]->Address2;
		$this->Zip = (string)$xml->Addresses->Address[0]->Zip;
		$this->City = (string)$xml->Addresses->Address[0]->City;
		$this->Lat = (string)$xml->Addresses->Address[0]->Latitude;
		$this->Long = (string)$xml->Addresses->Address[0]->Longitude;
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
