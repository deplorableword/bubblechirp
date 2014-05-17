#include <EEPROM.h>
#include <WebSocketClient.h>
#include <aJSON.h>
#include <Base64.h>
#include <SPI.h>
#include <Adafruit_CC3000.h>
#include <BergCC3000.h>

//
// WiFi network configuration
//

int outputPin = 47; 
int pinState = HIGH; 
long previousMillis = 0;
long interval = 10000;

#define WLAN_SSID                     ""
#define WLAN_PASS                     ""

#define WLAN_SEC                      WLAN_SEC_WPA2
#define WLAN_RECONNECT                false
#define WLAN_USE_SMARTCONFIG          false
#define WLAN_SMARTCONFIG_DEVICE_NAME  "DeviceName"
#define WLAN_SMARTCONFIG_PASSWORD     "DevicePassword"

// These values should be edited to reflect your Project setup on http://bergcloud.com/devcenter/
#define PROJECT_KEY ""
#define VERSION     1

unsigned long pollTimer;
int pollGap = 2000;
unsigned long connectionTimeMS;
String deviceAddress;
boolean receivedCommand;

void setup()
{
  
  pinMode(outputPin, OUTPUT);  
  digitalWrite(outputPin, pinState);  
  
  Serial.begin(115200);
  Serial.println("--- reset ---");

  BergWLANConfig WLANConfig;
  WLANConfig.ssid = WLAN_SSID;
  WLANConfig.pass = WLAN_PASS;
  WLANConfig.secmode = WLAN_SEC;
  WLANConfig.reconnect = WLAN_RECONNECT;
  WLANConfig.smartConfig = WLAN_USE_SMARTCONFIG;
  WLANConfig.smartConfigDeviceName = WLAN_SMARTCONFIG_DEVICE_NAME;
  WLANConfig.smartConfigPassword = WLAN_SMARTCONFIG_PASSWORD;
  Berg.begin(WLANConfig);

  // To reset the claimcode of the device make a 
  // connection between pin 50 and GND during powerup
  checkForReclaimPin(50);

  Serial.print("Uses WLAN_SSID ");
  Serial.println(WLAN_SSID);
  Serial.print("Command/Event poll = ");
  Serial.print(pollGap);
  Serial.println("ms");

  connectionTimeMS = millis();
  if (Berg.connect(PROJECT_KEY, VERSION))
  {
    Serial.print("Connecting to ");
    Serial.print(WLAN_SSID);
    Serial.print(" took ");
    Serial.print((millis()-connectionTimeMS)/1000);
    Serial.println(" seconds");
    connectionTimeMS = millis();
    Serial.println("Connecting to Berg...");
  }
  else{
    Serial.println("connect() returned false.");
  }

}

void loop()
{
  if(!is_connected()){
    // if not connected to Berg, check the claim state for this device
    if (!is_claimed()){
      // if not claimed print the claim code to the Serial monitor
      Serial.println("Claiming state: Not claimed");
      String claimcode;
      if (Berg.getClaimcode(claimcode))
      {
        Serial.println(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
        Serial.println("To complete connection visit http://bergcloud.com/devcenter/projects/");
        Serial.println("and claim your device under 'List and claim devices' in your project");
        Serial.print("using this claim code: ");
        Serial.println(claimcode);
        Serial.println("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
      }
      else{
        Serial.println("getClaimcode() returned false.");
      }
    }

    Serial.println("Checking connection again in 5 seconds");
    delay(5000);
    connectionTimeMS = millis();
  }
  else{
    // check the millis() against our pollGap
    if(millis()>=(pollTimer+pollGap)){

      receivedCommand = false;

      pollTimer=millis();
      unsigned long currentMillis = millis();      

      String address;

      BergMessage command, event;
      String text;
      int number;
      String commandName;

      ////////////////////////////////
      /// CHECKING FOR A COMMAND
      ////////////////////////////////
      
      if(currentMillis - previousMillis > interval) {
        // save the last time you blinked the LED 
        previousMillis = currentMillis;   
  
        pinState = HIGH;
        //set the LED with the pinState of the variable:
        digitalWrite(outputPin, pinState);
      }


      if (Berg.pollForCommand(command, commandName)){
        Serial.println("Recieved command:\t"+commandName);
  
        unsigned long currentMillis = millis();        
        
        receivedCommand = true;
        // Try to decode the two common types of serialized
        // data: An integer and a string
        // example payload [123, "Testing"]

        if (commandName == "bubble") {
            pinState = LOW;          
            digitalWrite(outputPin, pinState);            
        }
        

        ///////////////////////////////////////
        /// UNPACKING THE COMMAND PAYLOAD
        ///////////////////////////////////////
        if (command.unpack(number))
        {
          Serial.print("Containing number:\t");
          Serial.println(number);
        }
        else{
          Serial.println("unpack(int) returned false.");
        }

        Serial.print("Returning an event...\t");
        
        ////////////////////////////////
        /// SENDING AN EVENT
        ////////////////////////////////
        event.pack("Hello!");

        if (Berg.sendEvent("Echo", event)){
          Serial.println("ok");
        }
        else{
          Serial.println("failed/busy");
        }

      }

      ////////////////////////////////
      /// GENERAL STATUS REPORT
      ////////////////////////////////
      Berg.getDeviceAddress(deviceAddress);
      Serial.print("Device: ");
      Serial.print(deviceAddress);
      Serial.print(" Up time = ");
      Serial.print((millis()-connectionTimeMS)/1000);

      if(!receivedCommand){
        Serial.println(" seconds, no new commands");
      }
      else{
        Serial.println(" seconds");
      }
    }
  }


  Berg.loop();
}


////////////////////////////////////////////////
/// HELPER FUNCTION TO WRAP getConnectionState()
////////////////////////////////////////////////
boolean is_connected(){
  boolean connection = false;
  byte state;
  if(Berg.getConnectionState(state)){
    switch(state){
    case B_CONNECT_STATE_CONNECTED:
      //Serial.println("Connection state: Connected");
      connection = true;
      break;
    case B_CONNECT_STATE_CONNECTING:
      Serial.println("Connection state: Connecting...");
      break;
    case B_CONNECT_STATE_DISCONNECTED:
      Serial.println("Connection state: Disconnected");
      break;
    default:
      Serial.println("Connection state: Unknown!");
      break;
    }
  }
  else{
    Serial.print("getting connection state failed");
  }  
  return connection;
}

//////////////////////////////////////////////
/// HELPER FUNCTION TO WRAP getClaimingState()
//////////////////////////////////////////////
boolean is_claimed(){
  boolean claimed = false;
  byte state;
  // check the claiming state and use a switch to check the returned value.
  if (Berg.getClaimingState(state)){
    switch(state){
    case B_CLAIM_STATE_CLAIMED:
      //Serial.println("Claim State: Claimed");
      claimed = true;
      break;
    case B_CLAIM_STATE_NOT_CLAIMED:
      //Serial.println("Claim State: Not Claimed");
      break;
    default:
      Serial.println("Claim State: Unknown!");
      break;
    }
  }
  else{
    Serial.println("getClaimingState() returned false.");
  }
  return claimed;
}

////////////////////////////////////////////
/// HELPER FUNCTION TO WRAP resetClaimcode()
////////////////////////////////////////////
void checkForReclaimPin(int thePin){
  pinMode(thePin, INPUT_PULLUP);
  if(!digitalRead(thePin)){
    Serial.println("RESETTING CLAIMCODE");
    Berg.resetClaimcode();
  }
}





































