/* Include files */

#include "blascompat32.h"
#include "thermo_sfun.h"
#include "c5_thermo.h"
#define CHARTINSTANCE_CHARTNUMBER      (chartInstance.chartNumber)
#define CHARTINSTANCE_INSTANCENUMBER   (chartInstance.instanceNumber)
#include "thermo_sfun_debug_macros.h"

/* Type Definitions */

/* Named Constants */
#define c5_IN_NO_ACTIVE_CHILD          (0)

/* Variable Declarations */

/* Variable Definitions */
static SFc5_thermoInstanceStruct chartInstance;

/* Function Declarations */
static void initialize_c5_thermo(void);
static void initialize_params_c5_thermo(void);
static void enable_c5_thermo(void);
static void disable_c5_thermo(void);
static void c5_update_debugger_state_c5_thermo(void);
static const mxArray *get_sim_state_c5_thermo(void);
static void set_sim_state_c5_thermo(const mxArray *c5_st);
static void finalize_c5_thermo(void);
static void sf_c5_thermo(void);
static void init_script_number_translation(uint32_T c5_machineNumber, uint32_T
  c5_chartNumber);
static const mxArray *c5_sf_marshall(void *c5_chartInstance, void *c5_u);
static const mxArray *c5_b_sf_marshall(void *c5_chartInstance, void *c5_u);
static void init_dsm_address_info(void);

/* Function Definitions */
static void initialize_c5_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
  chartInstance.c5_is_active_c5_thermo = 0U;
}

static void initialize_params_c5_thermo(void)
{
}

static void enable_c5_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
}

static void disable_c5_thermo(void)
{
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
}

static void c5_update_debugger_state_c5_thermo(void)
{
}

static const mxArray *get_sim_state_c5_thermo(void)
{
  const mxArray *c5_st = NULL;
  const mxArray *c5_y = NULL;
  uint8_T c5_u;
  const mxArray *c5_b_y = NULL;
  c5_st = NULL;
  c5_y = NULL;
  sf_mex_assign(&c5_y, sf_mex_createcellarray(1));
  c5_u = chartInstance.c5_is_active_c5_thermo;
  c5_b_y = NULL;
  sf_mex_assign(&c5_b_y, sf_mex_create("y", &c5_u, 3, 0U, 0U, 0U, 0));
  sf_mex_setcell(c5_y, 0, c5_b_y);
  sf_mex_assign(&c5_st, c5_y);
  return c5_st;
}

static void set_sim_state_c5_thermo(const mxArray *c5_st)
{
  const mxArray *c5_u;
  const mxArray *c5_b_is_active_c5_thermo;
  uint8_T c5_u0;
  uint8_T c5_y;
  chartInstance.c5_doneDoubleBufferReInit = true;
  c5_u = sf_mex_dup(c5_st);
  c5_b_is_active_c5_thermo = sf_mex_dup(sf_mex_getcell(c5_u, 0));
  sf_mex_import("is_active_c5_thermo", sf_mex_dup(c5_b_is_active_c5_thermo),
                &c5_u0, 1, 3, 0U, 0, 0U, 0);
  c5_y = c5_u0;
  sf_mex_destroy(&c5_b_is_active_c5_thermo);
  chartInstance.c5_is_active_c5_thermo = c5_y;
  sf_mex_destroy(&c5_u);
  c5_update_debugger_state_c5_thermo();
  sf_mex_destroy(&c5_st);
}

static void finalize_c5_thermo(void)
{
}

static void sf_c5_thermo(void)
{
  int32_T c5_previousEvent;
  real_T c5_u;
  real_T c5_nargout = 0.0;
  real_T c5_nargin = 1.0;
  char_T c5_b_u;
  const mxArray *c5_y = NULL;
  real_T c5_c_u;
  const mxArray *c5_b_y = NULL;
  int32_T c5_i0;
  static char_T c5_cv0[12] = { 'd', 'r', 'p', 'm', '(', 'e', 'n', 'd', '+', '1',
    ')', '=' };

  char_T c5_d_u[12];
  const mxArray *c5_c_y = NULL;
  int32_T c5_i1;
  static char_T c5_cv1[4] = { 'b', 'a', 's', 'e' };

  char_T c5_e_u[4];
  const mxArray *c5_d_y = NULL;
  real_T *c5_f_u;
  c5_f_u = (real_T *)ssGetInputPortSignal(chartInstance.S, 0);
  _sfTime_ = (real_T)ssGetT(chartInstance.S);
  _SFD_CC_CALL(CHART_ENTER_SFUNCTION_TAG,4);
  _SFD_DATA_RANGE_CHECK(*c5_f_u, 0U);
  c5_previousEvent = _sfEvent_;
  _sfEvent_ = CALL_EVENT;
  _SFD_CC_CALL(CHART_ENTER_DURING_FUNCTION_TAG,4);
  c5_u = *c5_f_u;
  sf_debug_symbol_scope_push(3U, 0U);
  sf_debug_symbol_scope_add("nargout", &c5_nargout, c5_sf_marshall);
  sf_debug_symbol_scope_add("nargin", &c5_nargin, c5_sf_marshall);
  sf_debug_symbol_scope_add("u", &c5_u, c5_sf_marshall);
  CV_EML_FCN(0, 0);
  _SFD_EML_CALL(0,3);
  _SFD_EML_CALL(0,4);
  _SFD_EML_CALL(0,5);
  _SFD_EML_CALL(0,6);
  c5_b_u = ';';
  c5_y = NULL;
  sf_mex_assign(&c5_y, sf_mex_create("y", &c5_b_u, 10, 0U, 0U, 0U, 0));
  c5_c_u = c5_u;
  c5_b_y = NULL;
  sf_mex_assign(&c5_b_y, sf_mex_create("y", &c5_c_u, 0, 0U, 0U, 0U, 0));
  for (c5_i0 = 0; c5_i0 < 12; c5_i0 = c5_i0 + 1) {
    c5_d_u[c5_i0] = c5_cv0[c5_i0];
  }

  c5_c_y = NULL;
  sf_mex_assign(&c5_c_y, sf_mex_create("y", &c5_d_u, 10, 0U, 1U, 0U, 2, 1, 12));
  for (c5_i1 = 0; c5_i1 < 4; c5_i1 = c5_i1 + 1) {
    c5_e_u[c5_i1] = c5_cv1[c5_i1];
  }

  c5_d_y = NULL;
  sf_mex_assign(&c5_d_y, sf_mex_create("y", &c5_e_u, 10, 0U, 1U, 0U, 2, 1, 4));
  sf_mex_call_debug("evalin", 0U, 2U, 14, c5_d_y, 14, sf_mex_call_debug("strcat",
    1U, 3U, 14, c5_c_y, 14, sf_mex_call_debug("num2str"
    , 1U, 1U, 14, c5_b_y), 14, c5_y));
  _SFD_EML_CALL(0,-6);
  sf_debug_symbol_scope_pop();
  _SFD_CC_CALL(EXIT_OUT_OF_FUNCTION_TAG,4);
  _sfEvent_ = c5_previousEvent;
  sf_debug_check_for_state_inconsistency(_thermoMachineNumber_,
    chartInstance.chartNumber, chartInstance.instanceNumber);
}

static void init_script_number_translation(uint32_T c5_machineNumber, uint32_T
  c5_chartNumber)
{
}

static const mxArray *c5_sf_marshall(void *c5_chartInstance, void *c5_u)
{
  const mxArray *c5_y = NULL;
  real_T c5_b_u;
  const mxArray *c5_b_y = NULL;
  c5_y = NULL;
  c5_b_u = *((real_T *)c5_u);
  c5_b_y = NULL;
  sf_mex_assign(&c5_b_y, sf_mex_create("y", &c5_b_u, 0, 0U, 0U, 0U, 0));
  sf_mex_assign(&c5_y, c5_b_y);
  return c5_y;
}

const mxArray *sf_c5_thermo_get_eml_resolved_functions_info(void)
{
  const mxArray *c5_nameCaptureInfo = NULL;
  c5_nameCaptureInfo = NULL;
  sf_mex_assign(&c5_nameCaptureInfo, sf_mex_create("nameCaptureInfo", NULL, 0,
    0U, 1U, 0U, 2, 0, 1));
  return c5_nameCaptureInfo;
}

static const mxArray *c5_b_sf_marshall(void *c5_chartInstance, void *c5_u)
{
  const mxArray *c5_y = NULL;
  boolean_T c5_b_u;
  const mxArray *c5_b_y = NULL;
  c5_y = NULL;
  c5_b_u = *((boolean_T *)c5_u);
  c5_b_y = NULL;
  sf_mex_assign(&c5_b_y, sf_mex_create("y", &c5_b_u, 11, 0U, 0U, 0U, 0));
  sf_mex_assign(&c5_y, c5_b_y);
  return c5_y;
}

static void init_dsm_address_info(void)
{
}

/* SFunction Glue Code */
void sf_c5_thermo_get_check_sum(mxArray *plhs[])
{
  ((real_T *)mxGetPr((plhs[0])))[0] = (real_T)(2311590790U);
  ((real_T *)mxGetPr((plhs[0])))[1] = (real_T)(673341356U);
  ((real_T *)mxGetPr((plhs[0])))[2] = (real_T)(746117254U);
  ((real_T *)mxGetPr((plhs[0])))[3] = (real_T)(4125436293U);
}

mxArray *sf_c5_thermo_get_autoinheritance_info(void)
{
  const char *autoinheritanceFields[] = { "checksum", "inputs", "parameters",
    "outputs" };

  mxArray *mxAutoinheritanceInfo = mxCreateStructMatrix(1,1,4,
    autoinheritanceFields);

  {
    mxArray *mxChecksum = mxCreateDoubleMatrix(4,1,mxREAL);
    double *pr = mxGetPr(mxChecksum);
    pr[0] = (double)(2922354609U);
    pr[1] = (double)(3285886268U);
    pr[2] = (double)(3575247909U);
    pr[3] = (double)(2883515188U);
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

static mxArray *sf_get_sim_state_info_c5_thermo(void)
{
  const char *infoFields[] = { "chartChecksum", "varInfo" };

  mxArray *mxInfo = mxCreateStructMatrix(1, 1, 2, infoFields);
  char *infoEncStr[] = {
    "100 S'type','srcId','name','auxInfo'{{M[8],M[0],T\"is_active_c5_thermo\",}}"
  };

  mxArray *mxVarInfo = sf_mex_decode_encoded_mx_struct_array(infoEncStr, 1, 10);
  mxArray *mxChecksum = mxCreateDoubleMatrix(1, 4, mxREAL);
  sf_c5_thermo_get_check_sum(&mxChecksum);
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
          5,
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
                              (MexFcnForType)c5_sf_marshall);
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
        _SFD_CV_INIT_EML_FCN(0,0,"eML_blk_kernel",0,-1,156);
        _SFD_TRANS_COV_WTS(0,0,0,1,0);
        if (chartAlreadyPresent==0) {
          _SFD_TRANS_COV_MAPS(0,
                              0,NULL,NULL,
                              0,NULL,NULL,
                              1,NULL,NULL,
                              0,NULL,NULL);
        }

        {
          real_T *c5_u;
          c5_u = (real_T *)ssGetInputPortSignal(chartInstance.S, 0);
          _SFD_SET_DATA_VALUE_PTR(0U, c5_u);
        }
      }
    } else {
      sf_debug_reset_current_state_configuration(_thermoMachineNumber_,
        chartInstance.chartNumber,chartInstance.instanceNumber);
    }
  }
}

static void sf_opaque_initialize_c5_thermo(void *chartInstanceVar)
{
  chart_debug_initialization(chartInstance.S,0);
  initialize_params_c5_thermo();
  initialize_c5_thermo();
}

static void sf_opaque_enable_c5_thermo(void *chartInstanceVar)
{
  enable_c5_thermo();
}

static void sf_opaque_disable_c5_thermo(void *chartInstanceVar)
{
  disable_c5_thermo();
}

static void sf_opaque_gateway_c5_thermo(void *chartInstanceVar)
{
  sf_c5_thermo();
}

static mxArray* sf_opaque_get_sim_state_c5_thermo(void *chartInstanceVar)
{
  mxArray *st = (mxArray *) get_sim_state_c5_thermo();
  return st;
}

static void sf_opaque_set_sim_state_c5_thermo(void *chartInstanceVar, const
  mxArray *st)
{
  set_sim_state_c5_thermo(sf_mex_dup(st));
}

static void sf_opaque_terminate_c5_thermo(void *chartInstanceVar)
{
  if (sim_mode_is_rtw_gen(chartInstance.S) || sim_mode_is_external
      (chartInstance.S)) {
    sf_clear_rtw_identifier(chartInstance.S);
  }

  finalize_c5_thermo();
}

extern unsigned int sf_machine_global_initializer_called(void);
static void mdlProcessParameters_c5_thermo(SimStruct *S)
{
  int i;
  for (i=0;i<ssGetNumRunTimeParams(S);i++) {
    if (ssGetSFcnParamTunable(S,i)) {
      ssUpdateDlgParamAsRunTimeParam(S,i);
    }
  }

  if (sf_machine_global_initializer_called()) {
    initialize_params_c5_thermo();
  }
}

static void mdlSetWorkWidths_c5_thermo(SimStruct *S)
{
  if (sim_mode_is_rtw_gen(S) || sim_mode_is_external(S)) {
    int_T chartIsInlinable =
      (int_T)sf_is_chart_inlinable("thermo","thermo",5);
    ssSetStateflowIsInlinable(S,chartIsInlinable);
    ssSetRTWCG(S,sf_rtw_info_uint_prop("thermo","thermo",5,"RTWCG"));
    ssSetEnableFcnIsTrivial(S,1);
    ssSetDisableFcnIsTrivial(S,1);
    ssSetNotMultipleInlinable(S,sf_rtw_info_uint_prop("thermo","thermo",5,
      "gatewayCannotBeInlinedMultipleTimes"));
    if (chartIsInlinable) {
      ssSetInputPortOptimOpts(S, 0, SS_REUSABLE_AND_LOCAL);
      sf_mark_chart_expressionable_inputs(S,"thermo","thermo",5,1);
    }

    sf_set_rtw_dwork_info(S,"thermo","thermo",5);
    ssSetHasSubFunctions(S,!(chartIsInlinable));
    ssSetOptions(S,ssGetOptions(S)|SS_OPTION_WORKS_WITH_CODE_REUSE);
  }

  ssSetChecksum0(S,(732939112U));
  ssSetChecksum1(S,(809588179U));
  ssSetChecksum2(S,(1061343795U));
  ssSetChecksum3(S,(3302849469U));
  ssSetmdlDerivatives(S, NULL);
  ssSetExplicitFCSSCtrl(S,1);
}

static void mdlRTW_c5_thermo(SimStruct *S)
{
  if (sim_mode_is_rtw_gen(S)) {
    sf_write_symbol_mapping(S, "thermo", "thermo",5);
    ssWriteRTWStrParam(S, "StateflowChartType", "Embedded MATLAB");
  }
}

static void mdlStart_c5_thermo(SimStruct *S)
{
  chartInstance.chartInfo.chartInstance = NULL;
  chartInstance.chartInfo.isEMLChart = 1;
  chartInstance.chartInfo.chartInitialized = 0;
  chartInstance.chartInfo.sFunctionGateway = sf_opaque_gateway_c5_thermo;
  chartInstance.chartInfo.initializeChart = sf_opaque_initialize_c5_thermo;
  chartInstance.chartInfo.terminateChart = sf_opaque_terminate_c5_thermo;
  chartInstance.chartInfo.enableChart = sf_opaque_enable_c5_thermo;
  chartInstance.chartInfo.disableChart = sf_opaque_disable_c5_thermo;
  chartInstance.chartInfo.getSimState = sf_opaque_get_sim_state_c5_thermo;
  chartInstance.chartInfo.setSimState = sf_opaque_set_sim_state_c5_thermo;
  chartInstance.chartInfo.getSimStateInfo = sf_get_sim_state_info_c5_thermo;
  chartInstance.chartInfo.zeroCrossings = NULL;
  chartInstance.chartInfo.outputs = NULL;
  chartInstance.chartInfo.derivatives = NULL;
  chartInstance.chartInfo.mdlRTW = mdlRTW_c5_thermo;
  chartInstance.chartInfo.mdlStart = mdlStart_c5_thermo;
  chartInstance.chartInfo.mdlSetWorkWidths = mdlSetWorkWidths_c5_thermo;
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

void c5_thermo_method_dispatcher(SimStruct *S, int_T method, void *data)
{
  switch (method) {
   case SS_CALL_MDL_START:
    mdlStart_c5_thermo(S);
    break;

   case SS_CALL_MDL_SET_WORK_WIDTHS:
    mdlSetWorkWidths_c5_thermo(S);
    break;

   case SS_CALL_MDL_PROCESS_PARAMETERS:
    mdlProcessParameters_c5_thermo(S);
    break;

   default:
    /* Unhandled method */
    sf_mex_error_message("Stateflow Internal Error:\n"
                         "Error calling c5_thermo_method_dispatcher.\n"
                         "Can't handle method %d.\n", method);
    break;
  }
}
