function tos10(block)
%MSFUNTMPL_BASIC A template for a Leve-2 M-file S-function
%   The M-file S-function is written as a MATLAB function with the
%   same name as the S-function. Replace 'msfuntmpl_basic' with the 
%   name of your S-function.
%
%   It should be noted that the M-file S-function is very similar
%   to Level-2 C-Mex S-functions. You should be able to get more
%   information for each of the block methods by referring to the
%   documentation for C-Mex S-functions.
%
%   Copyright 2003-2009 The MathWorks, Inc.

% define instance variables
%% ---------------------------------------------------------
comPort = '';
baudRate = '';

%%
%% The setup method is used to setup the basic attributes of the
%% S-function such as ports, parameters, etc. Do not add any other
%% calls to the main body of the function.
%%
setup(block);

%endfunction

%% Function: setup ===================================================
%% Abstract:
%%   Set up the S-function block's basic characteristics such as:
%%   - Input ports
%%   - Output ports
%%   - Dialog parameters
%%   - Options
%%
%%   Required         : Yes
%%   C-Mex counterpart: mdlInitializeSizes
%%
function setup(block)

% Register number of ports
block.NumInputPorts  = 3;
block.NumOutputPorts = 7;

% Setup port properties to be inherited or dynamic
block.SetPreCompInpPortInfoToDynamic;
block.SetPreCompOutPortInfoToDynamic;

% Register the parameters.
block.NumDialogPrms     = 6; % scale factor
block.DialogPrmsTunable = {'Nontunable', 'Nontunable', 'Nontunable', 'Nontunable', 'Nontunable', 'Nontunable'};

% Register sample times
%  [0 offset]            : Continuous sample time
%  [positive_num offset] : Discrete sample time
%
%  [-1, 0]               : Inherited sample time
%  [-2, 0]               : Variable sample time
block.SampleTimes = [block.DialogPrm(1).Data 0];

% Specify the block simStateCompliance. The allowed values are:
%    'UnknownSimState', < The default setting; warn and assume DefaultSimState
%    'DefaultSimState', < Same sim state as a built-in block
%    'HasNoSimState',   < No sim state
%    'CustomSimState',  < Has GetSimState and SetSimState methods
%    'DisallowSimState' < Error out when saving or restoring the model sim state
block.SimStateCompliance = 'DefaultSimState';

%% -----------------------------------------------------------------
%% The M-file S-function uses an internal registry for all
%% block methods. You should register all relevant methods
%% (optional and required) as illustrated below. You may choose
%% any suitable name for the methods and implement these methods
%% as local functions within the same file. See comments
%% provided for each function for more information.
%% -----------------------------------------------------------------

block.RegBlockMethod('CheckParameters', @CheckPrms); % methods called during update diagram/compilation.
block.RegBlockMethod('SetInputPortSamplingMode',@SetInputPortSamplingMode);
block.RegBlockMethod('PostPropagationSetup',    @DoPostPropSetup);
block.RegBlockMethod('InitializeConditions', @InitializeConditions);
block.RegBlockMethod('Start', @Start);
block.RegBlockMethod('Outputs', @Outputs);     % Required
block.RegBlockMethod('Update', @Update);
block.RegBlockMethod('Derivatives', @Derivatives);
block.RegBlockMethod('Terminate', @Terminate); % Required

function CheckPrms(block)
    try
        validateattributes(block.DialogPrm(1).Data, {'numeric'}, {'real', 'scalar', 'nonzero'}); % sample time
        validateattributes(block.DialogPrm(2).Data, {'char'}, {'nonempty'}); % serial port
        validateattributes(block.DialogPrm(3).Data, {'numeric'}, {'integer', 'scalar', 'nonzero'}); % baudrate
        validateattributes(block.DialogPrm(4).Data, {'numeric'}, {'real', 'scalar', 'nonzero'}); % temp filter constatnt
        validateattributes(block.DialogPrm(5).Data, {'numeric'}, {'real', 'scalar', 'nonzero'}); % light filter constatnt
        validateattributes(block.DialogPrm(6).Data, {'numeric'}, {'real', 'scalar', 'nonzero'}); % fan filter constatnt
    catch %#ok<CTCH>
        throw(MSLException(block.BlockHandle, ...
            'Simulink:Parameters:BlkParamUndefined', ...
            'Invalid parameters for TOS block'));
    end        
%end


%%
%% PostPropagationSetup:
%%   Functionality    : Setup work areas and state variables. Can
%%                      also register run-time methods here
%%   Required         : No
%%   C-Mex counterpart: mdlSetWorkWidths
%%
function DoPostPropSetup(block)
    if block.SampleTimes(1) == 0
        throw(MSLException(block.BlockHandle, ...
              'The TOS library blocks can only handle discrete sample times'));
    end
    
%%
%% InitializeConditions:
%%   Functionality    : Called at the start of simulation and if it is 
%%                      present in an enabled subsystem configured to reset 
%%                      states, it will be called when the enabled subsystem
%%                      restarts execution to reset the states.
%%   Required         : No
%%   C-MEX counterpart: mdlInitializeConditions
%%
function InitializeConditions(block)
    block.OutputPort(1).Data = 0;
    block.OutputPort(2).Data = 0;
    block.OutputPort(3).Data = 0;
    block.OutputPort(4).Data = 0;
    block.OutputPort(5).Data = 0;
    block.OutputPort(6).Data = 0;
    block.OutputPort(7).Data = 0;
%end InitializeConditions


%%
%% Start:
%%   Functionality    : Called once at start of model execution. If you
%%                      have states that should be initialized once, this 
%%                      is the place to do it.
%%   Required         : No
%%   C-MEX counterpart: mdlStart
%%
function Start(block)
    global s;

    comPort = block.DialogPrm(2).Data;
    baudRate = block.DialogPrm(3).Data;

    % check param for com port
    if ~ischar(comPort),
        error('Error: The input argument for COM port must be a string, e.g. ''COM8'' or ''/dev/ttyUSB0'' ');
    end
    
    % check port
    if exist('instrhwinfo') == 2
      serialInfo = instrhwinfo('serial');
      if isempty(find(strcmp(comPort, serialInfo.AvailableSerialPorts)))
        error(['Error: port ''' comPort ''' is not available!']);
        %error(['Try one of these: ' strjoin(serialInfo.AvailableSerialPorts, ', ')]);
      end
    end

    % check if we are already connected
    if isa(s, 'serial') && isvalid(s) && strcmpi(get(s,'Status'),'open'),
        disp(['It looks like TOS is already connected to port ' comPort ]);
        disp('Delete the object to force disconnection');
        disp('before attempting a connection to a different port.');
    end

    % check whether serial port is currently used by MATLAB
    if ~isempty(instrfind({'Port'},{comPort})),
        disp(['The port ' comPort ' is already used by MATLAB']);
        disp(['If you are sure that TOS is connected to ' comPort]);
        disp('then delete the object, execute:');
        disp(['  delete(instrfind({''Port''},{''' comPort '''}))']);
        disp('to delete the port, disconnect the cable, reconnect it,');
        error(['Error: Port ' comPort ' already used by MATLAB']);
    end
    
%    s=serial(comPort, 'BaudRate', baudRate, 'Timeout', 1);
    s = serial(comPort); %assigns the object s to serial port
    set(s, 'InputBufferSize', 512); %number of bytes in inout buffer
    %set(s, 'FlowControl', 'hardware');
    set(s, 'BaudRate', baudRate);
    set(s, 'Parity', 'none');
    set(s, 'DataBits', 8);
    set(s, 'StopBit', 1);
    set(s, 'Timeout',10);
    %set(s, 'Terminator', '');

    % open port
    try
        fopen(s);
    catch
        throw(MSLException(block.BlockHandle, ...
            'Simulink:Parameters:BlkParamUndefined', ...
            'Could not open port!\nCheck, if the settings of the COM port and baud rate are correct.'));

        error(['Error: Could not open port: ' comPort]);
        delete(s);
    end

   % it takes several seconds before any operation could be attempted
    fprintf(1,'Attempting connection .');
    for i=1:3,
        pause(1);
        fprintf(1,'.');
    end
    fprintf(1,'\n');
    
    % SEND CMD ------------------------------------------------------------
    % Posleme parametre filtrov (teplota, svetlo, ventilator)
    fprintf(1,'Sending filters parametrers ...\n');
    msg = sprintf('SFP,%3.3f,%3.3f,%3.3f', block.DialogPrm(4).Data, block.DialogPrm(5).Data, block.DialogPrm(6).Data);

    crc = 0;
    for n=1:length(msg)
      crc = bitxor(crc,uint8(msg(n)));
    end
    crchex = dec2hex(crc);

    cmd = sprintf('$%s*%s\n', msg, crchex);
    try
        fprintf(s, '%s', cmd, 'sync');
    catch err,
        error(['Error: Unable to send data']);
        error(['err: ' err]);
        fclose(s);
        delete(s);
    end
    % SEND CMD ------------------------------------------------------------
    
    % SEND CMD ------------------------------------------------------------
    % Posleme prikaz na spustenie experimentu
    fprintf(1,'Starting Up experiment ...\n');
    msg = 'SSE';

    crc = 0;
    for n=1:length(msg)
      crc = bitxor(crc,uint8(msg(n)));
    end
    crchex = dec2hex(crc);

    cmd = sprintf('$%s*%s\n', msg, crchex);
    try
        fprintf(s, '%s', cmd, 'sync');
    catch err,
        error(['Error: Unable to send data']);
        error(['err: ' err]);
        fclose(s);
        delete(s);
    end
    % SEND CMD ------------------------------------------------------------
%end function

function SetInputPortSamplingMode(block, idx, fd)
  block.InputPort(idx).SamplingMode = fd;

  block.OutputPort(1).SamplingMode = fd;
  block.OutputPort(2).SamplingMode = fd;
  block.OutputPort(3).SamplingMode = fd;
  block.OutputPort(4).SamplingMode = fd;
  block.OutputPort(5).SamplingMode = fd;
  block.OutputPort(6).SamplingMode = fd;
  block.OutputPort(7).SamplingMode = fd;

%%
%% Outputs:
%%   Functionality    : Called to generate block outputs in
%%                      simulation step
%%   Required         : Yes
%%   C-MEX counterpart: mdlOutputs
%%
function Outputs(block)
    global s;

    % SEND CMD ------------------------------------------------------------
    % Posleme hodnoty vykonu (ziarovka, ventilator, led)
    msg = sprintf('SGV,%3.2f,%3.2f,%3.2f', block.InputPort(1).Data, block.InputPort(2).Data, block.InputPort(3).Data);

    crc = 0;
    for n=1:length(msg)
      crc = bitxor(crc,uint8(msg(n)));
    end
    crchex = dec2hex(crc);

    cmd = sprintf('$%s*%s\n', msg, crchex);
    try
        fprintf(s, '%s', cmd, 'sync');
        fid = fopen('/home/vagrant/api/files/matlab.txt','w+');
        fclose(fid);
    catch err,
        error(['Error: Unable to send data']);
        error(['err: ' err]);
        fclose(s);
        delete(s);
    end
    % SEND CMD ------------------------------------------------------------

    % READ VAL ------------------------------------------------------------
    values = '';
    try
        values = strcat(values, fscanf(s,'%s'));
        fid = fopen('/home/vagrant/api/files/matlab.txt','a+');
        fprintf(fid,'%s',values);
        fclose(fid);
    catch err
        error(['Error: Unable to read data']);
        error(['err: ' err]);
        fclose(s);
        delete(s);
    end
    % READ VAL ------------------------------------------------------------

    % PARSE VAL -----------------------------------------------------------
    dolar = strfind(values, '$');
    hviezda = strfind(values, '*');

    body = values((dolar+1):(hviezda-1));
    checksum = values((hviezda+1):end);

    intchecksum = hex2dec(checksum);
    % PARSE VAL -----------------------------------------------------------

    % CEHCKSUM ------------------------------------------------------------
    crc = 0;
    for n=1:length(body)
      crc = bitxor(crc,uint8(body(n)));
    end
    % CEHCKSUM ------------------------------------------------------------

    if (crc == intchecksum)
      % TEPLOTA DPS, filtrovana TEPLOTA, derivacia TEPLOTA, filtrovana SVETELNA INT., derivacia SVETELNEJ INT., filtrovane OTACKY, derivacia OTACOK
      val = sscanf(body, '%g,%g,%g,%g,%g,%g,%g');
      block.OutputPort(1).Data = val(1);
      block.OutputPort(2).Data = val(2);
      block.OutputPort(3).Data = val(3);
      block.OutputPort(4).Data = val(4);
      block.OutputPort(5).Data = val(5);
      block.OutputPort(6).Data = val(6);
      block.OutputPort(7).Data = val(7);
    end
%end Outputs

%%
%% Update:
%%   Functionality    : Called to update discrete states
%%                      during simulation step
%%   Required         : No
%%   C-MEX counterpart: mdlUpdate
%%
function Update(block)
    %block.Dwork(1).Data = block.InputPort(1).Data;
%end Update

%%
%% Derivatives:
%%   Functionality    : Called to update derivatives of
%%                      continuous states during simulation step
%%   Required         : No
%%   C-MEX counterpart: mdlDerivatives
%%
function Derivatives(block)

%end Derivatives

%%
%% Terminate:
%%   Functionality    : Called at the end of simulation for cleanup
%%   Required         : Yes
%%   C-MEX counterpart: mdlTerminate
%%
function Terminate(block)
    global s;

    % SEND CMD ------------------------------------------------------------
    % posleme prikaz na ukoncenie experimentu
    fprintf(1,'End of the experiment ...\n');

    crcoff = 0;
    crcoffhex = '';

    off = 'SEE';
    for n=1:length(off)
      crcoff = bitxor(crcoff,uint8(off(n)));
    end
    crcoffhex = dec2hex(crcoff);

    cmdoff = sprintf('$%s*%s\n', off, crcoffhex);
    fprintf(s, '%s', cmdoff, 'sync');
    % SEND CMD ------------------------------------------------------------

    fclose(s);
    delete(s);
%end Terminate
 
