/* Include files */

#include "blascompat32.h"
#include "thermo_sfun.h"
#include "c6_thermo.h"
#define CHARTINSTANCE_CHARTNUMBER      (chartInstance.chartNumber)
#define CHARTINSTANCE_INSTANCENUMBER   (chartInstance.instanceNumber)
#include "thermo_sfun_debug_macros.h"

/* Type Definitions */

/* Named Constants */
#define c6_IN_NO_ACTIVE_CHILD          (0)

/* Variable Declarations */

/* Variable Definitions */
static SFc6_thermoInstanceStruct chartInstance;

/* Function Declarations */
static void initialize_c6_thermo(void);
static void initialize_params_c6_thermo(void);
static void enable_c6_thermo(void);
static void disable_c6_thermo(void);
static void c6_update_debugger_state_c6_thermo(void);
static const mxArray *get_sim_state_c6_thermo(void);
static void set_sim_state_c6_thermo(const mxArray *c6_st);
static void finalize_c6_thermo(void);
static void sf_c6_thermo(void);
static void init_script_number_translation(uint32_T c6_machineNumber, uint32_T
  c6_chartNumber);
static const mxArray *c6_sf_marshall(void *c6_chartInstance, void *c6_u);
static const mxArray *c6_b_sf_marshall(void *c6_chartInstance, void *c6_u);
static void init_dsm_address_info(void);

/* Function Definitions */
static void initialize_c6_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
  chartInstance.c6_is_active_c6_thermo = 0U;
}

static void initialize_params_c6_thermo(void)
{
}

static void enable_c6_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
}

static void disable_c6_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
}

static void c6_update_debugger_state_c6_thermo(void)
{
}

static const mxArray *get_sim_state_c6_thermo(void)
{
  const mxArray *c6_st = NULL;
  const mxArray *c6_y = NULL;
  uint8_T c6_u;
  const mxArray *c6_b_y = NULL;
  c6_st = NULL;
  c6_y = NULL;
  sf_mex_assign(&c6_y, sf_mex_createcellarray(1));
  c6_u = chartInstance.c6_is_active_c6_thermo;
  c6_b_y = NULL;
  sf_mex_assign(&c6_b_y, sf_mex_create("y", &c6_u, 3, 0U, 0U, 0U, 0));
  sf_mex_setcell(c6_y, 0, c6_b_y);
  sf_mex_assign(&c6_st, c6_y);
  return c6_st;
}

static void set_sim_state_c6_thermo(const mxArray *c6_st)
{
  const mxArray *c6_u;
  const mxArray *c6_b_is_active_c6_thermo;
  uint8_T c6_u0;
  uint8_T c6_y;
  chartInstance.c6_doneDoubleBufferReInit = true;
  c6_u = sf_mex_dup(c6_st);
  c6_b_is_active_c6_thermo = sf_mex_dup(sf_mex_getcell(c6_u, 0));
  sf_mex_import("is_active_c6_thermo", sf_mex_dup(c6_b_is_active_c6_thermo),
                &c6_u0, 1, 3, 0U, 0, 0U, 0);
  c6_y = c6_u0;
  sf_mex_destroy(&c6_b_is_active_c6_thermo);
  chartInstance.c6_is_active_c6_thermo = c6_y;
  sf_mex_destroy(&c6_u);
  c6_update_debugger_state_c6_thermo();
  sf_mex_destroy(&c6_st);
}

static void finalize_c6_thermo(void)
{
}

static void sf_c6_thermo(void)
{
  int32_T c6_previousEvent;
  real_T c6_u;
  real_T c6_nargout = 0.0;
  real_T c6_nargin = 1.0;
  char_T c6_b_u;
  const mxArray *c6_y = NULL;
  real_T c6_c_u;
  const mxArray *c6_b_y = NULL;
  int32_T c6_i0;
  static char_T c6_cv0[14] = { 'f', 'l', 'i', 'g', 'h', 't', '(', 'e', 'n', 'd',
    '+', '1', ')', '=' };

  char_T c6_d_u[14];
  const mxArray *c6_c_y = NULL;
  int32_T c6_i1;
  static char_T c6_cv1[4] = { 'b', 'a', 's', 'e' };

  char_T c6_e_u[4];
  const mxArray *c6_d_y = NULL;
  real_T *c6_f_u;
  c6_f_u = (real_T *)ssGetInputPortSignal(chartInstance.S, 0);
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
  _SFD_CC_CALL(CHART_ENTER_SFUNCTION_TAG,5);
  _SFD_DATA_RANGE_CHECK(*c6_f_u, 0U);
  c6_previousEvent = _sfEvent_;
  _sfEvent_ = CALL_EVENT;
  _SFD_CC_CALL(CHART_ENTER_DURING_FUNCTION_TAG,5);
  c6_u = *c6_f_u;
  sf_debug_symbol_scope_push(3U, 0U);
  sf_debug_symbol_scope_add("nargout", &c6_nargout, c6_sf_marshall);
  sf_debug_symbol_scope_add("nargin", &c6_nargin, c6_sf_marshall);
  sf_debug_symbol_scope_add("u", &c6_u, c6_sf_marshall);
  CV_EML_FCN(0, 0);
  _SFD_EML_CALL(0,3);
  _SFD_EML_CALL(0,4);
  _SFD_EML_CALL(0,5);
  _SFD_EML_CALL(0,6);
  c6_b_u = ';';
  c6_y = NULL;
  sf_mex_assign(&c6_y, sf_mex_create("y", &c6_b_u, 10, 0U, 0U, 0U, 0));
  c6_c_u = c6_u;
  c6_b_y = NULL;
  sf_mex_assign(&c6_b_y, sf_mex_create("y", &c6_c_u, 0, 0U, 0U, 0U, 0));
  for (c6_i0 = 0; c6_i0 < 14; c6_i0 = c6_i0 + 1) {
    c6_d_u[c6_i0] = c6_cv0[c6_i0];
  }

  c6_c_y = NULL;
  sf_mex_assign(&c6_c_y, sf_mex_create("y", &c6_d_u, 10, 0U, 1U, 0U, 2, 1, 14));
  for (c6_i1 = 0; c6_i1 < 4; c6_i1 = c6_i1 + 1) {
    c6_e_u[c6_i1] = c6_cv1[c6_i1];
  }

  c6_d_y = NULL;
  sf_mex_assign(&c6_d_y, sf_mex_create("y", &c6_e_u, 10, 0U, 1U, 0U, 2, 1, 4));
  sf_mex_call_debug("evalin", 0U, 2U, 14, c6_d_y, 14, sf_mex_call_debug("strcat",
    1U, 3U, 14, c6_c_y, 14, sf_mex_call_debug("num2str"
    , 1U, 1U, 14, c6_b_y), 14, c6_y));
  _SFD_EML_CALL(0,-6);
  sf_debug_symbol_scope_pop();
  _SFD_CC_CALL(EXIT_OUT_OF_FUNCTION_TAG,5);
  _sfEvent_ = c6_previousEvent;
  sf_debug_check_for_state_inconsistency(_thermoMachineNumber_,
    chartInstance.chartNumber, chartInstance.instanceNumber);
}

static void init_script_number_translation(uint32_T c6_machineNumber, uint32_T
  c6_chartNumber)
{
}

static const mxArray *c6_sf_marshall(void *c6_chartInstance, void *c6_u)
{
  const mxArray *c6_y = NULL;
  real_T c6_b_u;
  const mxArray *c6_b_y = NULL;
  c6_y = NULL;
  c6_b_u = *((real_T *)c6_u);
  c6_b_y = NULL;
  sf_mex_assign(&c6_b_y, sf_mex_create("y", &c6_b_u, 0, 0U, 0U, 0U, 0));
  sf_mex_assign(&c6_y, c6_b_y);
  return c6_y;
}

const mxArray *sf_c6_thermo_get_eml_resolved_functions_info(void)
{
  const mxArray *c6_nameCaptureInfo = NULL;
  c6_nameCaptureInfo = NULL;
  sf_mex_assign(&c6_nameCaptureInfo, sf_mex_create("nameCaptureInfo", NULL, 0,
    0U, 1U, 0U, 2, 0, 1));
  return c6_nameCaptureInfo;
}

static const mxArray *c6_b_sf_marshall(void *c6_chartInstance, void *c6_u)
{
  const mxArray *c6_y = NULL;
  boolean_T c6_b_u;
  const mxArray *c6_b_y = NULL;
  c6_y = NULL;
  c6_b_u = *((boolean_T *)c6_u);
  c6_b_y = NULL;
  sf_mex_assign(&c6_b_y, sf_mex_create("y", &c6_b_u, 11, 0U, 0U, 0U, 0));
  sf_mex_assign(&c6_y, c6_b_y);
  return c6_y;
}

static void init_dsm_address_info(void)
{
}

/* SFunction Glue Code */
void sf_c6_thermo_get_check_sum(mxArray *plhs[])
{
  ((real_T *)mxGetPr((plhs[0])))[0] = (real_T)(4112200577U);
  ((real_T *)mxGetPr((plhs[0])))[1] = (real_T)(271565719U);
  ((real_T *)mxGetPr((plhs[0])))[2] = (real_T)(3409474084U);
  ((real_T *)mxGetPr((plhs[0])))[3] = (real_T)(3207912840U);
}

mxArray *sf_c6_thermo_get_autoinheritance_info(void)
{
  const char *autoinheritanceFields[] = { "checksum", "inputs", "parameters",
    "outputs" };

  mxArray *mxAutoinheritanceInfo = mxCreateStructMatrix(1,1,4,
    autoinheritanceFields);

  {
    mxArray *mxChecksum = mxCreateDoubleMatrix(4,1,mxREAL);
    double *pr = mxGetPr(mxChecksum);
    pr[0] = (double)(2255067544U);
    pr[1] = (double)(46653977U);
    pr[2] = (double)(760923579U);
    pr[3] = (double)(4232754166U);
    mxSetField(mxAutoinheritanceInfo,0,"checksum",mxChecksum);
  }

  {
    const char *dataFields[] = { "size", "type", "complexity" };

    mxArray *mxData = mxCreateStructMatrix(1,1,3,dataFields);

    {
      mxArray *mxSize = mxCreateDoubleMatrix(1,2,mxREAL);
      double *pr = mxGetPr(mxSize);
      pr[0] = (double)(1);
      pr[1] = (double)(1);
      mxSetField(mxData,0,"size",mxSize);
    }

    {
      const char *typeFields[] = { "base", "fixpt" };

      mxArray *mxType = mxCreateStructMatrix(1,1,2,typeFields);
      mxSetField(mxType,0,"base",mxCreateDoubleScalar(10));
      mxSetField(mxType,0,"fixpt",mxCreateDoubleMatrix(0,0,mxREAL));
      mxSetField(mxData,0,"type",mxType);
    }

    mxSetField(mxData,0,"complexity",mxCreateDoubleScalar(0));
    mxSetField(mxAutoinheritanceInfo,0,"inputs",mxData);
  }

  {
    mxSetField(mxAutoinheritanceInfo,0,"parameters",mxCreateDoubleMatrix(0,0,
                mxREAL));
  }

  {
    mxSetField(mxAutoinheritanceInfo,0,"outputs",mxCreateDoubleMatrix(0,0,mxREAL));
  }

  return(mxAutoinheritanceInfo);
}

static mxArray *sf_get_sim_state_info_c6_thermo(void)
{
  const char *infoFields[] = { "chartChecksum", "varInfo" };

  mxArray *mxInfo = mxCreateStructMatrix(1, 1, 2, infoFields);
  char *infoEncStr[] = {
    "100 S'type','srcId','name','auxInfo'{{M[8],M[0],T\"is_active_c6_thermo\",}}"
  };

  mxArray *mxVarInfo = sf_mex_decode_encoded_mx_struct_array(infoEncStr, 1, 10);
  mxArray *mxChecksum = mxCreateDoubleMatrix(1, 4, mxREAL);
  sf_c6_thermo_get_check_sum(&mxChecksum);
  mxSetField(mxInfo, 0, infoFields[0], mxChecksum);
  mxSetField(mxInfo, 0, infoFields[1], mxVarInfo);
  return mxInfo;
}

static void chart_debug_initialization(SimStruct *S, unsigned int
  fullDebuggerInitialization)
{
  if (!sim_mode_is_rtw_gen(S)) {
    if (ssIsFirstInitCond(S) && fullDebuggerInitialization==1) {
      /* do this only if simulation is starting */
      {
        unsigned int chartAlreadyPresent;
        chartAlreadyPresent = sf_debug_initialize_chart(_thermoMachineNumber_,
          6,
          1,
          1,
          1,
          0,
          0,
          0,
          0,
          0,
          &(chartInstance.chartNumber),
          &(chartInstance.instanceNumber),
          ssGetPath(S),
          (void *)S);
        if (chartAlreadyPresent==0) {
          /* this is the first instance */
          init_script_number_translation(_thermoMachineNumber_,
            chartInstance.chartNumber);
          sf_debug_set_chart_disable_implicit_casting(_thermoMachineNumber_,
            chartInstance.chartNumber,1);
          sf_debug_set_chart_event_thresholds(_thermoMachineNumber_,
            chartInstance.chartNumber,
            0,
            0,
            0);
          _SFD_SET_DATA_PROPS(0,1,1,0,SF_DOUBLE,0,NULL,0,0,0,0.0,1.0,0,"u",0,
                              (MexFcnForType)c6_sf_marshall);
          _SFD_STATE_INFO(0,0,2);
          _SFD_CH_SUBSTATE_COUNT(0);
          _SFD_CH_SUBSTATE_DECOMP(0);
        }

        _SFD_CV_INIT_CHART(0,0,0,0);

        {
          _SFD_CV_INIT_STATE(0,0,0,0,0,0,NULL,NULL);
        }

        _SFD_CV_INIT_TRANS(0,0,NULL,NULL,0,NULL);

        /* Initialization of EML Model Coverage */
        _SFD_CV_INIT_EML(0,1,0,0,0,0,0,0);
        _SFD_CV_INIT_EML_FCN(0,0,"eML_blk_kernel",0,-1,158);
        _SFD_TRANS_COV_WTS(0,0,0,1,0);
        if (chartAlreadyPresent==0) {
          _SFD_TRANS_COV_MAPS(0,
                              0,NULL,NULL,
                              0,NULL,NULL,
                              1,NULL,NULL,
                              0,NULL,NULL);
        }

        {
          real_T *c6_u;
          c6_u = (real_T *)ssGetInputPortSignal(chartInstance.S, 0);
          _SFD_SET_DATA_VALUE_PTR(0U, c6_u);
        }
      }
    } else {
      sf_debug_reset_current_state_configuration(_thermoMachineNumber_,
        chartInstance.chartNumber,chartInstance.instanceNumber);
    }
  }
}

static void sf_opaque_initialize_c6_thermo(void *chartInstanceVar)
{
  chart_debug_initialization(chartInstance.S,0);
  initialize_params_c6_thermo();
  initialize_c6_thermo();
}

static void sf_opaque_enable_c6_thermo(void *chartInstanceVar)
{
  enable_c6_thermo();
}

static void sf_opaque_disable_c6_thermo(void *chartInstanceVar)
{
  disable_c6_thermo();
}

static void sf_opaque_gateway_c6_thermo(void *chartInstanceVar)
{
  sf_c6_thermo();
}

static mxArray* sf_opaque_get_sim_state_c6_thermo(void *chartInstanceVar)
{
  mxArray *st = (mxArray *) get_sim_state_c6_thermo();
  return st;
}

static void sf_opaque_set_sim_state_c6_thermo(void *chartInstanceVar, const
  mxArray *st)
{
  set_sim_state_c6_thermo(sf_mex_dup(st));
}

static void sf_opaque_terminate_c6_thermo(void *chartInstanceVar)
{
  if (sim_mode_is_rtw_gen(chartInstance.S) || sim_mode_is_external
      (chartInstance.S)) {
    sf_clear_rtw_identifier(chartInstance.S);
  }

  finalize_c6_thermo();
}

extern unsigned int sf_machine_global_initializer_called(void);
static void mdlProcessParameters_c6_thermo(SimStruct *S)
{
  int i;
  for (i=0;i<ssGetNumRunTimeParams(S);i++) {
    if (ssGetSFcnParamTunable(S,i)) {
      ssUpdateDlgParamAsRunTimeParam(S,i);
    }
  }

  if (sf_machine_global_initializer_called()) {
    initialize_params_c6_thermo();
  }
}

static void mdlSetWorkWidths_c6_thermo(SimStruct *S)
{
  if (sim_mode_is_rtw_gen(S) || sim_mode_is_external(S)) {
    int_T chartIsInlinable =
      (int_T)sf_is_chart_inlinable("thermo","thermo",6);
    ssSetStateflowIsInlinable(S,chartIsInlinable);
    ssSetRTWCG(S,sf_rtw_info_uint_prop("thermo","thermo",6,"RTWCG"));
    ssSetEnableFcnIsTrivial(S,1);
    ssSetDisableFcnIsTrivial(S,1);
    ssSetNotMultipleInlinable(S,sf_rtw_info_uint_prop("thermo","thermo",6,
      "gatewayCannotBeInlinedMultipleTimes"));
    if (chartIsInlinable) {
      ssSetInputPortOptimOpts(S, 0, SS_REUSABLE_AND_LOCAL);
      sf_mark_chart_expressionable_inputs(S,"thermo","thermo",6,1);
    }

    sf_set_rtw_dwork_info(S,"thermo","thermo",6);
    ssSetHasSubFunctions(S,!(chartIsInlinable));
    ssSetOptions(S,ssGetOptions(S)|SS_OPTION_WORKS_WITH_CODE_REUSE);
  }

  ssSetChecksum0(S,(786019022U));
  ssSetChecksum1(S,(2932086512U));
  ssSetChecksum2(S,(1131258050U));
  ssSetChecksum3(S,(2473045908U));
  ssSetmdlDerivatives(S, NULL);
  ssSetExplicitFCSSCtrl(S,1);
}

static void mdlRTW_c6_thermo(SimStruct *S)
{
  if (sim_mode_is_rtw_gen(S)) {
    sf_write_symbol_mapping(S, "thermo", "thermo",6);
    ssWriteRTWStrParam(S, "StateflowChartType", "Embedded MATLAB");
  }
}

static void mdlStart_c6_thermo(SimStruct *S)
{
  chartInstance.chartInfo.chartInstance = NULL;
  chartInstance.chartInfo.isEMLChart = 1;
  chartInstance.chartInfo.chartInitialized = 0;
  chartInstance.chartInfo.sFunctionGateway = sf_opaque_gateway_c6_thermo;
  chartInstance.chartInfo.initializeChart = sf_opaque_initialize_c6_thermo;
  chartInstance.chartInfo.terminateChart = sf_opaque_terminate_c6_thermo;
  chartInstance.chartInfo.enableChart = sf_opaque_enable_c6_thermo;
  chartInstance.chartInfo.disableChart = sf_opaque_disable_c6_thermo;
  chartInstance.chartInfo.getSimState = sf_opaque_get_sim_state_c6_thermo;
  chartInstance.chartInfo.setSimState = sf_opaque_set_sim_state_c6_thermo;
  chartInstance.chartInfo.getSimStateInfo = sf_get_sim_state_info_c6_thermo;
  chartInstance.chartInfo.zeroCrossings = NULL;
  chartInstance.chartInfo.outputs = NULL;
  chartInstance.chartInfo.derivatives = NULL;
  chartInstance.chartInfo.mdlRTW = mdlRTW_c6_thermo;
  chartInstance.chartInfo.mdlStart = mdlStart_c6_thermo;
  chartInstance.chartInfo.mdlSetWorkWidths = mdlSetWorkWidths_c6_thermo;
  chartInstance.chartInfo.extModeExec = NULL;
  chartInstance.chartInfo.restoreLastMajorStepConfiguration = NULL;
  chartInstance.chartInfo.restoreBeforeLastMajorStepConfiguration = NULL;
  chartInstance.chartInfo.storeCurrentConfiguration = NULL;
  chartInstance.S = S;
  ssSetUserData(S,(void *)(&(chartInstance.chartInfo)));/* register the chart instance with simstruct */
  if (!sim_mode_is_rtw_gen(S)) {
    init_dsm_address_info();
  }

  chart_debug_initialization(S,1);
}

void c6_thermo_method_dispatcher(SimStruct *S, int_T method, void *data)
{
  switch (method) {
   case SS_CALL_MDL_START:
    mdlStart_c6_thermo(S);
    break;

   case SS_CALL_MDL_SET_WORK_WIDTHS:
    mdlSetWorkWidths_c6_thermo(S);
    break;

   case SS_CALL_MDL_PROCESS_PARAMETERS:
    mdlProcessParameters_c6_thermo(S);
    break;

   default:
    /* Unhandled method */
    sf_mex_error_message("Stateflow Internal Error:\n"
                         "Error calling c6_thermo_method_dispatcher.\n"
                         "Can't handle method %d.\n", method);
    break;
  }
}
