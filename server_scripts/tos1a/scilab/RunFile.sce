chdir('/var/www/olm_app_server/server_scripts/tos1a/scilab');
loadXcosLibs();

c_port = ascii(port);
//ilib_for_link('termo','termo.c',[],'c','','loader.sce','','','-g');
exec loader.sce

select own_ctrl,
  case 0 then importXcosDiagram("termo_model_controller.xcos"),
  case 1 then importXcosDiagram("termo_model_controllerOwn.xcos"),
  case 2 then importXcosDiagram(uploaded_file),
  else printf("simulation problem"),
end

//abs_path = get_absolute_file_path("RunFile.sce");
tmpfile_path=output;
//tmpfile   _path = abs_path+"tmpfile.txt";
//warning('off');
xcos_simulate(scs_m,4);





