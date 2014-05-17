<?php

//
// Add this line to your crontab file:
//
// * * * * * cd /path/to/project && php cron.php 1>> /dev/null 2>&1
//

require(__DIR__ . '/vendor/autoload.php');
require 'config.php';

// TODO: Convert to using Requests library
function search_twitter($bearer_token, $query, $since_id=false){
	$url = "https://api.twitter.com/1.1/search/tweets.json"; 
	$q = urlencode(trim($query)); 
	$formed_url ='?q='.$q; 
	if($since_id!=''){$formed_url = $formed_url.'&since_id='.$since_id;} // since_id offset
	$formed_url = $formed_url.'&result_type=recent&count=1';	
	$formed_url = $formed_url.'&include_entities=true'; included see documentation
	$headers = array( 
		"GET /1.1/search/tweets.json".$formed_url." HTTP/1.1", 
		"Host: api.twitter.com", 
		"Authorization: Bearer ".$bearer_token."",
	);
	$ch = curl_init();
	echo $url.$formed_url;
	curl_setopt($ch, CURLOPT_URL,$url.$formed_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$retrievedhtml = curl_exec ($ch);
	curl_close($ch);
	return json_decode($retrievedhtml);
}

$users = ORM::for_table('users')->find_many();
foreach($users as $user) {
	if(($user->hashtag) && ($user->twitter_auth_token) && ($user->tracking == 1)) {
		
		if ($user->since_id) {
			$result = search_twitter($user->twitter_auth_token,$user->hashtag, $user->since_id);
		} else {
			$result = search_twitter($user->twitter_auth_token,$user->hashtag);
		}

		if (count($result->statuses)) {
			
			if ($result->statuses[0]->id_str !== $user->twitter_since_id) {
				
				$headers = array(
					'Berg-API-Token' => BERG_API_TOKEN,
					'Content-Type' => 'application/json',
					'Accept' => 'application/json'
				);
		
				$data = array(
					'device_id' => BERG_DEVICE_ID,
					"name" => 'bubble',
					"payload" => array(1)
				);
		
				// hack because v2 API does not like quotes	for payload	
				$json_encoded_data = '{ "device_id": "'.BERG_DEVICE_ID.'", "name": "bubble", "payload": [1,"cron bubbles"]}';
				//		echo $json_encoded_data;

				$url = 'http://api.bergcloud.com/api/v2/projects/'.BERG_PROJECT_ID.'/commands';
				$request = Requests::post($url, $headers, $json_encoded_data);	
			}
						
			$user->twitter_since_id = $result->statuses[0]->id_str;
			$user->save();

		} else {
			die('error getting tweets');
		}
	}
}