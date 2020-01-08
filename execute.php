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

		
// gestisco la richiesta
$response = "";

if(isset($message['text']))
{
  $arr = explode("http", $text, 2);
  $testoLink = $arr[0];
  
  $dominioAmazon = get_string_between($text, "://", ".to);
  $dominioGearbest = get_string_between($text, "://www.", ".com");
	
  //NUOVO PARSER:
  //$text_url_array = parse_text($text);
  
  $text_url_array = getUrls($text);
	
  //$array1 = explode('.', $text_url_array[1]);
  //$dominio = $array1[1];
  //test url $string_test = var_export($array1, true);
	
  if(strpos($text, "/start") === 0 )
  {
	$response = "Ciao $firstname! \nMandami un link Amazon o condividilo direttamente con me da altre app! \nTi rispondero' con il link affiliato del mio padrone! Grazie mille!\n\nCreated by http://www.webemento.com";
  }
  elseif($dominioAmazon == "amzn")
  {	  
	//new parser:
	$url_to_parse = $text_url_array[0];
	$url_affiliate = set_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$obj_desc = $testoLink;
	$short = make_bitly_url($url_affiliate,'ghir0','json');
	$response = "$obj_desc\n$worldsym $short";
	
  }
   elseif($dominioGearbest == "gearbest")
   {
	$url_to_parse = $text_url_array[0];
	$url_affiliate = set_referral_URL_GB($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$obj_desc = $testoLink;
	$short = make_bitly_url($url_affiliate,'ghir0','json');
	$response = "$obj_desc\n$worldsym  $short";
  
   }
   elseif(strpos($text, "/link") === 0 && strlen($text)<6 )
  {
	   //$response = "Incolla l'URL Amazon da convertire dopo il comando /link";
   }
  else {
	  //$response = "$string_test";
  }
}
/*
*
* prende un link amazon, estrapola l'ASIN e ricrea un link allo stesso prodotto con il referral 
*/
function set_referral_URL($url){
	$referral = "diev-21";
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	$seller = "&".$seller;
	$url_edited = "https://www.amazon.it".$parsed_url_array['path']."?tag=".$referral.$seller;
	return $url_edited;
}
/*
*
* crea il link con referral di gearbest 
*/
function set_referral_URL_GB($url){
	$referral = "10851947";
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	//$seller = "&".$seller;
	$url_edited = "http://www.gearbest.com".$parsed_url_array['path']."?lkid=".$referral.$seller;
	return $url_edited;
}

function getUrls($string) {
 $regex = '/https?\:\/\/[^\" ]+/i';
 preg_match_all($regex, $string, $matches);
 return ($matches[0]);
}

//nuovo parser
function parse_text($string){
	$string2 = str_replace("/link", "", $string);
	preg_match_all('%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i', $string2, $match);
	$text_parsed_URL = $match[0][0];
	$arr = explode("http", $string2);
	$text_parsed_TEXT = $arr[0];
	$text_parsed = array($text_parsed_TEXT, $text_parsed_URL);
	return $text_parsed;
}
 
function extract_unit($string, $start, $end){
	$pos = stripos($string, $start);
	$str = substr($string, $pos);
	$str_two = substr($str, strlen($start));
	$second_pos = stripos($str_two, $end);
	$str_three = substr($str_two, 0, $second_pos);
	$unit = trim($str_three); // remove whitespaces
	return $unit;
}
function strbefore($string, $substring) {
  $pos = strpos($string, $substring);
  if ($pos === false)
   return $string;
  else  
   return(substr($string, 0, $pos));
}
function strafter($string, $substring) {
  $pos = strpos($string, $substring);
  if ($pos === false)
   return $string;
  else  
   return(substr($string, $pos+strlen($substring)));
}
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function make_bitly_url($url,$login,$format = 'xml',$version = '2.0.1')
{
	//create the URL
	$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey=R_c7d78316d223d5a1d7827d58d80e76be'.'&format='.$format;
	
	//get the url
	//could also use cURL here
	$response = file_get_contents($bitly);
	
	//parse depending on desired format
	if(strtolower($format) == 'json')
	{
		$json = @json_decode($response,true);
		return $json['results'][$url]['shortUrl'];
	}
	else //xml
	{
		$xml = simplexml_load_string($response);
		return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
	}
}

header("Content-Type: application/json");
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
