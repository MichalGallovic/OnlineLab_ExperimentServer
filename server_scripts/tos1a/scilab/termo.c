#include <scicos_block4.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <time.h>

#define r_IN(n, i)  ((GetRealInPortPtrs(blk, (n) + 1))[(i)])
#define r_OUT(n, i) ((GetRealOutPortPtrs(blk, (n) + 1))[(i)])
/*
#define VENDORID 9025
#define PRODUCTID 62
*/
#define bdrate 115200

// vstupy
#define inLamp	(r_IN(0,0))	// lamp
#define inLed	(r_IN(1,0))	// led
#define	inFan	(r_IN(2,0))	// fan

// vystupy
#define outTemp   (r_OUT(0,0))  // teplota dosky plošného spoja,
#define outFiltTempInternal (r_OUT(1,0))  // filtrovaná teplota interná,
#define outDerTempInternal (r_OUT(2,0))  // derivácia teplota interná,
#define outFiltTempExternal (r_OUT(3,0))  // filtrovaná teplota externá,
#define outDerTempExternal (r_OUT(4,0))  // derivácia teplota externá,
#define outFiltLightLinear  (r_OUT(5,0))  // filtrovaná svetelná intenzita, lineárna,
#define outDerLightLinear  (r_OUT(6,0))  // derivácia svetelnej intenzity, lineárna,
#define outFiltLightLog  (r_OUT(7,0))  // filtrovaná svetelná intenzita, logaritmická,
#define outDerLightLog  (r_OUT(8,0))  // derivácia svetelnej intenzity, logaritmická,
#define outAmpereLight    (r_OUT(9,0))  // prúd žiarovky
#define outVoltageLight    (r_OUT(10,0)) // napätie na žiarovke
#define outAmpereFan    (r_OUT(11,0))  // prúd ventilátora
#define outVoltageFan    (r_OUT(12,0)) // napätie na ventilátore
#define outFanRPM    (r_OUT(13,0))  // filtrované otáčky,
#define outDerFanRPM (r_OUT(14,0))  // derivácia otáčok
#define outChecksum (r_OUT(15,0))  // checksum vystupov, to co je za * z outputu zariadenia

//test nemohol som nalinkovat externu kniznicu tak som ju sem skopiroval... blby scilab

#include <termios.h>
#include <sys/ioctl.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <limits.h>
#include <sys/file.h>

int Cport,
    error_code;

struct termios new_port_settings,
       old_port_settings[38];

int OpenComport(char *port, int baudrate, const char *mode)
{
  int baudr,
      status;

  switch(baudrate)
  {
    case      50 : baudr = B50;
                   break;
    case      75 : baudr = B75;
                   break;
    case     110 : baudr = B110;
                   break;
    case     134 : baudr = B134;
                   break;
    case     150 : baudr = B150;
                   break;
    case     200 : baudr = B200;
                   break;
    case     300 : baudr = B300;
                   break;
    case     600 : baudr = B600;
                   break;
    case    1200 : baudr = B1200;
                   break;
    case    1800 : baudr = B1800;
                   break;
    case    2400 : baudr = B2400;
                   break;
    case    4800 : baudr = B4800;
                   break;
    case    9600 : baudr = B9600;
                   break;
    case   19200 : baudr = B19200;
                   break;
    case   38400 : baudr = B38400;
                   break;
    case   57600 : baudr = B57600;
                   break;
    case  115200 : baudr = B115200;
                   break;
    case  230400 : baudr = B230400;
                   break;
    case  460800 : baudr = B460800;
                   break;
    case  500000 : baudr = B500000;
                   break;
    case  576000 : baudr = B576000;
                   break;
    case  921600 : baudr = B921600;
                   break;
    case 1000000 : baudr = B1000000;
                   break;
    case 1152000 : baudr = B1152000;
                   break;
    case 1500000 : baudr = B1500000;
                   break;
    case 2000000 : baudr = B2000000;
                   break;
    case 2500000 : baudr = B2500000;
                   break;
    case 3000000 : baudr = B3000000;
                   break;
    case 3500000 : baudr = B3500000;
                   break;
    case 4000000 : baudr = B4000000;
                   break;
    default      : printf("invalid baudrate\n");
                   return(1);
                   break;
  }

  int cbits=CS8,
      cpar=0,
      ipar=IGNPAR,
      bstop=0;

  if(strlen(mode) != 3)
  {
    printf("invalid mode \"%s\"\n", mode);
    return(1);
  }

  switch(mode[0])
  {
    case '8': cbits = CS8;
              break;
    case '7': cbits = CS7;
              break;
    case '6': cbits = CS6;
              break;
    case '5': cbits = CS5;
              break;
    default : printf("invalid number of data-bits '%c'\n", mode[0]);
              return(1);
              break;
  }

  switch(mode[1])
  {
    case 'N':
    case 'n': cpar = 0;
              ipar = IGNPAR;
              break;
    case 'E':
    case 'e': cpar = PARENB;
              ipar = INPCK;
              break;
    case 'O':
    case 'o': cpar = (PARENB | PARODD);
              ipar = INPCK;
              break;
    default : printf("invalid parity '%c'\n", mode[1]);
              return(1);
              break;
  }

  switch(mode[2])
  {
    case '1': bstop = 0;
              break;
    case '2': bstop = CSTOPB;
              break;
    default : printf("invalid number of stop bits '%c'\n", mode[2]);
              return(1);
              break;
  }

/*
http://pubs.opengroup.org/onlinepubs/7908799/xsh/termios.h.html

http://man7.org/linux/man-pages/man3/termios.3.html
*/
    struct stat sb;  

    if (stat(port, &sb) == 0)
    {
      printf("YES File exist\n");   
    }
    else
    {
      printf("NO FIle doesnt exist\n"); 
    }

  Cport = open(port, O_RDWR | O_NOCTTY | O_NDELAY);
  //printf("AAAA%s\n", port);
  if(Cport==-1)
  {
    perror("unable to open comport ");
    return(1);
  }

  /* lock access so that another process can't also use the port */
  if(flock(Cport, LOCK_EX | LOCK_NB) != 0)
  {
    close(Cport);
    perror("Another process has locked the comport.");
    return(1);
  }

  error_code = tcgetattr(Cport, old_port_settings);
  if(error_code==-1)
  {	
    close(Cport);

    perror("unable to read portsettings ");
    return(1);
  } 
  memset(&new_port_settings, 0, sizeof(new_port_settings));  /* clear the new struct */

  new_port_settings.c_cflag = cbits | cpar | bstop | CLOCAL | CREAD;
  new_port_settings.c_iflag = ipar;
  new_port_settings.c_oflag = 0;
  new_port_settings.c_lflag = 0;
  new_port_settings.c_cc[VMIN] = 0;      /* block untill n bytes are received */
  new_port_settings.c_cc[VTIME] = 0;     /* block untill a timer expires (n * 100 mSec.) */

  cfsetispeed(&new_port_settings, baudr);
  cfsetospeed(&new_port_settings, baudr);

  error_code = tcsetattr(Cport, TCSANOW, &new_port_settings);
  if(error_code==-1)
  {
    close(Cport);
    perror("unable to adjust portsettings ");
    return(1);
  }

  if(ioctl(Cport, TIOCMGET, &status) == -1)
  {
    perror("unable to get portstatus");
    return(1);
  }

  status |= TIOCM_DTR;    /* turn on DTR */
  status |= TIOCM_RTS;    /* turn on RTS */

  if(ioctl(Cport, TIOCMSET, &status) == -1)
  {
    perror("unable to set portstatus");
    return(1);
  }

  return (0);
}


int PollComport(unsigned char *buf, int size)
{
  int n;

  n = read(Cport, buf, size);

  return(n);
}


int SendByte(unsigned char byte)
{
  int n;

  n = write(Cport, &byte, 1);
  if(n<0)  return(1);

  return(0);
}


int SendBuf(unsigned char *buf, int size)
{
  return(write(Cport, buf, size));
}


void CloseComport()
{
  int status;

  if(ioctl(Cport, TIOCMGET, &status) == -1)
  {
    perror("unable to get portstatus");
  }

  status &= ~TIOCM_DTR;    /* turn off DTR */
  status &= ~TIOCM_RTS;    /* turn off RTS */

  if(ioctl(Cport, TIOCMSET, &status) == -1)
  {
    perror("unable to set portstatus");
  }

  tcsetattr(Cport, TCSANOW, old_port_settings);
  close(Cport);

  flock(Cport, LOCK_UN); /* free the port so that others can use it. */
}




unsigned char checkSum(unsigned char *str) {
  unsigned char answer = 0;
  unsigned int i;
    for (i = 0; i < strlen((char*)str); ++i)
    {   // XOR each byte of the string and stores the result in answer.
        answer ^= str[i];
    }
    return answer;
}


int tos_init(char *port) {
// inicializacia zariadenia, otvorenie portu a poslanie SSE
	char mode[]={'8','N','1',0};

 	unsigned char init[64];
	memcpy(init, "$SSE*45\n", 9);

	if(OpenComport(port, bdrate, mode)) {
		printf("Can not open comport\n");

		return(0);
	} else {
		printf("Port opened\n");
	}

	SendBuf(init, strlen((char*)init));
	printf("sent: %s\n", init);

	return 1;
}

void tos_write(double lamp, double led, double fan) {
  // nastavi vstupne napatia na zariadeni
  unsigned char paramettres[64];
  char msg[65];
  unsigned char control_checksum;
  char check[65];

  sprintf(check, "SGV,%f,%f,%f"
      , (lamp)
      , (fan)
      , (led));
  control_checksum = checkSum((unsigned char*)check);

  //printf("%x\n",control_checksum);

  sprintf(msg, "$SGV,%f,%f,%f*%X\n"
      , (lamp)
      , (fan)
      , (led)
      , control_checksum);
  printf("%s\n",msg);
  
  memcpy(paramettres, msg, strlen(msg));

  SendBuf(paramettres, strlen((char*)paramettres));   

}

void tos_writeRead() {
  
  unsigned char paramettres[64];
  char msg[65];
  unsigned char control_checksum;
  char check[65];

  sprintf(check, "SGV");
  control_checksum = checkSum((unsigned char*)check);

  //printf("%x\n",control_checksum);

  sprintf(msg, "$SGV*%x\n", control_checksum);
  //printf("%s\n",msg);
  
  memcpy(paramettres, msg, strlen(msg));

  SendBuf(paramettres, strlen((char*)paramettres));   
  //printf("RequestData");

}


int tos_read(double *Temp, double *FiltTempInternal, double *DerTempInternal, double *FiltTempExternal, double *DerTempExternal, double *FiltLightLinear, double *DerLightLinear, double *FiltLightLog, double *DerLightLog, double *AmpereLight, double *VoltageLight, double *AmpereFan, double *VoltageFan,double *FanRPM, double *DerFanRPM, double *Checksum) {
  // nacita vystupne hodnoty zo zariadenia
  // vrati -1 ak prisli chybne data. inak 0
  int n,i,c=0,returnValue = 0;
  unsigned char buf[4096];
  char *delim = ","; // input separated by spaces
  char *token = NULL;
  char *checkSum = NULL;
  double out[15];

    n = PollComport(buf, 4095);
      
      if(n > 0)
      {
        buf[n] = 0;   // always put a "null" at the end of a string! 

        for(i=0; i < n; i++)
        {
          if(buf[i] < 32)  // replace unreadable control-codes by null 
          {
            buf[i] = 0;
          }
        }
        if (buf[0] == '\0'){
          printf ("EMPTY DATA YET\n");
          returnValue = 0;
        } else {
            returnValue = 1;
            //fprintf(fp, "%s\n", (char *)buf); // write to file whole result from device
            printf("LOAD DATA START-\n");
            printf("received %i bytes: %s\n", n, (char *)buf);
            printf ("%i\n",strlen(buf));
            printf("LOAD DATA END-\n");

            memmove(buf, buf+1, strlen(buf)); // remove $ from start 

            token = strtok(buf, "*");
            checkSum = strtok(NULL, "*");
            double dCheck;
            sscanf(checkSum, "%lf", &dCheck);

            token = NULL;

            out[0]=0;out[1]=0;out[2]=0;out[3]=0;out[4]=0;out[5]=0;out[6]=0;out[7]=0;out[8]=0;out[9]=0;out[10]=0;out[11]=0;out[12]=0;out[13]=0;out[14]=0;

              for (c = 0,token = strtok(buf, delim); token != NULL; token = strtok(NULL, delim), c++) {
                double d=0;
                sscanf(token, "%lf", &d);

                //printf("%lf\n", d);
                out[c] = d;
              }

            *Temp = out[0];
            *FiltTempInternal = out[1];
            *DerTempInternal= out[2];
            *FiltTempExternal = out[3];
            *DerTempExternal= out[4];
            *FiltLightLinear = out[5];
            *DerLightLinear= out[6];
            *FiltLightLog= out[7];
            *DerLightLog = out[8];
            *AmpereLight = out[9];
            *VoltageLight = out[10];
            *AmpereFan = out[11];
            *VoltageFan= out[12];
            *FanRPM = out[13];
            *DerFanRPM = out[14];
            *Checksum = dCheck;
            
        }
      }
      else {
        printf("NODATA\n");
        returnValue = 0;

      }

  return returnValue;
}

void tos_close() {
	// posle na zariadenie nuly a ukonci komunikaciu
 	unsigned char close[64];
	memcpy(close, "$SEE*53\n", 9);

 	SendBuf(close, strlen((char*)close));
    printf("sent: %s\n", close);

    CloseComport();

	printf("--------------------\n");
}

typedef struct {
  double last_outTemp; 
  double last_outFiltTempInternal;
  double last_outDerTempInternal;
  double last_outFiltTempExternal;
  double last_outDerTempExternal;
  double last_outFiltLightLinear;
  double last_outDerLightLinear;  
  double last_outFiltLightLog;  
  double last_outDerLightLog;  
  double last_outAmpereLight;   
  double last_outVoltageLight;   
  double last_outAmpereFan;   
  double last_outVoltageFan;   
  double last_outFanRPM;  
  double last_outDerFanRPM;
  double last_outChecksum;
	int counter;
} han;

void termo(scicos_block *blk, int flag)
{
	han *ptr;
	int i,k, errTest=0;
	char port_string[13];
  double Temp;
  double FiltTempInternal;
  double DerTempInternal;
  double FiltTempExternal;
  double DerTempExternal;
  double FiltLightLinear;
  double DerLightLinear;
  double FiltLightLog;
  double DerLightLog;
  double AmpereLight;
  double VoltageLight;
  double AmpereFan;
  double VoltageFan;
  double FanRPM;
  double DerFanRPM;
  double Checksum;

switch (flag) {

	case 4: //Init
		printf("INIT\n");

		*(blk->work) = (han*)malloc(sizeof(han));
    ptr = *(blk->work);
    ptr->last_outTemp  = 0;
    ptr->last_outFiltTempInternal = 0;
    ptr->last_outDerTempInternal = 0;
    ptr->last_outFiltTempExternal = 0;
    ptr->last_outDerTempExternal  = 0;
    ptr->last_outFiltLightLinear  = 0;
    ptr->last_outDerLightLinear   = 0;
    ptr->last_outFiltLightLog   = 0;
    ptr->last_outDerLightLog   = 0;
    ptr->last_outAmpereLight    = 0;
    ptr->last_outVoltageLight    = 0;
    ptr->last_outAmpereFan    = 0;
    ptr->last_outVoltageFan    = 0;
    ptr->last_outFanRPM   = 0;
    ptr->last_outDerFanRPM  = 0;
    ptr->last_outChecksum = 0;
		ptr->counter = 0; 
		
		break;
	case 1: // Output kazdu periodu vzorkovania
		ptr = *(blk->work);
		if (ptr->counter == 0) {
			for (i = 0 ; i < sizeof((GetRealInPortPtrs(blk, 4)))+4 ; i++)
			{
			    port_string[i] = (int)(GetRealInPortPtrs(blk, 4))[i];
          //printf("%c", port_string[i]);
			}
			if (port_string[i] < 0){
         port_string[i]=0;
      }else {
          port_string[i+1]=0;
      }

      printf("PORT ID START\n");
      printf("PORT: %s\n",port_string);
      printf("PORT ID END\n");

		}

		if (ptr->counter == 0) {
      ptr->counter++;
			tos_init(port_string);
			usleep(1000);
			tos_write(inLamp, inLed, inFan);
			//usleep(10000);
      errTest = tos_read(&Temp, &FiltTempInternal, &DerTempInternal, &FiltTempExternal, &DerTempExternal, &FiltLightLinear, &DerLightLinear, &FiltLightLog, &DerLightLog, &AmpereLight, &VoltageLight, &AmpereFan, &VoltageFan, &FanRPM, &DerFanRPM, &Checksum);
      //usleep(10000);
      //tos_writeRead();
      //usleep(10000);

    }else {      
      ptr->counter++;
      //usleep(2000);
      tos_write(inLamp, inLed, inFan);
      //usleep(2000);
      errTest = tos_read(&Temp, &FiltTempInternal, &DerTempInternal, &FiltTempExternal, &DerTempExternal, &FiltLightLinear, &DerLightLinear, &FiltLightLog, &DerLightLog, &AmpereLight, &VoltageLight, &AmpereFan, &VoltageFan, &FanRPM, &DerFanRPM,&Checksum);
      //usleep(10000);
      printf("------\n");     

    }

    if ( errTest ) { 
      ptr->last_outTemp = outTemp = Temp;
      ptr->last_outFiltTempInternal = outFiltTempInternal  = FiltTempInternal;
      ptr->last_outDerTempInternal = outDerTempInternal = DerTempInternal;
      ptr->last_outFiltTempExternal = outFiltTempExternal  = FiltTempExternal;
      ptr->last_outDerTempExternal = outDerTempExternal = DerTempExternal;
      ptr->last_outFiltLightLinear = outFiltLightLinear  = FiltLightLinear;
      ptr->last_outDerLightLinear = outDerLightLinear = DerLightLinear;
      ptr->last_outFiltLightLog = outFiltLightLog = FiltLightLog;
      ptr->last_outDerLightLog = outDerLightLog  = DerLightLog;
      ptr->last_outAmpereLight = outAmpereLight  = AmpereLight;
      ptr->last_outVoltageLight = outVoltageLight  = VoltageLight;
      ptr->last_outAmpereFan = outAmpereFan  = AmpereFan;
      ptr->last_outVoltageFan = outVoltageFan = VoltageFan;
      ptr->last_outFanRPM = outFanRPM  = FanRPM;
      ptr->last_outDerFanRPM = outDerFanRPM  = DerFanRPM;
      ptr->last_outChecksum = outChecksum = Checksum;
    } else {
      outTemp  = ptr->last_outTemp;
      outFiltTempInternal  = ptr->last_outFiltTempInternal;
      outDerTempInternal = ptr->last_outDerTempInternal;
      outFiltTempExternal  = ptr->last_outFiltTempExternal;
      outDerTempExternal = ptr->last_outDerTempExternal;
      outFiltLightLinear  = ptr->last_outFiltLightLinear;
      outDerLightLinear = ptr->last_outDerLightLinear;
      outFiltLightLog = ptr->last_outFiltLightLog;
      outDerLightLog  = ptr->last_outDerLightLog;
      outAmpereLight  = ptr->last_outAmpereLight;
      outVoltageLight  = ptr->last_outVoltageLight;
      outAmpereFan  = ptr->last_outAmpereFan;
      outVoltageFan = ptr->last_outVoltageFan;
      outFanRPM  = ptr->last_outFanRPM;
      outDerFanRPM  = ptr->last_outDerFanRPM;
      outChecksum = ptr->last_outChecksum;
    }
    
		break;

	case 5: //Ending
		ptr = *(blk->work);
		tos_close();
		free(*(blk->work));
    		
		break;
		

}


}
