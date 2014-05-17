<?php

define('BERG_PROJECT_ID',''); // bergcloud bubblechirp project ID
define('BERG_API_TOKEN','');
define('BERG_DEVICE_ID','');

define('TWITTER_API_KEY',''); // your twitter API key
define('TWITTER_API_SECRET',''); // your twitter api SECRET
define('SESSION_SECRET','bubblechirp'); 

define('DATABASE_HOST','localhost'); 
define('DATABASE_NAME','bubbletweet'); 
define('DATABASE_USERNAME',''); 
define('DATABASE_PASSWORD', ''); 

ORM::configure('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME);
ORM::configure('username', DATABASE_USERNAME);
ORM::configure('password', DATABASE_PASSWORD);
ORM::configure('error_mode', PDO::ERRMODE_WARNING);
