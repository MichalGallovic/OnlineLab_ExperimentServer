package Segway
  model reference_val
    extends Modelica.Blocks.Icons.Block;
    Modelica.Blocks.Interfaces.RealOutput ref_val "Connector of Real output signals 1" annotation(Placement(transformation(extent = {{100, 70}, {120, 90}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val2 "Connector of Real output signals 2" annotation(Placement(transformation(extent = {{100, 30}, {120, 50}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val3 "Connector of Real output signals 3" annotation(Placement(transformation(extent = {{100, -10}, {120, 10}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val4 "Connector of Real output signals 4" annotation(Placement(transformation(extent = {{100, -50}, {120, -30}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput user_val5 "Connector of Real output signals 5" annotation(Placement(transformation(extent = {{100, -90}, {120, -70}}, rotation = 0)));
    Real varsArr[5];

    function initTimetck
      input Modelica.SIunits.Time iTime;
    
      external  annotation(Library = "/home/fei_admin2/NetBeansProjects/OMCklient/testing/libgetref.o", Include = "#include \"/home/fei_admin2/NetBeansProjects/OMCklient/testing/Handle_data.h\"");
    end initTimetck;

    function Get_reference_signal
      input Modelica.SIunits.Time timesim;
      input Real ref[5];
      output Real x;
    
      external  annotation(Library = "/home/fei_admin2/NetBeansProjects/OMCklient/testing/libgetref.o", Include = "#include \"/home/fei_admin2/NetBeansProjects/OMCklient/testing/Handle_data.h\"");
    end Get_reference_signal;
  equation

  algorithm
    if initial() then
      noEvent(initTimetck(time));
    end if;
    noEvent(Get_reference_signal(time, varsArr));
    ref_val := noEvent(varsArr[1]);
    user_val2 := noEvent(varsArr[2]);
    user_val3 := noEvent(varsArr[3]);
    user_val4 := noEvent(varsArr[4]);
    user_val5 := noEvent(varsArr[5]);
    varsArr := noEvent(varsArr);
    annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), experiment(StartTime = 0, StopTime = 100, Tolerance = 1e-06, Interval = 0.01));
  end reference_val;

  partial record SHM_KEY
    parameter Integer SHM_KEY_VALUE = 16000;
  end SHM_KEY;

  model simulation_example
    Modelica.Blocks.Math.Add add1(k1 = 1, k2 = -1) annotation(Placement(visible = true, transformation(origin = {-44, 4}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Gain gain1(k = 100) annotation(Placement(visible = true, transformation(origin = {-8, 4}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Modelica.Blocks.Routing.DeMultiplex5 deMultiplex51(n1 = 1, n2 = 1, n3 = 1, n4 = 1, n5 = 1) annotation(Placement(visible = true, transformation(origin = {62, 4}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    reference_val reference_val1 annotation(Placement(visible = true, transformation(origin = {-88, 10}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  equation
    connect(reference_val1.y, add1.u1) annotation(Line(points = {{-77, 10}, {-56, 10}}, color = {0, 0, 127}));
    connect(deMultiplex51.y1[1], add1.u2) annotation(Line(points = {{73, 12}, {84, 12}, {84, -34}, {-78, -34}, {-78, -2}, {-56, -2}}, color = {0, 0, 127}));
    connect(add1.y, gain1.u) annotation(Line(points = {{-33, 4}, {-20, 4}, {-20, 4}, {-20, 4}}, color = {0, 0, 127}));
    annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})));
  end simulation_example;

  model simulation
    Segway.reference_val reference_val1 annotation(Placement(visible = true, transformation(origin = {-100, 20}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Gain gain1(k = -1) annotation(Placement(visible = true, transformation(origin = {42, 36}, extent = {{-8, -8}, {8, 8}}, rotation = 0)));
    Segway.communication_helper communication_helper1 annotation(Placement(visible = true, transformation(origin = {70, 36}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Modelica.Blocks.Continuous.LimPID PID(Nd = 0.038 / 0.005, Ni = 0.9, Td = 0.04, Ti = 0.1, controllerType = Modelica.Blocks.Types.SimpleController.PID, k = 26, wd = 0, wp = 0, yMax = 175, yMin = -175) annotation(Placement(visible = true, transformation(origin = {-60, 42}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Modelica.Blocks.Continuous.LimPID PID2(Nd = 0.038 / 0.005, Ni = 0.9, Td = 0.04, Ti = 0.1, controllerType = Modelica.Blocks.Types.SimpleController.PD, k = 40, wd = 0, wp = 0, yMax = 175, yMin = -175) annotation(Placement(visible = true, transformation(origin = {-34, 14}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Segway.PIDselector pIDselector1 annotation(Placement(visible = true, transformation(origin = {12, 36}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  equation
    connect(reference_val1.ref_val, PID2.u_s) annotation(Line(points = {{-88, 28}, {-76, 28}, {-76, 14}, {-46, 14}, {-46, 14}}, color = {0, 0, 127}));
    connect(reference_val1.ref_val, PID.u_s) annotation(Line(points = {{-89, 28}, {-76, 28}, {-76, 42}, {-72, 42}}, color = {0, 0, 127}));
    connect(communication_helper1.phi, PID.u_m) annotation(Line(points = {{81, 44}, {90, 44}, {90, -4}, {-60, -4}, {-60, 30}}, color = {0, 0, 127}));
    connect(PID.y, pIDselector1.u1) annotation(Line(points = {{-49, 42}, {0, 42}, {0, 43}}, color = {0, 0, 127}));
    connect(PID2.y, pIDselector1.u2) annotation(Line(points = {{-23, 14}, {-20.5, 14}, {-20.5, 36}, {0, 36}}, color = {0, 0, 127}));
    connect(pIDselector1.y, gain1.u) annotation(Line(points = {{23, 36}, {32, 36}}, color = {0, 0, 127}));
    connect(communication_helper1.phi, pIDselector1.phi) annotation(Line(points = {{81, 44}, {90, 44}, {90, -4}, {0, -4}, {0, 29}}, color = {0, 0, 127}));
    connect(communication_helper1.phi, PID2.u_m) annotation(Line(points = {{81, 44}, {90, 44}, {90, -4}, {-33.3125, -4}, {-33.3125, 2}, {-34, 2}}, color = {0, 0, 127}));
    connect(gain1.y, communication_helper1.u) annotation(Line(points = {{51, 36}, {58, 36}}, color = {0, 0, 127}));
    annotation(uses(Modelica(version = "3.2.1")), experiment(StartTime = 0, StopTime = 10, Tolerance = 1e-06, Interval = 0.005));
  end simulation;

  model communication_helper
    extends SHM_KEY;
    extends Modelica.Blocks.Icons.Block;
    Modelica.Blocks.Interfaces.RealOutput phi "Connector of Real output signal phi" annotation(Placement(transformation(extent = {{100, 70}, {120, 90}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput speedR "Connector of Real output signal speedR" annotation(Placement(transformation(extent = {{100, 30}, {120, 50}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput speedL "Connector of Real output signal speedL" annotation(Placement(transformation(extent = {{100, -10}, {120, 10}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput posR "Connector of Real output signal posR" annotation(Placement(transformation(extent = {{100, -50}, {120, -30}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealOutput posL "Connector of Real output signal posL" annotation(Placement(transformation(extent = {{100, -90}, {120, -70}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealInput u "Connector of Real input signal u" annotation(Placement(transformation(extent = {{-140, -20}, {-100, 20}}, rotation = 0)));
    Real varsArr[5];
    Real ErrorType(start = 0);
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

    function ComunicationInterface
      input Real u;
      input Modelica.SIunits.Time timesim;
      input Real arr[5];
      output Real ErrorVal;
    
      external  annotation(Library = "/home/fei_admin2/NetBeansProjects/OMCklient/testing/libgetref.o", Include = "#include \"/home/fei_admin2/NetBeansProjects/OMCklient/testing/Handle_data.h\"");
    end ComunicationInterface;
  equation
/*der(varsArr[1]) = 0;
  der(varsArr[2]) = 0;
  der(varsArr[3]) = 0;
  der(varsArr[4]) = 0;
  der(varsArr[5]) = 0;
   ErrorType=ComunicationInterface(u, time, varsArr);
    phi = varsArr[1];
    speedR = varsArr[2];
    speedL = varsArr[3];
    posR = varsArr[4];
    posL = varsArr[5];
    alphaR = posR * 360.0 / encoder_ticsperrotation;
    alphaL = posL * 360.0 / encoder_ticsperrotation;
    xR = posR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation;
    xL = posL * 2 * Modelica.Constants.pi * rL / encoder_ticsperrotation;
    omegaR = speedR * 360.0 / encoder_ticsperrotation;
    omegaL = speedL * 360.0 / encoder_ticsperrotation;
    vR = speedR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation;
    vL = speedL * 2 * Modelica.Constants.pi * rL / encoder_ti05csperrotation;*/
  algorithm
    ErrorType := noEvent(ComunicationInterface(u, time, varsArr));
    if noEvent(ErrorType > 100) then
      terminate("error or simulation stop");
    end if;
    phi := noEvent(varsArr[1]);
    speedR := noEvent(varsArr[2]);
    speedL := noEvent(varsArr[3]);
    posR := noEvent(varsArr[4]);
    posL := noEvent(varsArr[5]);
    alphaR := noEvent(posR * 360.0 / encoder_ticsperrotation);
    alphaL := noEvent(posL * 360.0 / encoder_ticsperrotation);
    xR := noEvent(posR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation);
    xL := noEvent(posL * 2 * Modelica.Constants.pi * rL / encoder_ticsperrotation);
    omegaR := noEvent(speedR * 360.0 / encoder_ticsperrotation);
    omegaL := noEvent(speedL * 360.0 / encoder_ticsperrotation);
    vR := noEvent(speedR * 2 * Modelica.Constants.pi * rR / encoder_ticsperrotation);
    vL := noEvent(speedL * 2 * Modelica.Constants.pi * rL / encoder_ticsperrotation);
    varsArr := noEvent(varsArr);
    annotation(Icon, Diagram, experiment(StartTime = 0, StopTime = 20, Tolerance = 1e-06, Interval = 0.01));
  end communication_helper;

  partial record SHM_KEY
    parameter Integer SHM_KEY_VALUE = 16000;
  end SHM_KEY;

  model simulation2
    Modelica.Blocks.Sources.Ramp ramp1 annotation(Placement(visible = true, transformation(origin = {-98, -32}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Fuzzy_Control_lib.toPercent toPercent1 annotation(Placement(visible = true, transformation(origin = {-47, 5}, extent = {{-7, -7}, {7, 7}}, rotation = 0)));
    Fuzzy_Control_lib.Version_2.inputs_v2.v2_input_5 v2_input_51 annotation(Placement(visible = true, transformation(origin = {-9, 13}, extent = {{-7, -7}, {7, 7}}, rotation = 0)));
    Fuzzy_Control_lib.Version_2.outputs_v2.v2_output_cos_5 v2_output_cos_51 annotation(Placement(visible = true, transformation(origin = {69, 7}, extent = {{-7, -7}, {7, 7}}, rotation = 0)));
    Fuzzy_Control_lib.Version_2.inputs_v2.v2_input_5 v2_input_53 annotation(Placement(visible = true, transformation(origin = {33, 35}, extent = {{7, -7}, {-7, 7}}, rotation = 0)));
    Modelica.Blocks.Sources.Constant const annotation(Placement(visible = true, transformation(origin = {32, 62}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Fuzzy_Control_lib.Version_2.rules_v2.v2_rules v2_rules1 annotation(Placement(visible = true, transformation(origin = {34, 8}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
    Fuzzy_Control_lib.fromPercent fromPercent1 annotation(Placement(visible = true, transformation(origin = {108, 0}, extent = {{-10, -10}, {10, 10}}, rotation = 0)));
  equation
    connect(const.y, v2_input_53.u) annotation(Line(points = {{44, 62}, {54, 62}, {54, 32}, {41, 32}, {41, 35}}, color = {0, 0, 127}));
    connect(toPercent1.y_sign, fromPercent1.u_sign) annotation(Line(points = {{-40, 0}, {16, 0}, {16, -8}, {96, -8}, {96, -8}}, color = {0, 0, 127}));
    connect(v2_output_cos_51.y, fromPercent1.u_plus) annotation(Line(points = {{76, 8}, {94, 8}, {94, 8}, {96, 8}}, color = {0, 0, 127}));
    connect(v2_rules1.Out1, v2_output_cos_51.u) annotation(Line(points = {{46, 8}, {61, 8}, {61, 7}}, color = {0, 0, 127}));
    connect(v2_input_51.y, v2_rules1.In1) annotation(Line(points = {{-2, 14}, {12, 14}, {12, 16}, {22, 16}, {22, 16}}, color = {0, 0, 127}));
    connect(v2_input_53.y, v2_rules1.In2) annotation(Line(points = {{26, 36}, {10, 36}, {10, 12}, {22, 12}, {22, 14}}, color = {0, 0, 127}));
    connect(toPercent1.y_plus, v2_input_51.u) annotation(Line(points = {{-39, 10}, {-39, 15.5}, {-17, 15.5}, {-17, 13}}, color = {0, 0, 127}));
    connect(ramp1.y, toPercent1.u) annotation(Line(points = {{-86, -32}, {-68, -32}, {-68, 4}, {-56, 4}, {-56, 6}}, color = {0, 0, 127}));
    annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), uses(Modelica(version = "3.2.1")));
  end simulation2;

  model nonlinearcompensator
    extends Modelica.Blocks.Interfaces.SISO;
  equation
    if u > 5 then
      y = u * 5;
    elseif u < (-5) then
      y = u * 5;
    else
      y = 0;
    end if;
    annotation(experiment(StartTime = 0, StopTime = 20, Tolerance = 1e-06, Interval = 0.01));
  end nonlinearcompensator;

  block LimPID "P, PI, PD, and PID controller with limited output, anti-windup compensation and setpoint weighting"
    import Modelica.Blocks.Types.InitPID;
    import Modelica.Blocks.Types.Init;
    import Modelica.Blocks.Types.SimpleController;
    extends Modelica.Blocks.Interfaces.SVcontrol;
    output Real controlError = u_s - u_m "Control error (set point - measurement)";
    parameter .Modelica.Blocks.Types.SimpleController controllerType = .Modelica.Blocks.Types.SimpleController.PID "Type of controller";
    parameter Real k(min = 0, unit = "1") = 1 "Gain of controller";
    parameter Modelica.SIunits.Time Ti(min = Modelica.Constants.small) = 0.5 "Time constant of Integrator block" annotation(Dialog(enable = controllerType == .Modelica.Blocks.Types.SimpleController.PI or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Modelica.SIunits.Time Td = 0.1 "Time constant of Derivative block" annotation(Dialog(enable = controllerType == .Modelica.Blocks.Types.SimpleController.PD or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Real yMax(start = 1) "Upper limit of output";
    parameter Real yMin = -yMax "Lower limit of output";
    parameter Real wp(min = 0) = 1 "Set-point weight for Proportional block (0..1)";
    parameter Real wd(min = 0) = 0 "Set-point weight for Derivative block (0..1)" annotation(Dialog(enable = controllerType == .Modelica.Blocks.Types.SimpleController.PD or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Real Ni(min = 100 * Modelica.Constants.eps) = 0.9 "Ni*Ti is time constant of anti-windup compensation" annotation(Dialog(enable = controllerType == .Modelica.Blocks.Types.SimpleController.PI or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Real Nd(min = 100 * Modelica.Constants.eps) = 10 "The higher Nd, the more ideal the derivative block" annotation(Dialog(enable = controllerType == .Modelica.Blocks.Types.SimpleController.PD or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter .Modelica.Blocks.Types.InitPID initType = .Modelica.Blocks.Types.InitPID.DoNotUse_InitialIntegratorState "Type of initialization (1: no init, 2: steady state, 3: initial state, 4: initial output)" annotation(Evaluate = true, Dialog(group = "Initialization"));
    parameter Boolean limitsAtInit = true "= false, if limits are ignored during initialization" annotation(Evaluate = true, Dialog(group = "Initialization"));
    parameter Real xi_start = 0 "Initial or guess value value for integrator output (= integrator state)" annotation(Dialog(group = "Initialization", enable = controllerType == .Modelica.Blocks.Types.SimpleController.PI or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Real xd_start = 0 "Initial or guess value for state of derivative block" annotation(Dialog(group = "Initialization", enable = controllerType == .Modelica.Blocks.Types.SimpleController.PD or controllerType == .Modelica.Blocks.Types.SimpleController.PID));
    parameter Real y_start = 0 "Initial value of output" annotation(Dialog(enable = initType == .Modelica.Blocks.Types.InitPID.InitialOutput, group = "Initialization"));
    parameter Boolean strict = false "= true, if strict limits with noEvent(..)" annotation(Evaluate = true, choices(checkBox = true), Dialog(tab = "Advanced"));
    constant Modelica.SIunits.Time unitTime = 1 annotation(HideResult = true);
    Modelica.Blocks.Math.Add addP(k1 = wp, k2 = -1) annotation(Placement(transformation(extent = {{-80, 40}, {-60, 60}}, rotation = 0)));
    Modelica.Blocks.Math.Add addD(k1 = wd, k2 = -1) if with_D annotation(Placement(transformation(extent = {{-80, -10}, {-60, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Gain P(k = 1) annotation(Placement(transformation(extent = {{-40, 40}, {-20, 60}}, rotation = 0)));
    Modelica.Blocks.Continuous.Integrator I(k = unitTime / Ti, y_start = xi_start, initType = if initType == InitPID.SteadyState then Init.SteadyState else if initType == InitPID.InitialState or initType == InitPID.DoNotUse_InitialIntegratorState then Init.InitialState else Init.NoInit) if with_I annotation(Placement(transformation(extent = {{-40, -60}, {-20, -40}}, rotation = 0)));
    Modelica.Blocks.Continuous.Derivative D(k = Td / unitTime, T = max([Td / Nd, 1.e-14]), x_start = xd_start, initType = if initType == InitPID.SteadyState or initType == InitPID.InitialOutput then Init.SteadyState else if initType == InitPID.InitialState then Init.InitialState else Init.NoInit) if with_D annotation(Placement(transformation(extent = {{-40, -10}, {-20, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Gain gainPID(k = k) annotation(Placement(transformation(extent = {{30, -10}, {50, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Add3 addPID(k2 = -1) annotation(Placement(transformation(extent = {{0, -10}, {20, 10}}, rotation = 0)));
    Modelica.Blocks.Math.Add3 addI(k2 = -1) if with_I annotation(Placement(transformation(extent = {{-80, -60}, {-60, -40}}, rotation = 0)));
    Modelica.Blocks.Math.Add addSat(k1 = +1, k2 = -1) if with_I annotation(Placement(transformation(origin = {80, -50}, extent = {{-10, -10}, {10, 10}}, rotation = 270)));
    Modelica.Blocks.Math.Gain gainTrack(k = 1 / (k * Ni)) if with_I annotation(Placement(transformation(extent = {{40, -80}, {20, -60}}, rotation = 0)));
    Modelica.Blocks.Nonlinear.Limiter limiter(uMax = yMax, uMin = yMin, strict = strict, limitsAtInit = limitsAtInit) annotation(Placement(transformation(extent = {{70, -10}, {90, 10}}, rotation = 0)));
  protected
    parameter Boolean with_I = controllerType == SimpleController.PI or controllerType == SimpleController.PID annotation(Evaluate = true, HideResult = true);
    parameter Boolean with_D = controllerType == SimpleController.PD or controllerType == SimpleController.PID annotation(Evaluate = true, HideResult = true);
  public
    Modelica.Blocks.Sources.Constant Dzero(k = 0) if not with_D annotation(Placement(transformation(extent = {{-30, 20}, {-20, 30}}, rotation = 0)));
    Modelica.Blocks.Sources.Constant Izero(k = 0) if not with_I annotation(Placement(transformation(extent = {{10, -55}, {0, -45}}, rotation = 0)));
  initial equation
    if initType == InitPID.InitialOutput then
      gainPID.y = y_start;
    end if;
  equation
    assert(yMax >= yMin, "LimPID: Limits must be consistent. However, yMax (=" + String(yMax) + ") < yMin (=" + String(yMin) + ")");
    if initType == InitPID.InitialOutput and (y_start < yMin or y_start > yMax) then
      Modelica.Utilities.Streams.error("LimPID: Start value y_start (=" + String(y_start) + ") is outside of the limits of yMin (=" + String(yMin) + ") and yMax (=" + String(yMax) + ")");
    end if;
    assert(limitsAtInit or not limitsAtInit and y >= yMin and y <= yMax, "LimPID: During initialization the limits have been switched off.\n" + "After initialization, the output y (=" + String(y) + ") is outside of the limits of yMin (=" + String(yMin) + ") and yMax (=" + String(yMax) + ")");
    connect(u_s, addP.u1) annotation(Line(points = {{-120, 0}, {-96, 0}, {-96, 56}, {-82, 56}}, color = {0, 0, 127}));
    connect(u_s, addD.u1) annotation(Line(points = {{-120, 0}, {-96, 0}, {-96, 6}, {-82, 6}}, color = {0, 0, 127}));
    connect(u_s, addI.u1) annotation(Line(points = {{-120, 0}, {-96, 0}, {-96, -42}, {-82, -42}}, color = {0, 0, 127}));
    connect(addP.y, P.u) annotation(Line(points = {{-59, 50}, {-42, 50}}, color = {0, 0, 127}));
    connect(addD.y, D.u) annotation(Line(points = {{-59, 0}, {-42, 0}}, color = {0, 0, 127}));
    connect(addI.y, I.u) annotation(Line(points = {{-59, -50}, {-42, -50}}, color = {0, 0, 127}));
    connect(P.y, addPID.u1) annotation(Line(points = {{-19, 50}, {-10, 50}, {-10, 8}, {-2, 8}}, color = {0, 0, 127}));
    connect(D.y, addPID.u2) annotation(Line(points = {{-19, 0}, {-2, 0}}, color = {0, 0, 127}));
    connect(I.y, addPID.u3) annotation(Line(points = {{-19, -50}, {-10, -50}, {-10, -8}, {-2, -8}}, color = {0, 0, 127}));
    connect(addPID.y, gainPID.u) annotation(Line(points = {{21, 0}, {28, 0}}, color = {0, 0, 127}));
    connect(gainPID.y, addSat.u2) annotation(Line(points = {{51, 0}, {60, 0}, {60, -20}, {74, -20}, {74, -38}}, color = {0, 0, 127}));
    connect(gainPID.y, limiter.u) annotation(Line(points = {{51, 0}, {68, 0}}, color = {0, 0, 127}));
    connect(limiter.y, addSat.u1) annotation(Line(points = {{91, 0}, {94, 0}, {94, -20}, {86, -20}, {86, -38}}, color = {0, 0, 127}));
    connect(limiter.y, y) annotation(Line(points = {{91, 0}, {110, 0}}, color = {0, 0, 127}));
    connect(addSat.y, gainTrack.u) annotation(Line(points = {{80, -61}, {80, -70}, {42, -70}}, color = {0, 0, 127}));
    connect(gainTrack.y, addI.u3) annotation(Line(points = {{19, -70}, {-88, -70}, {-88, -58}, {-82, -58}}, color = {0, 0, 127}));
    connect(u_m, addP.u2) annotation(Line(points = {{0, -120}, {0, -92}, {-92, -92}, {-92, 44}, {-82, 44}}, color = {0, 0, 127}, thickness = 0.5));
    connect(u_m, addD.u2) annotation(Line(points = {{0, -120}, {0, -92}, {-92, -92}, {-92, -6}, {-82, -6}}, color = {0, 0, 127}, thickness = 0.5));
    connect(u_m, addI.u2) annotation(Line(points = {{0, -120}, {0, -92}, {-92, -92}, {-92, -50}, {-82, -50}}, color = {0, 0, 127}, thickness = 0.5));
    connect(Dzero.y, addPID.u2) annotation(Line(points = {{-19.5, 25}, {-14, 25}, {-14, 0}, {-2, 0}}, color = {0, 0, 127}));
    connect(Izero.y, addPID.u3) annotation(Line(points = {{-0.5, -50}, {-10, -50}, {-10, -8}, {-2, -8}}, color = {0, 0, 127}));
    annotation(defaultComponentName = "PID", Icon(coordinateSystem(preserveAspectRatio = true, extent = {{-100, -100}, {100, 100}}), graphics = {Line(points = {{-80, 78}, {-80, -90}}, color = {192, 192, 192}), Polygon(points = {{-80, 90}, {-88, 68}, {-72, 68}, {-80, 90}}, lineColor = {192, 192, 192}, fillColor = {192, 192, 192}, fillPattern = FillPattern.Solid), Line(points = {{-90, -80}, {82, -80}}, color = {192, 192, 192}), Polygon(points = {{90, -80}, {68, -72}, {68, -88}, {90, -80}}, lineColor = {192, 192, 192}, fillColor = {192, 192, 192}, fillPattern = FillPattern.Solid), Line(points = {{-80, -80}, {-80, -20}, {30, 60}, {80, 60}}, color = {0, 0, 127}), Text(extent = {{-20, -20}, {80, -60}}, lineColor = {192, 192, 192}, textString = "%controllerType"), Line(visible = strict, points = {{30, 60}, {81, 60}}, color = {255, 0, 0}, smooth = Smooth.None)}), Documentation(info = "<HTML>
<p>
Via parameter <b>controllerType</b> either <b>P</b>, <b>PI</b>, <b>PD</b>,
or <b>PID</b> can be selected. If, e.g., PI is selected, all components belonging to the
D-part are removed from the block (via conditional declarations).
The example model
<a href=\"modelica://Modelica.Blocks.Examples.PID_Controller\">Modelica.Blocks.Examples.PID_Controller</a>
demonstrates the usage of this controller.
Several practical aspects of PID controller design are incorporated
according to chapter 3 of the book:
</p>

<dl>
<dt>&Aring;str&ouml;m K.J., and H&auml;gglund T.:</dt>
<dd> <b>PID Controllers: Theory, Design, and Tuning</b>.
     Instrument Society of America, 2nd edition, 1995.
</dd>
</dl>

<p>
Besides the additive <b>proportional, integral</b> and <b>derivative</b>
part of this controller, the following features are present:
</p>
<ul>
<li> The output of this controller is limited. If the controller is
     in its limits, anti-windup compensation is activated to drive
     the integrator state to zero. </li>
<li> The high-frequency gain of the derivative part is limited
     to avoid excessive amplification of measurement noise.</li>
<li> Setpoint weighting is present, which allows to weight
     the setpoint in the proportional and the derivative part
     independently from the measurement. The controller will respond
     to load disturbances and measurement noise independently of this setting
     (parameters wp, wd). However, setpoint changes will depend on this
     setting. For example, it is useful to set the setpoint weight wd
     for the derivative part to zero, if steps may occur in the
     setpoint signal.</li>
</ul>

<p>
The parameters of the controller can be manually adjusted by performing
simulations of the closed loop system (= controller + plant connected
together) and using the following strategy:
</p>

<ol>
<li> Set very large limits, e.g., yMax = Modelica.Constants.inf</li>
<li> Select a <b>P</b>-controller and manually enlarge parameter <b>k</b>
     (the total gain of the controller) until the closed-loop response
     cannot be improved any more.</li>
<li> Select a <b>PI</b>-controller and manually adjust parameters
     <b>k</b> and <b>Ti</b> (the time constant of the integrator).
     The first value of Ti can be selected, such that it is in the
     order of the time constant of the oscillations occurring with
     the P-controller. If, e.g., vibrations in the order of T=10 ms
     occur in the previous step, start with Ti=0.01 s.</li>
<li> If you want to make the reaction of the control loop faster
     (but probably less robust against disturbances and measurement noise)
     select a <b>PID</b>-Controller and manually adjust parameters
     <b>k</b>, <b>Ti</b>, <b>Td</b> (time constant of derivative block).</li>
<li> Set the limits yMax and yMin according to your specification.</li>
<li> Perform simulations such that the output of the PID controller
     goes in its limits. Tune <b>Ni</b> (Ni*Ti is the time constant of
     the anti-windup compensation) such that the input to the limiter
     block (= limiter.u) goes quickly enough back to its limits.
     If Ni is decreased, this happens faster. If Ni=infinity, the
     anti-windup compensation is switched off and the controller works bad.</li>
</ol>

<p>
<b>Initialization</b>
</p>

<p>
This block can be initialized in different
ways controlled by parameter <b>initType</b>. The possible
values of initType are defined in
<a href=\"modelica://Modelica.Blocks.Types.InitPID\">Modelica.Blocks.Types.InitPID</a>.
This type is identical to
<a href=\"modelica://Modelica.Blocks.Types.Init\">Types.Init</a>,
with the only exception that the additional option
<b>DoNotUse_InitialIntegratorState</b> is added for
backward compatibility reasons (= integrator is initialized with
InitialState whereas differential part is initialized with
NoInit which was the initialization in version 2.2 of the Modelica
standard library).
</p>

<p>
Based on the setting of initType, the integrator (I) and derivative (D)
blocks inside the PID controller are initialized according to the following table:
</p>

<table border=1 cellspacing=0 cellpadding=2>
  <tr><td valign=\"top\"><b>initType</b></td>
      <td valign=\"top\"><b>I.initType</b></td>
      <td valign=\"top\"><b>D.initType</b></td></tr>

  <tr><td valign=\"top\"><b>NoInit</b></td>
      <td valign=\"top\">NoInit</td>
      <td valign=\"top\">NoInit</td></tr>

  <tr><td valign=\"top\"><b>SteadyState</b></td>
      <td valign=\"top\">SteadyState</td>
      <td valign=\"top\">SteadyState</td></tr>

  <tr><td valign=\"top\"><b>InitialState</b></td>
      <td valign=\"top\">InitialState</td>
      <td valign=\"top\">InitialState</td></tr>

  <tr><td valign=\"top\"><b>InitialOutput</b><br>
          and initial equation: y = y_start</td>
      <td valign=\"top\">NoInit</td>
      <td valign=\"top\">SteadyState</td></tr>

  <tr><td valign=\"top\"><b>DoNotUse_InitialIntegratorState</b></td>
      <td valign=\"top\">InitialState</td>
      <td valign=\"top\">NoInit</td></tr>
</table>

<p>
In many cases, the most useful initial condition is
<b>SteadyState</b> because initial transients are then no longer
present. If initType = InitPID.SteadyState, then in some
cases difficulties might occur. The reason is the
equation of the integrator:
</p>

<pre>
   <b>der</b>(y) = k*u;
</pre>

<p>
The steady state equation \"der(x)=0\" leads to the condition that the input u to the
integrator is zero. If the input u is already (directly or indirectly) defined
by another initial condition, then the initialization problem is <b>singular</b>
(has none or infinitely many solutions). This situation occurs often
for mechanical systems, where, e.g., u = desiredSpeed - measuredSpeed and
since speed is both a state and a derivative, it is natural to
initialize it with zero. As sketched this is, however, not possible.
The solution is to not initialize u_m or the variable that is used
to compute u_m by an algebraic equation.
</p>

<p>
If parameter <b>limitAtInit</b> = <b>false</b>, the limits at the
output of this controller block are removed from the initialization problem which
leads to a much simpler equation system. After initialization has been
performed, it is checked via an assert whether the output is in the
defined limits. For backward compatibility reasons
<b>limitAtInit</b> = <b>true</b>. In most cases it is best
to use <b>limitAtInit</b> = <b>false</b>.
</p>
</html>"));
  end LimPID;

  model PIDselector
    extends Modelica.Blocks.Interfaces.SO;
    Modelica.Blocks.Interfaces.RealInput u1 "Connector of Real input signals 1" annotation(Placement(transformation(extent = {{-140, 50}, {-100, 90}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealInput u2 "Connector of Real input signals 2" annotation(Placement(transformation(extent = {{-140, -20}, {-100, 20}}, rotation = 0)));
    Modelica.Blocks.Interfaces.RealInput phi "Connector of Real input signals 3" annotation(Placement(transformation(extent = {{-140, -90}, {-100, -50}}, rotation = 0)));
    /* if phi == (-100.89) then
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        y = 109; else*/
  equation
    if phi < (-111) then
      y = 109;
  /*  elseif time > 9.95 then
      y = 0;*/
    elseif phi < 3.5 and phi > (-3.5) then
      y = u1;
    else
      y = u2;
//u2;
    end if;
    annotation(experiment(StartTime = 0, StopTime = 20, Tolerance = 1e-06, Interval = 0.01));
  end PIDselector;
  annotation(Icon(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})), Diagram(coordinateSystem(extent = {{-100, -100}, {100, 100}}, preserveAspectRatio = true, initialScale = 0.1, grid = {2, 2})));
end Segway;
