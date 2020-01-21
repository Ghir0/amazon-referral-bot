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

$proprietario = "";
// gestisco la richiesta
$response = "";
if(isset($message['text']))

{
  //NUOVO PARSER:
  $text_url_array = parse_text($text);

  if(strpos($text, "/start") === 0 )
  {
	$response = "Ciao $firstname! \nMandami un link Amazon preceduto da /link! \nTi rispondero' con un link affiliate!";
  }
  elseif(strpos($text, "/link") === 0 && strlen($text)>6 )
  {	  
	//new parser:
	$url_to_parse = $text_url_array[1];
	$url_affiliate = set_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$obj_desc = $text_url_array[0];
	$short = make_bitly_url($url_affiliate,'ghir0','json');
	$response = "Ecco il link di $proprietario: $obj_desc\n$worldsym  $short";
	
  }
  elseif(strpos($text, "/mike") === 0 && strlen($text)>6 )
  {	  
	//new parser:
	$url_to_parse = $text_url_array[1];
	$url_affiliate = set_mike_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$obj_desc = $text_url_array[0];
	$short = make_bitly_url($url_affiliate,'ghir0','json');
	$response = "Ecco il link di Mike: $obj_desc\n$worldsym  $short";
	
  }
  elseif(strpos($text, "/dc") === 0 && strlen($text)>6 )
  {	  
	//new parser:
	$url_to_parse = $text_url_array[1];
	$url_affiliate = set_dc_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$obj_desc = $text_url_array[0];
	$short = make_bitly_url($url_affiliate,'ghir0','json');
	$response = "Ecco il link di Diego: $obj_desc\n$worldsym  $short";
	
  }
   elseif(strpos($text, "/link") === 0 && strlen($text)<6 )
  {
	   $response = "Incolla l'URL da convertire dopo il comando /link";
   }
}
/*
*
* prende un link amazon, estrapola l'ASIN e ricrea un link allo stesso prodotto con il referral 
*/
function set_referral_URL($url){
	$referral = array("miketama-21","s1m0nex27-21","antonio99-21","antcaiazza-21");
	$random = mt_rand(0,3);
	$GLOBALS['proprietario'] = $referral[$random];
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	$seller = "&".$seller;
	$url_edited = "https://www.amazon.it".$parsed_url_array['path']."?tag=".$referral[$random].$seller;
	return $url_edited;
}
function set_mike_referral_URL($url){
	$referral_mike = "miketama-21";
	
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	$seller = "&".$seller;
	$url_edited = "https://www.amazon.it".$parsed_url_array['path']."?tag=".$referral_mike.$seller;
	return $url_edited;
}
function set_dc_referral_URL($url){
	$referral_dc = "amazonscont0e-21";
	
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	$seller = "&".$seller;
	$url_edited = "https://www.amazon.it".$parsed_url_array['path']."?tag=".$referral_dc.$seller."keywords=I%20Consigli%20di%20Diegocampy.it";
	return $url_edited;
}

//nuovo parser
function parse_text($string){
	$string1 = str_replace("/dc", "", $string);
	$string2 = str_replace("/link", "", $string1);
	$string3 = str_replace("/mike", "", $string2);
	preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $string3, $match);
	$text_parsed_URL = $match[0][0];
	$arr = explode("http", $string3);
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

/*function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function clean_for_URL($string){
	$cleaned_string = explode(' ',strstr($string,'https://'))[0];
	if(strcmp($cleaned_string,"false") == "0"){ $cleaned_string = explode(' ',strstr($string,'http://'))[0]; }
	return $cleaned_string;
}
*/
header("Content-Type: application/json");
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
