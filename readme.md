# Bubblechirp

A WiFi bubble machine powered by Tweets. Uses the [BERG Cloud V2 API](bergcloud.com/devcenter/api/v2). You don't need any special BERG hardware for this, just a regular Arduino Mega and WiFi shield. This is a weekend hack, there will be bugs. Pull requests welcome.

# Hardware list

- Bubble Machine ([this one is easy to hack](http://www.amazon.co.uk/Billion-Bubbles-Kids-Bubble-Generator/dp/B000OOQ3MG&tag=thedeplorable-21))
- [Arduino Mega](http://shop.pimoroni.com/products/arduino-mega2560)
- [Adafruit CC3000 WiFi Sheild](http://shop.pimoroni.com/products/adafruit-cc3000-wifi-shield-with-onboard-ceramic-antenna)
- [Relay Module](http://www.amazon.co.uk/2-Channel-Module-Shield-Arduino-Electronic/dp/B009P04ZKC/ref=sr_1_1?ie=UTF8&qid=1400332193&sr=8-1&keywords=relay&tag=thedeplorable-21)
- A server or local computer which can run PHP / MySQL

## How it works

The device is dumb and just creates new bubbles. The server is smart and does the work of connecting to Twitter and finding new tweets.

The server runs a cron and finds new Tweets. This is posted via HTTP to BERG Cloud which deals with delivering the notification to the Arduino. When the Arduino receives a message it triggers a relay, which creates bubbles. 

##Setup

Create a [new project](http://bergcloud.com/devcenter/projects/new) in the BERG Cloud devcentre, set the API version to 2.

###Hardware

1. Break into the buble machine and bypass / remove the switch.
2. Run power and ground out of the case and into one of the relay terminals.
3. Assemble the Mega + WiFi shield and load up the sketch from the load up the sketch in the Arduino folder. Set the WLAN_SSID,WLAN_PASS to those for your WiFi network. Finally set the PROJECT_KEY to use the value you got from the devcentre.
4. While still connected to the computer, open up the Arduino console and copy the claim code.
5. In the [dev centre](http://bergcloud.com/devcenter/projects/) for your project, enter the claim code to claim the device to the project.
6. Finally, wire up the relay to Power, Ground and Pin 47. 


###Software

1. Create a new app on [Twitter](https://apps.twitter.com/)
2. Install the webapp. It's written using [Slim](http://docs.slimframework.com/).
3. Open up `config.php` and insert the following keys:
- From the BERG Devcentre `BERG_PROJECT_ID`, `BERG_API_TOKEN` and `BERG_DEVICE_ID`
- From the Twitter set `TWITTER_API_KEY` and `TWITTER_API_SECRET`
- From your computer, set the values for `DATABASE_HOST`, `DATABASE_NAME`, `DATABASE_USERNAME` and `DATABASE_PASSWORD`

3. Connect to MySQL database and run the following:

```
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(70) NOT NULL DEFAULT '',
  `device_id` varchar(255) DEFAULT '',
  `hashtag` varchar(50) DEFAULT NULL,
  `twitter_auth_token` varchar(255) DEFAULT NULL,
  `twitter_since_id` varchar(255) DEFAULT NULL,
  `tracking` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
```

4. Open up the webapp in a browser, create a new account with an email / password combo and device id. 
5. On the command line, edit your crontab.

`* * * * * php /path/to/bubblechirp/cron.php >>  /dev/null`

6. Back in the browser, hit Quickfire to test everything works, then you can start & stop tracking a particular hashtag using the interface. 

##Todo
- [ ] Support for smart wifi config, so username and password can be added via a smartphone,
- [ ] Handle multiple devices.
- [ ] Provide notification when the device goes offline.
- [ ] Support mentions on Twitter.
- [ ] Handle device claiming via claimcodes.
- [ ] Make all of the HTTP requests use the Request library vs random curl garbage. 

Pull requests welcome