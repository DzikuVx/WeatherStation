#include <wiringPi.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#define MAX_TIME 85
#define DHT11PIN 7
int dht11_val[5]={0,0,0,0,0};

void dht11_read_val() {
	uint8_t lststate=HIGH;
	uint8_t counter=0;
	uint8_t j=0,i;
	
	for(i=0;i<5;i++)
		dht11_val[i]=0;

	pinMode(DHT11PIN,OUTPUT);
	digitalWrite(DHT11PIN,LOW);
	delay(18);
	digitalWrite(DHT11PIN,HIGH);
	delayMicroseconds(40);
	pinMode(DHT11PIN,INPUT);

	for(i=0;i<MAX_TIME;i++) {
		counter=0;

		while(digitalRead(DHT11PIN)==lststate) {
			counter++;
			delayMicroseconds(1);
			if(counter==255) {
				break;
			}
		}

		lststate=digitalRead(DHT11PIN);

		if(counter==255)
			break;

		if((i>=4)&&(i%2==0)){

			dht11_val[j/8]<<=1;

			if(counter>16)
				dht11_val[j/8]|=1;
			j++;
		}
	}

	// verify cheksum and print the verified data

	if((j>=40)&&(dht11_val[4]==((dht11_val[0]+dht11_val[1]+dht11_val[2]+dht11_val[3])& 0xFF))) {
		printf("%d.%d|%d.%d",dht11_val[0],dht11_val[1],dht11_val[2],dht11_val[3]);
	}
	else {
		printf("|");
	}
}

int main(void) {
	
	if(wiringPiSetup()==-1) {
		printf("|");
		exit(1);
	}

	// while(1) {
	dht11_read_val();
		// delay(5000);
	// }

	return 0;
}