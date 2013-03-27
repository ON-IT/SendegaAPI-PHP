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
			$this->properties["sender"] = $sender;
		}
	}

	private function handleResponse($response)
	{
		$status = simplexml_load_string($response);
		print $status->asXML();
		if($status->SendResult->Success == 'True')
			return $status->SendResult->MessageID;
		print "a" . $status->SendResult;
		print "b" . $status->SendResult->ErrorNumber;
//		throw new SendegaException($status->SendResult);
	}

	private function call($content)
	{
		$url = $this->base . "?" . http_build_query($content);
		$status = file_get_contents($url);
		return $this->handleResponse($status);
	}

	function SMS($recipient, $message, $sender = "")
	{
		$sms = $this->properties;
		$sms["destination"] = $recipient;
		$sms["content"] = utf8_decode($message);
		if(strlen($sender) > 0){
			$sms["sender"] = $sender;
		}
		$this->call($sms);
	}

	function MMS($recipient, $message, $attachment, $sender = "")
	{
		$mms = $this->properties;
	}
}

/* Exceptions */

interface IException
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace
   
    /* Overrideable methods inherited from Exception class */
    public function __toString();                 // formated string for display
    public function __construct($message = null, $code = 0);
}

abstract class CustomException extends Exception implements IException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }
   
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}

class ArgumentMissingException extends Exception 
{
}
class SendegaException extends Exception
{
	function __construct($status = null, $code = 0)
	{
		$this->message = "test" . $status->ErrorMessage;
	}
}

$sendega = new Sendega("1235", "57892");
try {
$sendega->SMS("99508374", "hellooo");
} catch (SendegaException $e){
	print "Tryyyn";
	print $e;
}
?>
