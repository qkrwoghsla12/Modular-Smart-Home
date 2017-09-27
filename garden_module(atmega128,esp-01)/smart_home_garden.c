#include <avr/io.h>
#include <avr/interrupt.h>
#include <util/delay.h>

#define SAMPLING_TIME 60				// ���� 1�� 1��
#define OPEN 8
#define CLOSE 22

void uart_init(void);
void uart_send(unsigned char data);
unsigned char uart_rece(void);
void iic_init(void);
void iic_start(void);
void iic_write(unsigned char data);
char iic_read(void);
void iic_stop(void);
void iic_writebyte(unsigned char data,unsigned char add);
char iic_readbyte(char add);
int adc_conv(int num);
void uart_sends(char* x);
char* num_to_char(unsigned int num,int buf_num);
void servo_init(void);
void servo(int num,int deg);
void lcd_cmd(char data);
void lcd_init(void);
void lcd_data(char data);
void lcd_string(char *data,int num);
char* strcat4(char* str1,char* str2,char* str3,char* str4);

volatile char auto_switch = 'f';
volatile char buf[12]={'\0'};
volatile int set_humi[4] = {'\0'};
volatile int adc_flag = SAMPLING_TIME-1;
volatile int c_buf=0;
int i = 0;
char n2c[2][4] = {{0,0,0,0},{0,0,0,0}};		// 3���� + null ���� 
char strcat_buf[16] = {'\0'};
volatile char manual_pot[5]={'\0'};

ISR(USART0_RX_vect){
	switch(uart_rece()){
		case 'h':						// �ڵ� ���ֱ� ���� ���� ����
			for(i=0;i<12;i++){
				buf[i] = uart_rece();
			}
			for(i=0;i<12;i++){
				iic_writebyte(buf[i],i+10);
			}
			set_humi[0] = (buf[0]-48)*100 + (buf[1]-48)*10 + (buf[2]-48);
			set_humi[1] = (buf[3]-48)*100 + (buf[4]-48)*10 + (buf[5]-48);
			set_humi[2] = (buf[6]-48)*100 + (buf[7]-48)*10 + (buf[8]-48);
			set_humi[3] = (buf[9]-48)*100 + (buf[10]-48)*10 + (buf[11]-48);
			break;
		case 'o':
			iic_writebyte('o',5);		// �ڵ� ���ֱ� on
			auto_switch = 'o';
			break;
		case 'f':
			iic_writebyte('f',5);		// �ڵ� ���ֱ� off
			auto_switch = 'f';
			break;
		case 'm':						// ���� ���ֱ�
			uart_rece();				// ����� 'p' �� �޾Ƽ� �׳� ����
			manual_pot[uart_rece()-48]=1;	// ����� ���� ȭ�� �ĺ� ���ڸ� ����
			//PORTG = 0x01;		// ������ on
			//_delay_ms(5000);
			//PORTG = 0x00;
			break;
		case 'r':						// ������ �����Ͱ� ������ ��� ������
			adc_flag = SAMPLING_TIME;
			break;
	}
}

ISR(TIMER2_OVF_vect){
	TCNT2 = 100;			// 9.984ms = 10ms
	c_buf++;			
	if(c_buf>=6000){		// 1��
		adc_flag++;			// adc_flag �� ����� ��
		c_buf = 0;
	}
}


int main(void){
	//char res[5]={'\0'};
	volatile int humi[4]={'\0'};
	char open_flag=0;

	lcd_init();			// lcd �ʱ�ȭ
	uart_init();			// uart �ʱ�ȭ
	iic_init();			// iic ��� �ʱ�ȭ
	servo_init();			// �������� pwm �ʱ�ȭ
	sei();				// ���� ���ͷ�Ʈ ���
	TCNT2 = 100;			// Ÿ�̸� �ʱⰪ
	TCCR2 = 0x05;			// 1024 ���� �븻ī����
	TIMSK |= 0x40;			// Ÿ�̸�2 OVF ���	
	DDRG = 0xfd;			// PG1 : ������������ ����ġ, PG0 : ���� ������
	
	auto_switch = iic_readbyte(5);
	for(i=0;i<12;i++){
		buf[i] = iic_readbyte(i+10);
	}
	set_humi[0] = (buf[0]-48)*100 + (buf[1]-48)*10 + (buf[2]-48);
	set_humi[1] = (buf[3]-48)*100 + (buf[4]-48)*10 + (buf[5]-48);
	set_humi[2] = (buf[6]-48)*100 + (buf[7]-48)*10 + (buf[8]-48);
	set_humi[3] = (buf[9]-48)*100 + (buf[10]-48)*10 + (buf[11]-48);

	DDRB = 0xff;
	servo(1,CLOSE);
	servo(2,CLOSE);
	servo(3,CLOSE);
	servo(4,CLOSE);
	_delay_ms(1000);
	DDRB = 0x00;		// ���� ��ȣ off

	while(1){
		for(i=1;i<5;i++){
			if(manual_pot[i]){
				PORTG = 0x01;	
				DDRB = (0x01<<(i+3));
				switch(i){
					case 1:
						servo(1,OPEN);
						break;
					case 2:
						servo(2,OPEN);
						break;
					case 3:
						servo(3,OPEN);
						break;
					case 4:
						servo(4,OPEN);
						break;
				}
				_delay_ms(1000);
				DDRB = 0x00;			// ���� �Ҹ� �ּ�ȭ ���ؼ� ���������� ��ȣ ���ڸ� �Է����� ��ȯ
				_delay_ms(3000);
				
				DDRB = 0xff;
				servo(1,CLOSE);
				servo(2,CLOSE);
				servo(3,CLOSE);
				servo(4,CLOSE);
				_delay_ms(1000);
				DDRB = 0x00;
				PORTG = 0x00;
				manual_pot[i] = 0;
			}
		}
		
		for(i=0;i<4;i++){
			humi[i] = (500-adc_conv(i))*10/25-4;
			if(humi[i]<0){
				humi[i] = 0;
			}
			else if(humi[i] > 99){
				humi[i] = 99;
			}
		}
		lcd_string(strcat4("S1:",num_to_char(humi[0],0),", S2:",num_to_char(humi[1],1)),1);
		lcd_string(strcat4("S3:",num_to_char(humi[2],0),", S4:",num_to_char(humi[3],1)),2);
		
		if(adc_flag==SAMPLING_TIME){	
			for(i=0;i<4;i++){
				uart_send(48+i);
				uart_send(humi[i]/10 + 33);	// 10���ڸ� ����, ���ڷ� �ٲ㼭 ����������
				uart_send(humi[i]%10 + 33);	// 1���ڸ� ����, ���ڷ� �ٲ㼭 ����������
			}
			adc_flag=0;
		}

		if(auto_switch == 'o'){
			for(i=0;i<4;i++){
				humi[i] = (500-adc_conv(i))*10/25-4;
				if(humi[i]<0){
					humi[i] = 0;
				}
				else if(humi[i]>99){
					humi[i] = 99;
				}

				lcd_string(strcat4("S1:",num_to_char(humi[0],0),", S2:",num_to_char(humi[1],1)),1);
				lcd_string(strcat4("S3:",num_to_char(humi[2],0),", S4:",num_to_char(humi[3],1)),2);

				if(humi[i] <= set_humi[i]){
					open_flag = 1;
					PORTG = 0x01;				// ������ on
					DDRB = (0x01<<(i+4));		// PB4, PB5, PB6, PB7 ������ ȭ��1,2,3,4
					servo(i+1,OPEN);
						
					_delay_ms(1000);
					DDRB = 0x00;				// ���� �������� ����
					_delay_ms(4000);

					DDRB = (0x01<<(i+4));		// �������͸� 1������ �ϳ��� ���ʷ� ������� ����
					servo(i+1,CLOSE);			// ���� ����
					_delay_ms(1000);			
					DDRB = 0x00;				// ���� �������� ����
				}
			}
			PORTG = 0x00;
			
		}
		_delay_ms(1000);
		if(open_flag == 1){
			DDRB = 0xff;
			servo(1,CLOSE);
			servo(2,CLOSE);
			servo(3,CLOSE);
			servo(4,CLOSE);
			_delay_ms(1000);
			DDRB = 0x00;
			open_flag = 0;
		}
	}
}


void uart_init(void){
	UCSR0B = 0x98;	//tx,rx������ UCSZ02 = 0, �����ͱ��� 8��Ʈ , ���� ���ͷ�Ʈ 
	UCSR0C = 0x06;	//UCSZ01 = 1,UCSZ00 = 1,�и�Ƽ x, ������Ʈ 1 ,�����ͱ���8��Ʈ
	UBRR0L = 103;	//baudrate 9600bps 
	UBRR0H = 0;
}

void uart_send(unsigned char data){
	while(((UCSR0A)&(0x20))==0);
	UDR0 = data;
}

void uart_sends(char* x){
	while(*x != '\0'){
		uart_send(*x);
		x++;
	}
}

unsigned char uart_rece(void){
	while((UCSR0A & 0x80)==0);
	return UDR0;
}


void iic_init(void){
	TWBR = 0x01;
	TWSR = 0x01;
}	// 333 kbps

void iic_start(void){
	TWCR = 0xA4;
	while(!(TWCR & 0x80));

}

void iic_write(unsigned char data){
	TWDR = data;
	TWCR = 0x84;
	while(!(TWCR & 0x80));

}

char iic_read(void){
	TWCR = 0x84;
	while(!(TWCR & 0x80));
	return TWDR;
}

void iic_stop(void){
	TWCR = 0x94;

}

void iic_writebyte(unsigned char data,unsigned char add){
	do{
		iic_start();
		iic_write(0xA0);
	}while((TWSR & 0xf8) != 0x18);
	iic_write(add);
	iic_write(data);
	iic_stop();
}

char iic_readbyte(char add){
	char data;
	do{
		iic_start();
		iic_write(0xA0);
	}while((TWSR & 0xf8) != 0x18);
	iic_write(add);
	iic_start();
	iic_write(0xA1);
	data = iic_read();
	iic_stop();
	return data;
}

int adc_conv(int num){
	ADMUX = num;
	ADCSRA = 0xC7;				// ADC �㰡, ADC ��ȯ ����, 128����
 	while(!(ADCSRA & 0x10));	// AD��ȯ�Ϸ���� ��ٸ�
 	ADCSRA |= 0x10;				// ADC ���ͷ�Ʈ �÷��� Ŭ����
 	return ADC;
}

char* num_to_char(unsigned int num,int buf_num){
	n2c[buf_num][0] = num/100 + 48;
	n2c[buf_num][1] = num/10 - (num/100)*10 + 48;
	n2c[buf_num][2] = num%10 + 48;

	return n2c[buf_num];
}

void servo_init(void){
	DDRB = 0xFF;
	TCCR0 = 0x6F;	//1024 ����, fastpwm,�ֱ� 16ms
	TCCR1A = 0xA9;	//OCR1A, OCR1B,OCR1C 8��Ʈ pwm
	TCCR1B = 0x0D;	//1024 ����, fastpwm, 8��Ʈ pwm, �ֱ� 16ms
}
void servo(int num,int deg){
	if(num == 1){
		OCR0 = deg;
	}
	else if(num == 2){
		OCR1A = deg;
	}
	else if(num == 3){
		OCR1B = deg;
	}
	else if(num == 4){
		OCR1C = deg;
	}
}


void lcd_init(void){
	_delay_ms(100);
	DDRC = 0xFF;
	
	lcd_cmd(0x28);
	lcd_cmd(0x0C);
	lcd_cmd(0x06);
}

void lcd_data(char data){
	_delay_us(50);
	PORTC = 0x05;
	PORTC = (data & 0xF0) | 0x05;
	PORTC = 0;
	PORTC = 0x05;
	PORTC = ((data & 0x0F)<<4) | 0x05;
	PORTC = 0;
	_delay_ms(1);
}

void lcd_string(char *data,int num){
	switch(num){
		case 1:
			lcd_cmd(0x80);
			break;
		case 2:
			lcd_cmd(0xC0);
			break;
		case 3:
			lcd_cmd(0x94);
			break;
		case 4:
			lcd_cmd(0xD4);
			break;
		
	}

	while(*data != '\0'){
		lcd_data(*data);
		data++;
	}
}

void lcd_cmd(char data){
	_delay_us(50);
	PORTC = 0x04;
	PORTC = (data & 0xF0) | 0x04;
	PORTC = 0;
	PORTC = 0x04;
	PORTC = ((data & 0x0F)<<4) | 0x04;
	PORTC = 0;
	_delay_ms(1);
}
char* strcat4(char* str1,char* str2,char* str3,char* str4){	
	int i=0;
	while(*str1 != '\0'){
		strcat_buf[i] = *str1;
		i++;
		str1++;
	}
	while(*str2 != '\0'){
		strcat_buf[i] = *str2;
		str2++;
		i++;
	}
	while(*str3 != '\0'){
		strcat_buf[i] = *str3;
		i++;
		str3++;
	}
	while(*str4 != '\0'){
		strcat_buf[i] = *str4;
		str4++;
		i++;
	}

	return strcat_buf;
}
