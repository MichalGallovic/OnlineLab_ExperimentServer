chdir('/var/www/olm_app_server/server_scripts/tos1a/scilab');
loadXcosLibs();

global y;
global e;
y = list(0,0,0,0);
e = list(0,0,0,0); 

c_port = ascii(port);

exec loader.sce

select own_ctrl,
  case 0 then importXcosDiagram("termo_model_controller.xcos"),
  case 1 then importXcosDiagram("termo_model_controllerOwn.xcos"),
  case 2 then importXcosDiagram(uploaded_file),
  else printf("simulation problem"),
end

tmpfile_path=output;
//warning('off');
xcos_simulate(scs_m,4);





