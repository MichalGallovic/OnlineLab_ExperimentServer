chdir('/var/www/olm_app_server/server_scripts/tos1a/scilab');
loadXcosLibs();
//ts=0.2;
//time=10;
//c_lamp=50;
//c_led=0;
//c_fan=0;
//port="/dev/ttyACM0";
//
////output="/home/andrej/Plocha/ScilabTermo/tmpfile.txt";
//output="/var/www/olm_app_server/server_scripts/tos1a/scilab/tmpfile.txt";
//own_ctrl=1;   // 0- PID    1-own function
//
//in_sw=1;  //1-lampa  2-ledka 3-ventilator 
//out_sw=2; //1-filtrovaná teplota interná, 2- filtrovaná svetelná intenzita lineárna, 3-filtrované otáčky,
//required_value=50; //pozadovana hodnota
//
//P=0.4;  //0.08 na otacky
//I=1;    //0.1  1.5 len na intenzitu
//D=0;
//
//
//// toto pojde zo shell scriptu
//function y1=user_reg_func(u1,u2,u3,u4)
//  y1=u1;
//    endfunction

c_port = ascii(port);
//ilib_for_link('termo','termo.c',[],'c','','loader.sce','','','-g');
exec loader.sce

select own_ctrl,
  case 0 then importXcosDiagram("termo_model_controller.xcos"),
  case 1 then importXcosDiagram("termo_model_controllerOwn.xcos"),
  else printf("simulation problem"),
end

//select own_ctrl,
//  case 0 then importXcosDiagram("termo_model_controllerGraph.xcos"),
//  case 1 then importXcosDiagram("termo_model_controllerOwnGraph.xcos"),
//  else printf("simulation problem"),
// end

//abs_path = get_absolute_file_path("RunFile.sce");
tmpfile_path=output;
//tmpfile   _path = abs_path+"tmpfile.txt";
//warning('off');
xcos_simulate(scs_m,4);





