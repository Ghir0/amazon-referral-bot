<?php
// recupero il contenuto inviato da Telegram
$content = file_get_contents("php://input");
// converto il contenuto da JSON ad array PHP
$update = json_decode($content, true);
// se la richiesta è null interrompo lo script
if(!$update)
{
  exit;
}
// assegno alle seguenti variabili il contenuto ricevuto da Telegram
$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";
// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
//$text = strtolower($text);
$array1 = array();
$key = "";
$ASIN = "";
		
// gestisco la richiesta
$response = "";
if(isset($message['text']))

{
  $text_clean = clean_for_URL($text);
  $array1 = explode('.', $text_clean);
  $dominio = $array1[1];
  if(strpos($text, "/start") === 0 )
  {
	$response = "Hi $firstname! Send me an Amazon link";
  }
  elseif(strcmp($dominio,"amazon") === 0)
  {
	//$response = "Good! This is an ".$dominio." link!!";
	$url_to_parse = $text_clean;
	$url_affiliate = set_referral_URL($url_to_parse);
	$response = $url_affiliate;
  }
  elseif(strcmp($array1[0],"www") === 0)
  {
	//$response = "Wrong! This is not an Amazon link, retry!";
  }
  else
  {
	//$response = "This doesn't work, send me an Amazon link";
	  //$response = "http://amzn.to/2f8aTvW";
  }
}
/*
*
* prende un link amazon, estrapola l'ASIN e ricrea un link allo stesso prodotto con il referral 
*/
function set_referral_URL($url){
	$referral = "miketama-21";
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	$path = explode('/', $parsed_url_array['path']);
	$key = array_search('dp', $path);
	if($key==''){$key = array_search('d', $path);}
	$ASIN = $path[$key+1];
	if (strcmp($ASIN,"")=== 0)
	{
		$url_edited="http://amzn.to/2f8aTvW"
	}
	else
	{
	$url_edited = "https://www.amazon.it/dp/".$ASIN."?tag=".$referral;
	}
	return $url_edited;
}

function clean_for_URL($string){
	$cleaned_string = "";
	$cleaned_string = explode(' ',strstr($string,'https://'))[0];
	if($cleaned_string == ""){ explode(' ',strstr($string,'http://'))[0]; }
	return $cleaned_string;
}
	
header("Content-Type: application/json");
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
