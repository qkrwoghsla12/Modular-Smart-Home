#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <WebSocketsServer.h>
#include <Servo.h>
#include <Hash.h>
#include "DHT.h"
#ifdef ESP8266
  extern "C" {
    #include "user_interface.h"
  }
#endif
#define GPIO 2
#define SEN_SEL 15
#define DIP1 5
#define DIP2 13
#define DIP3 12
#define DIP4 14
#define DIP5 4
#define SLEEP 24*60*60*1000000    // 24시간
#define SEND_TIME 10*60*1000000 * 6   // 60분
#define SOCKET_CONNECT 1
#define SOCKET_DISCONNECT 0
#define SOCKET_PORT 11111
#define ON 40   // 버튼 모듈 ON
#define OFF 150 // 버튼 모듈 OFF

const char* ssid = "ollehEgg_084";
const char* password = "aleldj315";
//const char* ssid = "FET";
//const char* password = "haha6009";
const char* host_ip = "192.168.0.83";
int sen_num = 0;
int sen_mode = 0;
int global = SOCKET_DISCONNECT;
uint8_t channel;

void control();
void mode_setup();
void pir();
void dust();
void tem();
void push();
void electronic();
void relaycontrol();
void fire();
void push_web_socket(uint8_t num, WStype_t type, uint8_t* payload, size_t lenght);
void webSocketEvent(uint8_t num, WStype_t type, uint8_t * payload, size_t lenght);

ESP8266WebServer server(80);
WebSocketsServer webSocket = WebSocketsServer(SOCKET_PORT);
Servo servo;
WiFiClient client;
IPAddress ip;
IPAddress subnet(255,255,255,0);
IPAddress gateway(192,168,0,1);

void setup() {
  mode_setup();
  Serial.begin(9600);
  //Serial.print("sen num : ");
  //Serial.println(sen_num);
  //Serial.print("mode : ");
  //Serial.println(sen_mode);
  
  delay(100);
  if(sen_mode == 2){  // 버튼
    switch(sen_num){
      case 1:
        ip = IPAddress(192,168,0,90);
        break;
      case 2:
        ip = IPAddress(192,168,0,91);
        break;
      case 3:
        ip = IPAddress(192,168,0,92);
        break;
      case 4:
        ip = IPAddress(192,168,0,93);
        break;
    }  
    WiFi.config(ip,gateway,subnet);
  }
  if(sen_mode == 4){    // 전력
    switch(sen_num){
      case 1:
        ip = IPAddress(192,168,0,30);
        break;
      case 2:
        ip = IPAddress(192,168,0,31);
        break;
      case 3:
        ip = IPAddress(192,168,0,32);
        break;
      case 4:
        ip = IPAddress(192,168,0,33);
        break;
    }  
    WiFi.config(ip,gateway,subnet);
  }
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    //Serial.print(".");
  }
  Serial.println(WiFi.localIP());

  switch(sen_mode){
    case 0:
      pir();
      break;
    case 1:
      dust();
      break;
    case 2:
      push();
      break;
    case 3:
      tem();
      break;
    case 4:
      electronic();
      break;
    case 5:
      fire();
      break;
  }
}

void loop() {
}

void mode_setup(){
  pinMode(SEN_SEL,OUTPUT);
  digitalWrite(SEN_SEL,HIGH);
  pinMode(DIP1,INPUT);
  pinMode(DIP2,INPUT);
  pinMode(DIP3,INPUT);
  pinMode(DIP4,INPUT);
  pinMode(DIP5,INPUT);
  digitalWrite(SEN_SEL,HIGH);
  sen_num = digitalRead(DIP1) + digitalRead(DIP2)*2 + 1;
  sen_mode = digitalRead(DIP3)+ digitalRead(DIP4)*2 + digitalRead(DIP5)*4;
  digitalWrite(SEN_SEL,LOW);
  pinMode(SEN_SEL,INPUT);
  delay(500);
}

void pir(){
  client.connect(host_ip, 80);
  client.println("GET /esp2db.php?num="+(String)sen_num+"&pir=1 HTTP/1.1");
  client.println("Host: "+(String)host_ip);
  client.println("Connection: close\r\n");
  client.flush();
  client.stop();
  ESP.deepSleep(0);     // infinity
}

void dust(){
  int samplingTime = 280;
  int deltaTime = 40;
  int sleepTime = 9680;
  
  float voMeasured = 0;
  float dustDensity[5]={'\0'};
  float result = 0;
  
  while(1){
    client.connect(host_ip, 80);
    pinMode(GPIO,OUTPUT);
    for(int i=0;i<5;i++){
      do{
        digitalWrite(GPIO,LOW);
        delayMicroseconds(samplingTime);
        voMeasured = analogRead(A0);
        delayMicroseconds(deltaTime);
        digitalWrite(GPIO,HIGH);
        delayMicroseconds(sleepTime);
        
        dustDensity[i] = (voMeasured * 3.3 / 1024.0 - 0.6) / 6.0;
        delay(1000);
      }while(dustDensity[i]<=0.009);
    }
    result = (dustDensity[0]+dustDensity[1]+dustDensity[2]+dustDensity[3]+dustDensity[4])/5;
    client.println("GET /esp2db.php?num="+(String)sen_num+"&dust="+(String)result+" HTTP/1.1");
    client.println("Host: "+(String)host_ip);
    client.println("Connection: close\r\n");
    client.flush();
    client.stop();
    ESP.deepSleep(SEND_TIME);
  }
}

void tem(){
  int h,t;
  DHT dht(GPIO, DHT11);
  
  while(1){
    client.connect(host_ip, 80);
    do{
      h = dht.readHumidity();
      t = dht.readTemperature();
    }while(h>100 || t>100);
    client.println("GET /esp2db.php?num="+(String)sen_num+"&tem="+(String)t+"&hum="+(String)h+" HTTP/1.1");
    client.println("Host: "+(String)host_ip);
    client.println("Connection: close\r\n");
    client.flush();             
    client.stop();
    ESP.deepSleep(SEND_TIME);
  }
}

void push(){
  wifi_set_sleep_type(NONE_SLEEP_T);
  webSocket.begin();
  webSocket.onEvent(push_web_socket);
  servo.attach(GPIO);
  
  servo.write(130);
  delay(1000);
  pinMode(GPIO,INPUT);

  while(1){
    webSocket.loop();
    delay(100);
  }
}

void electronic(){
  int readValue;             
  int maxValue = 0; 
  float irms, vpp, prms, isum;
  int count=0;
  char buff[20]={'\0',};
  unsigned long long start_time, send_time =0;
  wifi_set_sleep_type(NONE_SLEEP_T);
  
  pinMode(GPIO,OUTPUT);
  pinMode(A0,INPUT);
  digitalWrite(GPIO,HIGH);

  webSocket.begin();
  webSocket.onEvent(webSocketEvent);
  send_time = millis();
  isum = 0;
  while(1){
    maxValue = 0;
    webSocket.loop();
    start_time = millis();
    
    while((millis()-start_time) < 1000){
      readValue = analogRead(A0);
      if (readValue > maxValue){
        maxValue = readValue;
      }
      webSocket.loop();
      delay(1);
    }
    vpp = (maxValue * 3.3)/1024.0;
    irms = (vpp/200.0) * 707;   // 전류 200은 센서에 달려있는 저항이 200옴 i=v/r
    isum += irms;
    count++;
    //prms = prms + irms * 220;
    
    if(global == SOCKET_CONNECT){
      dtostrf(irms,10,3,buff);
      //webSocket.sendTXT(channel,buff);
      webSocket.broadcastTXT(buff);
    }
    if((millis()-send_time) >= 600000){         // 10분간격으로 서버로 사용량 전송
      irms = isum / count;
      client.connect(host_ip, 80);
      client.println("GET /esp2db.php?num="+(String)sen_num+"&electronic="+(String)irms+" HTTP/1.1");
      client.println("Host: "+(String)host_ip);
      client.println("Connection: close\r\n");
      client.flush();
      client.stop();
      send_time = millis();
      irms = isum = count = 0;
    }
    client.connect(host_ip, 80);
    client.println("GET /esp2db.php HTTP/1.1");         // 소켓연결이 끊기지않도록 더미 요청을 계속함
    client.println("Host: "+(String)host_ip);
    client.println("Connection: close\r\n");
    client.flush();
    client.stop();
  }
}

void fire(){
  int buf = 1024;
  wifi_set_sleep_type(MODEM_SLEEP_T);   // wifi 모뎀 슬립
  //wifi_set_sleep_type(LIGHT_SLEEP_T);
  //WiFi.mode(WIFI_STA);             // wifi ap 가 아니라 단말로 사용, sta = station

  while(1){
    int x = analogRead(A0);
    if((x - buf > 130) || (x > 800)){
      client.connect(host_ip, 80);
      client.println("GET /esp2db.php?num="+(String)sen_num+"&fire=1 HTTP/1.1");
      //client.println("GET /esp2db.php?num="+(String)sen_num+"&sonic="+(String)x+" HTTP/1.1");
      client.println("Host: "+(String)host_ip);
      client.println("Connection: close\r\n");
      client.flush();
      client.stop(); 
    }
    buf = x;
    delay(5000);
  }
  
}

void push_web_socket(uint8_t num, WStype_t type, uint8_t* payload, size_t lenght) {
   switch(type) {
    case WStype_DISCONNECTED:
      channel = NULL;
      global = SOCKET_DISCONNECT;
      break;
      
    case WStype_CONNECTED:
      channel = num;
      global = SOCKET_CONNECT;
      break;
    
    case WStype_TEXT:
      pinMode(GPIO,OUTPUT);
      if(!strcmp((char*)payload,"on")){
        servo.write(ON);
        delay(500);
      }
      else if(!strcmp((char*)payload,"off")){
        servo.write(OFF);
        delay(500);
      }
      else if(!strcmp((char*)payload,"toggle")){
        servo.write(ON);
        delay(500);
        servo.write(OFF);
        delay(1000);
        servo.write(ON);
      }
      pinMode(GPIO,INPUT);
      //webSocket.sendTXT(channel,payload);
      break;
  }
}

void webSocketEvent(uint8_t num, WStype_t type, uint8_t* payload, size_t lenght) {     // num 이 채널
  switch(type) {
    case WStype_DISCONNECTED:
      channel = NULL;
      global = SOCKET_DISCONNECT;
      break;
      
    case WStype_CONNECTED:
      channel = num;
      global = SOCKET_CONNECT;
      break;
    
    case WStype_TEXT:
      if(!strcmp((char*)payload,"on")){
        digitalWrite(GPIO,HIGH);
      }
      else{
        digitalWrite(GPIO,LOW);
      }
      //webSocket.sendTXT(channel,payload);
      break;
  }
}

