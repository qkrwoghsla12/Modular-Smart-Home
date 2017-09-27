#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#ifdef ESP8266
extern "C" {
  #include "user_interface.h"
}
#endif

const char* ssid = "ollehEgg_084";
const char* password = "aleldj315";
const char* host_ip = "192.168.0.83";
int pot[4]={'\0'};
volatile int flag;

IPAddress subnet(255,255,255,0);
IPAddress gateway(192,168,0,1);
IPAddress my_ip =  IPAddress(192,168,0,80);

ESP8266WebServer server(80);
WiFiClient client;

void auto_on(){
  Serial.print('o');
  server.send(200, "text/plain", "auto_on");
}

void auto_off(){
  Serial.print('f');
  server.send(200, "text/plain", "auto_off");
}

void manual(){
  Serial.print('m');
  Serial.print(server.arg("pot"));
  server.send(200, "text/plain", "manual");
}

void humi(){
  Serial.print('h');
  server.send(200, "text/plain", "ok");
  for(char i='1';i<'5';i++){
    if(server.arg("pot"+(String)i).length()==2){
      Serial.print("0"+server.arg("pot"+(String)i));
    }
    else if(server.arg("pot"+(String)i).length()==1){
      Serial.print("00"+server.arg("pot"+(String)i));
    }
    else{
     Serial.print(server.arg("pot"+(String)i));
    }
  }
}

void setup() {
  wifi_set_sleep_type(NONE_SLEEP_T);
  Serial.begin(9600);
  
  WiFi.config(my_ip,gateway,subnet);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    //Serial.print(".");
  }

  /*
  Serial.print(my_ip);
  client.connect(host_ip, 80);
  client.print("GET /esp2db.php?avr_ip=");
  client.print(my_ip);
  client.println(" HTTP/1.1");
  client.println("Host: "+(String)host_ip);
  client.println("Connection: close\r\n");
  client.flush();
  delay(100);
  */
  server.on("/auto_on",auto_on);
  server.on("/auto_off",auto_off);
  server.on("/manual",manual);
  server.on("/humi",humi);
  server.begin();
}

void loop() {
  server.handleClient();
  delay(10);   // 빼도됨
  if(Serial.available()){
    delay(100);
    for(int i=0;i<4;i++){
      if(Serial.read() == (i+48)){
        pot[i] = ((int)Serial.read() - 33) * 10;
        pot[i] = pot[i] + (int)Serial.read() - 33;
        flag = 1;
      }
      else{
        pot[i] = 0;
        Serial.read();
        Serial.read();
        flag = 0;
      }
    }
    if(flag == 1){
      client.connect(host_ip, 80);
      client.println("GET /esp2db.php?pot1="+(String)pot[0]+"&pot2="+(String)pot[1]+"&pot3="+(String)pot[2]+"&pot4="+(String)pot[3]+" HTTP/1.1");
      client.println("Host: "+(String)host_ip);
      client.println("Connection: close\r\n");
      client.flush();
      client.stop();
    }
    else{
      Serial.print('r');
    }
  }
}



