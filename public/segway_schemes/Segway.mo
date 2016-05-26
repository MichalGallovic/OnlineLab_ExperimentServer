package Segway
  model reference_val
    extends Modelica.Blocks.Icons.Block;
    Modelica.Blocks.Interfaces.RealOutput ref_val "Connector of Real output signals 1" annotation(Placement(transformation(extent = {{100, 70}, {120, 90}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val2 "Connector of Real output signals 2" annotation(Placement(transformation(extent = {{100, 30}, {120, 50}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val3 "Connector of Real output signals 3" annotation(Placement(transformation(extent = {{100, -10}, {120, 10}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val4 "Connector of Real output signals 4" annotation(Placement(transformation(extent = {{100, -50}, {120, -30}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val5 "Connector of Real output signals 5" annotation(Placement(transformation(extent = {{100, -90}, {120, -70}}, rotation = 0)));
  equation

  algorithm
    ref_val := 0;
    user_val2 := 1;
    user_val3 := 2;
    user_val4 := 3;
    user_val5 := 4;
    annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), experiment(StartTime = 0, StopTime = 100, Tolerance = 1e-06, Interval = 0.01));
  end reference_val;

  model communication_helper
    extends Modelica.Blocks.Icons.Block;
    Modelica.Blocks.Interfaces.RealOutput phi "Connector of Real output signal phi" annotation(Placement(transformation(extent = {{100, 70}, {120, 90}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput speedR "Connector of Real output signal speedR" annotation(Placement(transformation(extent = {{100, 30}, {120, 50}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput speedL "Connector of Real output signal speedL" annotation(Placement(transformation(extent = {{100, -10}, {120, 10}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput posR "Connector of Real output signal posR" annotation(Placement(transformation(extent = {{100, -50}, {120, -30}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput posL "Connector of Real output signal posL" annotation(Placement(transformation(extent = {{100, -90}, {120, -70}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealInput u "Connector of Real input signal u" annotation(Placement(transformation(extent = {{-140, -20}, {-100, 20}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput alphaR(start = 0) "Connector of Real output signal angle traveled by R" annotation(Placement(transformation(origin = {60, -120}, extent = {{10, -10}, {-20, 20}}, rotation = 90)));
    Modelica.Blocks.Interfaces.RealOutput alphaL(start = 0) "Connector of Real output signal angle traveled by L" annotation(Placement(transformation(origin = {20, -120}, extent = {{10, -10}, {-20, 20}}, rotation = 90)));
    Modelica.Blocks.Interfaces.RealOutput xR(start = 0) "Connector of Real output signal distance traveled by R" annotation(Placement(transformation(origin = {-20, -120}, extent = {{10, -10}, {-20, 20}}, rotation = 90)));
    Modelica.Blocks.Interfaces.RealOutput xL(start = 0) "Connector of Real output signal distance traveled by L" annotation(Placement(transformation(origin = {-60, -120}, extent = {{10, -10}, {-20, 20}}, rotation = 90)));
    Modelica.Blocks.Interfaces.RealOutput omegaR(start = 0) "Connector of Real output signal angle traveled by R" annotation(Placement(transformation(origin = {-60, 110}, extent = {{10, -10}, {-20, 20}}, rotation = -90)));
    Modelica.Blocks.Interfaces.RealOutput omegaL(start = 0) "Connector of Real output signal angle traveled by L" annotation(Placement(transformation(origin = {-20, 110}, extent = {{10, -10}, {-20, 20}}, rotation = -90)));
    Modelica.Blocks.Interfaces.RealOutput vR(start = 0) "Connector of Real output signal distance traveled by R" annotation(Placement(transformation(origin = {20, 110}, extent = {{10, -10}, {-20, 20}}, rotation = -90)));
    Modelica.Blocks.Interfaces.RealOutput vL(start = 0) "Connector of Real output signal distance traveled by L" annotation(Placement(transformation(origin = {60, 110}, extent = {{10, -10}, {-20, 20}}, rotation = -90)));
    // final parameter Real encoder_ticsperrotation(start = 408.1675) "number of tics per rotation"; //onechannel
    final parameter Real encoder_ticsperrotation(start = 1632.67) "number of tics per rotation";
    //bothchannel rising and falling edges
    final parameter Real rR(start = 3) "radius of R";
    final parameter Real rL(start = 3) "radius of L";
  equation
/* der(varsArr[1]) = 0;
  der(varsArr[2]) = 0;
  der(varsArr[3]) = 0;
  der(varsArr[4]) = 0;
  der(varsArr[5]) = 0;*/
// ErrorType=ComunicationInterface(u, time, varsArr);
    der(phi) = 0.02 * u - 0.01;
    speedR = 0.1 * u;
    speedL = 0.1 * u;
    der(posR) = speedR;
    der(posL) = speedL;
    alphaR = posR * 360.0 / encoder_ticsperrotation;
    alphaL = posL * 360.0 / encoder_ticsperrotation;
    xR = posR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation;
    xL = posL * 2 * Modelica.Constants.pi * rL / encoder_ticsperrotation;
    omegaR = speedR * 360.0 / encoder_ticsperrotation;
    omegaL = speedL * 360.0 / encoder_ticsperrotation;
    vR = speedR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation;
    vL = speedL * 2 * Modelica.Constants.pi * rL / encoder_ticsperrotation;
    annotation(Icon, Diagram, experiment(StartTime = 0, StopTime = 20, Tolerance = 1e-06, Interval = 0.01));
  end communication_helper;

  model simulation
    Segway.communication_helper communication_helper1 annotation(Placement(visible = true, transformation(origin = {46, 30}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  Segway.reference_val reference_val1 annotation(Placement(visible = true, transformation(origin = {-118, 30}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  Modelica.Blocks.Continuous.LimPID PID(Nd = 0.038 / 0.005, Td = 0.04, Ti = 0.1, controllerType = Modelica.Blocks.Types.SimpleController.PID, k = 26, yMax = 175, yMin = -175) annotation(Placement(visible = true, transformation(origin = {-58, 30}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  Modelica.Blocks.Math.Gain gain1(k = -1)  annotation(Placement(visible = true, transformation(origin = {6, 30}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  equation
    connect(PID.y, gain1.u) annotation(Line(points = {{-46, 30}, {-6, 30}, {-6, 30}, {-6, 30}}, color = {0, 0, 127}));
    connect(gain1.y, communication_helper1.u) annotation(Line(points = {{18, 30}, {32, 30}, {32, 30}, {34, 30}}, color = {0, 0, 127}));
    connect(communication_helper1.phi, PID.u_m) annotation(Line(points = {{58, 38}, {80, 38}, {80, 2}, {-58, 2}, {-58, 18}}, color = {0, 0, 127}));
    connect(reference_val1.ref_val, PID.u_s) annotation(Line(points = {{-107, 38}, {-95.5, 38}, {-95.5, 38}, {-84, 38}, {-84, 30}, {-70, 30}}, color = {0, 0, 127}));
    annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), uses(Modelica(version = "3.2.1")));
  end simulation;
  annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})));
end Segway;