<?php
require '../vendor/autoload.php';
require '../config.php';
require '../common.php';

$app->get('/', function () use ($app) {
	if (!isset($_SESSION['user'])) {	
		$app->render('index.php');	
	} else {
		$app->redirect('/dashboard');			
	}
});

// login form
$app->get('/login/', function () use ($app) {
	$app->render('login.php');
});

// login auth
$app->post('/login/', function () use ($app) {
	
	if ($app->request()->post('email') 
	&& $app->request()->post('password') 
	) {
	
		$user = ORM::for_table('users')->where('email', $app->request()->post('email'))->find_one();
		$password = password_verify($app->request()->post('password'),$user->password);
	
		$errors = Array();
	
		if ($user == false) {
			$errors['email'] = "Check your email address";
		}	
	
		if ($password == false) {
			$errors['email'] = "Check your password.";
		}	
	
		if (count($errors) > 0) {
			$app->flash('errors', $errors);
			$app->redirect('/login');
		} else {
			$_SESSION['user'] = $app->request()->post('email'); 
			if (isset($_SESSION['urlRedirect'])) {
				$tmp = $_SESSION['urlRedirect'];
				unset($_SESSION['urlRedirect']);
				$app->redirect($tmp);
			}
		$app->redirect('/dashboard');
		}
	} else {
		$app->redirect('/dashboard');	
	}
});


$app->get('/logout/', function () use ($app) {	
	if ($_SESSION) {
		unset($_SESSION['user']);		
	}
	$app->view()->setData('user', null);
	$app->redirect('/');
});



$app->get('/setup/', function () use ($app) {
	// push code onto your ardunio + sheild	
	// show form
	$app->render('setup.php');
});


// register
$app->post('/setup/', function () use ($app) {
			
	if ($app->request()->post('email') 
	&& $app->request()->post('password') 
	&& $app->request()->post('device-id')
	) {
		$user = ORM::for_table('users')->create();
		
		$user->email = $app->request()->post('email');
		$user->password = password_hash($app->request()->post('password'), PASSWORD_BCRYPT);
		$user->device_id = $app->request()->post('device-id');
		$user->save();
		
		$_SESSION['user'] = $user->email;
		
		$app->redirect('/dashboard');
		
	} else {
		$app->redirect('/setup');
	}	
});

/* following routes are protected by login */

$app->get('/dashboard', $isloggedin($app), function () use ($app) {
		
	// get status of device
	$options = Array(
		'twitter_account' => 'deplorableword',
		'tracking' => $app->active_user->tracking,
		'hashtag' => $app->active_user->hashtag,
		'device_id' => $app->active_user->device_id,
	);
	$app->render('dashboard.php', $options);
});


$app->get('/webhook', function () use ($app) {
		
});

// API protected by auth
$app->group('/api', $isloggedin($app), function () use ($app) {
	
	$app->post('/hashtag/update/', function () use ($app){
		$app->active_user->hashtag = $app->request->post('hashtag');
		$app->active_user->save();
		$app->redirect('/dashboard');	
	});	
	
	$app->post('/test/', function () use ($app) {
		// send a test burst 
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
		$json_encoded_data = '{ "device_id": "'.BERG_DEVICE_ID.'", "name": "bubble", "payload": [1,"test bubbles"]}';
		//		echo $json_encoded_data;

		$url = 'http://api.bergcloud.com/api/v2/projects/'.BERG_PROJECT_ID.'/commands';
		$request = Requests::post($url, $headers, $json_encoded_data);
		$app->redirect('/dashboard');					
	});

	// get oauth bearer token
	$app->get('/twitter/auth/', function () use ($app) {	
		
		// https://github.com/jonhurlock/Twitter-Application-Only-Authentication-OAuth-PHP/blob/master/Oauth.php
		$encoded_consumer_key = urlencode(TWITTER_API_KEY);
		$encoded_consumer_secret = urlencode(TWITTER_API_SECRET);
		$bearer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
		$base64_encoded_bearer_token = base64_encode($bearer_token);
	
		// step 2
		$url = "https://api.twitter.com/oauth2/token"; // url to send data to for authentication
		$headers = array( 
			"POST /oauth2/token HTTP/1.1", 
			"Host: api.twitter.com", 
			"Authorization: Basic ".$base64_encoded_bearer_token."",
			"Content-Type: application/x-www-form-urlencoded;charset=UTF-8", 
			"Content-Length: 29"
		); 

		$ch = curl_init();  // setup a curl
		curl_setopt($ch, CURLOPT_URL,$url);  // set url to send to
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set custom headers
		curl_setopt($ch, CURLOPT_POST, 1); // send as post
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials"); // post body/fields to be sent
		$header = curl_setopt($ch, CURLOPT_HEADER, 1); // send custom headers
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$retrievedhtml = curl_exec ($ch); // execute the curl
		curl_close($ch); // close the curl
		$output = explode("\n", $retrievedhtml);
		$bearer_token = '';
		foreach($output as $line)
		{
			if($line === false)
			{
				// there was no bearer token
			}else{
				$bearer_token = $line;
			}
		}
		$bearer_token = json_decode($bearer_token);

		$app->active_user->twitter_auth_token = $bearer_token->{'access_token'};
		$app->active_user->tracking = 1;
		$app->active_user->save();

		$app->redirect('/dashboard');	
	});
	
	// forget token
	$app->get('/twitter/disconnect/', function () use ($app) {
		
		$bearer_token = $app->active_user->twitter_auth_token;
		
		$encoded_consumer_key = urlencode(TWITTER_API_KEY);
		$encoded_consumer_secret = urlencode(TWITTER_API_SECRET);
		$consumer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
		$base64_encoded_consumer_token = base64_encode($consumer_token);
		// step 2
		$url = "https://api.twitter.com/oauth2/invalidate_token"; // url to send data to for authentication
		$headers = array( 
			"POST /oauth2/invalidate_token HTTP/1.1", 
			"Host: api.twitter.com", 
			"Authorization: Basic ".$base64_encoded_consumer_token."",
			"Accept: */*", 
			"Content-Type: application/x-www-form-urlencoded", 
			"Content-Length: ".(strlen($bearer_token)+13).""
		); 
    
		$ch = curl_init();  // setup a curl
		curl_setopt($ch, CURLOPT_URL,$url);  // set url to send to
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set custom headers
		curl_setopt($ch, CURLOPT_POST, 1); // send as post
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output
		curl_setopt($ch, CURLOPT_POSTFIELDS, "access_token=".$bearer_token.""); // post body/fields to be sent
		$header = curl_setopt($ch, CURLOPT_HEADER, 1); // send custom headers
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$retrievedhtml = curl_exec ($ch); // execute the curl
		curl_close($ch); // close the curl
		
		$app->active_user->tracking = 0;
		$app->active_user->save();
		
		$app->redirect('/dashboard');	
	});
		
});



$app->run();// 