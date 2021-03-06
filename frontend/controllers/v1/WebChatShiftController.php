<?php
namespace frontend\controllers\v1;

use Yii;
use yii\web\Response;
use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\models\shift\Schedule;
use common\models\shift\ShiftDate;
use common\models\shift\ShiftResultConfirm;
use common\models\shift\ShiftType;
use common\models\shift\ShiftOrderBy;
use common\models\shift\TypeSkill;
use common\models\shift\ShiftResult;
use common\models\shift\ShiftResultOrange;
use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\employee\Employee;
use common\models\shift\EmpSkill;
use common\models\shift\ShiftModel;
use common\models\user\User;
use common\models\shift\ShiftTypeDetail;
use common\models\attendance\AttendanceRecord;
use common\models\shift\ShiftChangeApply;
use common\models\attendance\ApproverTab;

use common\models\system\AppSys;


class WebChatShiftController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\ShiftResult';

    /**
     * 
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
        'errno'   => 0,
        'status'  =>''
    ];

    

    /**
     * @param  [action] yii\rest\IndexAction
     * @return [type] 
     */
    public function beforeAction($action)
    {
        $format = \Yii::$app->getRequest()->getQueryParam('format', 'json');

        if($format == 'xml'){
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        return $action;
    }

    /**
     * @param  [type]
     * @param  [type]
     * @return [type]
     */
    public function afterAction($action, $result){
        $result = parent::afterAction($action, $result);

        return $result;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * @SWG\Post(path="/web-chat-shift/shift-change",
     *     tags={"微信-shift-调班申请"},
     *     summary="获取调班人某天班次信息",
     *     description="获取调班人某天班次信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDate",
     *        description = "开始时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "获取调班人某天班次信息"
     *     ),
     * )
     *
    **/
    public function actionShiftChange()
    {
        //获取员编号
        $empId=$this->empNumber;
        $post=Yii::$app->request->post();
        $shiftdate=$post['shiftDate'];

        $work_station=$this->workStation;

        if(!strtotime($shiftdate)){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不是正确的时间格式";
            return [];
        }


        
        $shiftResult=array();
        $orange=array();
        //查询排班临时表中是否有数据
        $typedetailmodel=new ShiftTypeDetail;
        $resultmodel=new ShiftResult;
        $shiftordermodel=new ShiftOrderBy;
        $typemode=new ShiftType;
       
        $shiftResult = $resultmodel->getShiftByDateAndEmp2($empId,$shiftdate,$work_station);
        $work_station=$this->workStation;
       
        $is_default=0;

        if($shiftResult['status']==true){
            $scheduleId=$shiftResult['schedule_id'];
            if($shiftResult['second']['type_id']!=NULL && $shiftResult['first']['type_id'] == $shiftResult['second']['type_id']){
                $orange[0]['id']=$shiftResult['first']['type_id']>0?$shiftResult['first']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['first']['name'])?$shiftResult['first']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='0';
                $orange[0]['time'][0]['id']='0';
                $orange[0]['time'][0]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][0]['time_combine']='全部';

                $orange[0]['time'][1]['id']='1';
                $orange[0]['time'][1]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][1]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][1]['time_combine']=$shiftResult['first']['start_time'].'-'.$shiftResult['first']['end_time'];

                $orange[0]['time'][2]['id']='2';
                $orange[0]['time'][2]['start_time']=$shiftResult['second']['start_time'];
                $orange[0]['time'][2]['end_time']=$shiftResult['second']['end_time'];
                $orange[0]['time'][2]['time_combine']=$shiftResult['second']['start_time'].'-'.$shiftResult['second']['end_time'];

            }else if( $shiftResult['second']['type_id']!=NULL && $shiftResult['second']['type_id'] == $shiftResult['third']['type_id']){

                $orange[0]['id']=$shiftResult['second']['type_id']>0?$shiftResult['second']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['second']['name'])?$shiftResult['second']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='0';
                $orange[0]['time'][0]['id']='0';
                $orange[0]['time'][0]['start_time']=$shiftResult['second']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['second']['end_time'];
                $orange[0]['time'][0]['time_combine']='全部';

                $orange[0]['time'][1]['id']='1';
                $orange[0]['time'][1]['start_time']=$shiftResult['second']['start_time'];
                $orange[0]['time'][1]['end_time']=$shiftResult['second']['end_time'];
                $orange[0]['time'][1]['time_combine']=$shiftResult['second']['start_time'].'-'.$shiftResult['second']['end_time'];

                $orange[0]['time'][2]['id']='2';
                $orange[0]['time'][2]['start_time']=$shiftResult['third']['start_time'];
                $orange[0]['time'][2]['end_time']=$shiftResult['third']['end_time'];
                $orange[0]['time'][2]['time_combine']=$shiftResult['third']['start_time'].'-'.$shiftResult['third']['end_time'];

            }else if ($shiftResult['first']['type_id']!=NULL && $shiftResult['first']['type_id'] == $shiftResult['third']['type_id']){

                $orange[0]['id']=$shiftResult['first']['type_id']>0?$shiftResult['first']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['first']['name'])?$shiftResult['first']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='0';
                $orange[0]['time'][0]['id']='0';
                $orange[0]['time'][0]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][0]['time_combine']='全部';

                $orange[0]['time'][1]['id']='1';
                $orange[0]['time'][1]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][1]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][1]['time_combine']=$shiftResult['first']['start_time'].'-'.$shiftResult['first']['end_time'];

                $orange[0]['time'][2]['id']='2';
                $orange[0]['time'][2]['start_time']=$shiftResult['third']['start_time'];
                $orange[0]['time'][2]['end_time']=$shiftResult['third']['end_time'];
                $orange[0]['time'][2]['time_combine']=$shiftResult['third']['start_time'].'-'.$shiftResult['third']['end_time'];

            }else if($shiftResult['first']['type_id']==NULL && $shiftResult['second']['type_id']==NULL &&  $shiftResult['third']['type_id']==NULL){

                $orange[0]['id']=$shiftResult['first']['type_id']>0?$shiftResult['first']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['first']['name'])?$shiftResult['first']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='0';
                $orange[0]['time'][0]['id']='0';
                $orange[0]['time'][0]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][0]['time_combine']='全部';

                $orange[0]['time'][1]['id']='1';
                $orange[0]['time'][1]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][1]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][1]['time_combine']=$shiftResult['first']['start_time'].'-'.$shiftResult['first']['end_time'];

                $orange[0]['time'][2]['id']='2';
                $orange[0]['time'][2]['start_time']=$shiftResult['second']['start_time'];
                $orange[0]['time'][2]['end_time']=$shiftResult['second']['end_time'];
                $orange[0]['time'][2]['time_combine']=$shiftResult['second']['start_time'].'-'.$shiftResult['second']['end_time'];

            }else if($shiftResult['first']['type_id']==NULL && $shiftResult['second']['type_id']==NULL &&  $shiftResult['third']['type_id']!=NULL){

                $orange[0]['id']=$shiftResult['third']['type_id']>0?$shiftResult['third']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['third']['name'])?$shiftResult['third']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='0';
                $orange[0]['time'][0]['id']='0';
                $orange[0]['time'][0]['start_time']=$shiftResult['third']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['third']['end_time'];
                $orange[0]['time'][0]['time_combine']='全部';



            }else{

                $orange[0]['id']=$shiftResult['first']['type_id']>0?$shiftResult['first']['type_id']:'gongxiu';
                $orange[0]['scheduleId']=$shiftResult['schedule_id'];
                $orange[0]['name']=isset($shiftResult['first']['name'])?$shiftResult['first']['name']:'休息';
                $orange[0]['is_show']='1';
                $orange[0]['is_default']='1';

                $orange[0]['time'][0]['id']='1';
                $orange[0]['time'][0]['start_time']=$shiftResult['first']['start_time'];
                $orange[0]['time'][0]['end_time']=$shiftResult['first']['end_time'];
                $orange[0]['time'][0]['time_combine']=$shiftResult['first']['start_time'].'-'.$shiftResult['first']['end_time'];

                $orange[1]['id']=$shiftResult['second']['type_id']>0?$shiftResult['second']['type_id']:'gongxiu';
                $orange[1]['scheduleId']=$shiftResult['schedule_id'];
                $orange[1]['name']=isset($shiftResult['second']['name'])?$shiftResult['second']['name']:'休息';
                $orange[1]['is_show']='1';
                $orange[1]['is_default']='2';

                $orange[1]['time'][0]['id']='2';
                $orange[1]['time'][0]['start_time']=$shiftResult['second']['start_time'];
                $orange[1]['time'][0]['end_time']=$shiftResult['second']['end_time'];
                $orange[1]['time'][0]['time_combine']=$shiftResult['second']['start_time'].'-'.$shiftResult['second']['end_time'];

            }
            
            $result['orange'] = $orange;
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";
            return $result;

        }else{
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "期间没有排班信息";
            return [];
        }
        
    }


    /**
     * @SWG\Post(path="/web-chat-shift/type-confirm",
     *     tags={"微信-shift-调班申请"},
     *     summary="获取调班人某天班次信息",
     *     description="获取调班人某天班次信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDate",
     *        description = "开始时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "timemark",
     *        description = "时间段标记",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "orange_type",
     *        description = "申请的班次ID",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "申请的班次ID",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "获取调班人某天班次信息"
     *     ),
     * )
     *
    **/
    public function actionTypeConfirm()
    {
        $empId=$this->empNumber;
        $post=Yii::$app->request->post();
        $shiftdate=$post['shiftDate'];
        $timemark=$post['timemark'];
        $orange_type=$post['orange_type'];

        $schedule_id=$post['schedule_id'];
        $resultmodel=new ShiftResult;
        $work_station=$this->workStation;

        if(!strtotime($shiftdate)){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不是正确的时间格式";
            return [];
        }

        $cur_date=$resultmodel->getResultByformat($schedule_id,$shiftdate,$timemark,$work_station,$orange_type);


        if($cur_date['status']==true){
            unset($cur_date['status']);

            $unique=array_column($cur_date,NULL, 'type_id');

            $result['cur_date'] = array_values($unique);
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";
            return $result;

        }else{
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "期间没有排班信息";
            return [];
        }

    }

    /**
     * @SWG\Post(path="/web-chat-shift/change-emp",
     *     tags={"微信-shift-调班申请"},
     *     summary="获取被调班次的人员列表",
     *     description="获取被调班次的人员列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "scheduleId",
     *        description = "班次计划id",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ChangeshiftDate",
     *        description = "要调班的日期",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "newType",
     *        description = "被调的班次",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "timeMarkOne",
     *        description = "第一个时间段",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionChangeEmp()
    {

        $post=Yii::$app->request->post();
        $schedule_id=$post['scheduleId'];
        $date=$post['ChangeshiftDate'];
        $type=$post['newType'];
        $timeMarkOne = $post['timeMarkOne'];
        $empId=$this->empNumber;
        $resultmodel=new ShiftResult;
        $empmodel=new Employee;
        $shiftordermodel=new ShiftOrderBy;
        $typedetailmodel=new ShiftTypeDetail;
        $schedulemodel=new Schedule;

        if(!strtotime($date)){
          
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不是正确的时间格式";
            return [];
        }
        if(null==$timeMarkOne){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "原班次选择时间段";
            return [];
        }


        $work_station=$this->workStation;
        $changEmpListLast=array();
        $changEmpList=array();
        $schedule=$schedulemodel->getScheduleById($schedule_id);

        if($schedule->is_confirm==1 && $schedule->is_show==1){
            $cur_date=$resultmodel->getShiftByDateAndEmp3($date,$type,$timeMarkOne,$schedule_id,$work_station,$empId);
            if($cur_date['status']==true){
                unset($cur_date['status']);
                $this->serializer['errno']   = 0;
                $this->serializer['status']   = true;
                $this->serializer['message'] = "获取人员列表成功";
                return $cur_date;
            }else{
                $this->serializer['errno']   = 0;
                $this->serializer['status']   = false;
                $this->serializer['message'] = "没有符合的员工信息";
                return [];
            }
               
        }
    }


    /**
     * @SWG\Post(path="/web-chat-shift/change-apply",
     *     tags={"微信-shift-调班申请"},
     *     summary="提交调班申请",
     *     description="提交调班申请",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "confirm_emp",
     *        description = "被调班次人员的工资号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_date",
     *        description = "要调班的日期",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sup_employee",
     *        description = "审核人工资号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "approver",
     *        description = "审核人姓名",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "orange_type",
     *        description = "原始班次",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "confirm_type",
     *        description = "被调班次",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "timeMarkOne",
     *        description = "第一个时间段",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "timeMarkTwo",
     *        description = "第二个时间段",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoId",
     *        description = "抄送人工资号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoName",
     *        description = "抄送人姓名",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "申请理由",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),



     * )
     *
    **/
    public function actionChangeApply()
    {


        $post=Yii::$app->request->post();
        $schedule_id = $post['schedule_id']; 
        $shift_date = $post['shift_date']; 
        $orange_emp = $this->empNumber; 
        $confirm_emp = $post['confirm_emp'];//需要与谁调班
        $shift_reason = $post['note'];//申请理由
        $orangeType=$post['orange_type'];//原班次的ID
        $confirmType=$post['confirm_type'];//新班次的ID
        $sup_employees=$post['sup_employee'];//审核人id
        $chaoId = isset($post['chaoId'])?$post['chaoId']:'';   //抄送人ID
        $chaoName = isset($post['chaoName'])?$post['chaoName']:'';    //抄送人名
        $timeMark = $post['timeMarkOne'];   //时间段标记
        $chao_arr = explode(',',$chaoId);

        $work_station=$this->workStation;
        $attendmodel=new AttendanceRecord;
        $applymodel=new ShiftChangeApply;
        $resultmodel=new ShiftResult;

        $if_daka_orange=$attendmodel->getAttendanceRecordByWB($orange_emp,$shift_date);
        $if_daka_confrim=$attendmodel->getAttendanceRecordByWB($confirm_emp,$shift_date);

        $param='申请人：'.$orange_emp.';被申请人:'.$confirm_emp.';申请日期:'.$shift_date.';申请人打卡返回信息:'.$if_daka_orange.';被请人打卡返回信息:'.$if_daka_confrim.';调班时间段:'.$timeMark;

        if($if_daka_orange==-1){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "您有一条打卡信息，不允许调班";
            return [];
        }

        if( $if_daka_confrim==-1){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "您有一条打卡信息，不允许调班";
            return [];
        }

        if($if_daka_orange >0){
            if($timeMark !=$if_daka_orange){

                $this->serializer['errno']   = 0;
                $this->serializer['status']   = false;
                $this->serializer['message'] = "您的班次时间段与打卡时间段冲突，不允许调班";
                return [];

            }
        }

        if($if_daka_confrim>0){
            if($timeMark != $if_daka_confrim){
                $this->serializer['errno']   = 0;
                $this->serializer['status']   = false;
                $this->serializer['message'] = "被申请人班次时间段与打卡时间段冲突，不允许调班";
                return [];
            }
        }
        
    
        $login_emp=$this->empNumber;
        $sup_employees=explode(',', $sup_employees);
        $time1=strtotime($shift_date);
        $time3=strtotime(date('Y-m-d',time()));

        if($time1<$time3){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不能调节已上班次";
            return [];
        }

        if(!strtotime($shift_date)){

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不是正确的时间格式";
            return [];
        }
        //查询审核表中是否有调班申请

        $inShenHeOr=$applymodel->getShiftChangeApply($orange_emp);

        $inShenHeOr2=$applymodel->getShiftChangeApply2($confirm_emp);

        $inShenHeOr3=$applymodel->getShiftChangeApply($confirm_emp);

        $inShenHeOr4=$applymodel->getShiftChangeApply2($orange_emp);

    
        // if(count($inShenHeOr)>0|| count($inShenHeOr2)>0 ||count($inShenHeOr3)>0|| count($inShenHeOr4)>0 ){
        //     $this->serializer['errno']   = 0;
        //     $this->serializer['status']   = false;
        //     $this->serializer['message'] = "申请人/被申请人有一条申请正在审核中，请先处理";
        //     return [];
        // }


        $leavemodel=new LeaveEntitlement;
        $typemodel=new ShiftType;

        $oran_leave=$leavemodel->judgeUsedLeave($orange_emp,$shift_date);
        //原始班次信息
        $orangeTypEntity=array();
        $newShiftType=array();
        $orangeTypEntity=$typemodel->getShifTypeById($orangeType);


        $transaction = Yii::$app->db->beginTransaction();

        try {

            $time=date('Y-m-d H:i:s',time());
            $newShiftType =$typemodel->getShifTypeById($confirmType);
            $is_approval = isset($newShiftType)?$newShiftType['is_approval']:0;
            $shiftapply=new ShiftChangeApply;
            $applydata['ShiftChangeApply']['schedule_id']=$schedule_id;
            $applydata['ShiftChangeApply']['orange_emp']=$orange_emp;
            $applydata['ShiftChangeApply']['confirm_emp']=$confirm_emp;
            $applydata['ShiftChangeApply']['shift_date']=$shift_date;
            $applydata['ShiftChangeApply']['reason_shift']=$shift_reason;

            if($is_approval){
                $applydata['ShiftChangeApply']['status']='1';
            }else{
                $applydata['ShiftChangeApply']['status']='2';
            }

            $applydata['ShiftChangeApply']['create_at']=$time;
            $applydata['ShiftChangeApply']['orange_type']=$orangeType;
            $applydata['ShiftChangeApply']['confirm_type']=$confirmType;
            $applydata['ShiftChangeApply']['time_mark']=$timeMark;
               
            
            if(!$shiftapply->saveApply($applydata)){
                throw new \Exception();
            }

            $shift_apply_id=$shiftapply->id;



            if(!$is_approval){
                $result = $resultmodel->confirmShiftNoLeave($shiftapply->schedule_id,$shiftapply->shift_date,$shiftapply->orange_emp,$shiftapply->confirm_emp,$shiftapply->orange_type,$shiftapply->confirm_type,$shiftapply->time_mark,$work_station,$orangeTypEntity,$newShiftType); 

                if($result['status']==true){
                    $shiftapply->status='2';
                    if(!$shiftapply->save()){
                        throw new \Exception();
                    }
                }else{
                    throw new \Exception();
                }
            }

            $a = array();
            //审核表中插入新数据
            foreach ($sup_employees as $key => $sup_employee) {
                $approverTab=new ApproverTab;
                $approverdata['ApproverTab']['sup_employee']=$sup_employee;
                $approverdata['ApproverTab']['sub_employee']=$orange_emp;
                $approverdata['ApproverTab']['leave_id']='';
                $approverdata['ApproverTab']['shift_apply_id']=$shift_apply_id;
                $approverdata['ApproverTab']['overtime_id']='';
                $approverdata['ApproverTab']['attend_id']='';
                $approverdata['ApproverTab']['app_type']='4';
                $approverdata['ApproverTab']['chao_id']=$chaoId;
                $approverdata['ApproverTab']['chao_name']=$chaoName;
                $approverdata['ApproverTab']['witness_id']='';
                $approverdata['ApproverTab']['agree_employee']='';
                

                if($is_approval){
                    $status = '1';
                }else{
                    $status = '2';
                }

                $approverdata['ApproverTab']['status']=$status;

                if(!$approverTab->addApprover($approverdata)){
                    
                    throw new \Exception();
                }

                
                $approverTabId=$approverTab->getPrimaryKey();

                //创建消息
                $title='您收到一条调班申请';
                $targetType='4';
                $targetId=$approverTabId;
                $sendId=$login_emp;//发送人emp
                $receiverId=$sup_employee;//审批人emp
                $sendTimeStamp=time();
                $user_message_table=$approverTab->getMessageTable('user_message',$receiverId); //消息根据接受人emp判断插入具体哪一个表
                $user_message_detail_table=$approverTab->getMessageTable('user_message_detail',$receiverId);

                $empmodel=new Employee;
                $sender=$empmodel->getEmpByNum($sendId);
                $sender_name=$sender['emp_firstname'];
                
                $confirment=$empmodel->getEmpByNum($confirm_emp);
                $confirm_name=$confirment['emp_firstname'];
         
                $content=$sender_name.'申请与'.$confirm_name.'调班';
                $param = array();
                $param['type'] = 4;
                $param['approver'] = $sup_employee;
                $param['sendId'] = $sendId;
                $param['firsteHead'] = '您好，您有一条新审核提醒';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '调班申请';
                $param['footer'] = $sender_name.'申请与'.$confirm_name.'调班';
                $param['url'] = 'my-approval';



                if($is_approval){
                    $AppSys = new AppSys();
                   $AppSys->sendWeiXinNotice($param);
                }
            }


            /**
             * pan 5.17新加给抄送人发送消息
             */
            /*foreach($chao_arr as $v){
                $title = '您收到一条调班申请'; 
                $targetId = $targetId ;   //不管审核表有几条记录 只要有一个ID 就可以找到申请记录 所以这里可以填写 上面循环的 审核ID的其中一个ID就可以了
                $targetType = 4;
                $receiverId = $v;
                $content = $sender_name.'申请调班,请求查看';
                $connection = Yii::$app->db; 
                $sql="insert into $user_message_table (`title`, `targetType`,`targetId`,`sendId`, `receiverId`, `sendTimeStamp`) values('$title',$targetType,$targetId,$sendId,$receiverId,$sendTimeStamp)";
                $command = $connection->createCommand($sql)->execute(); 
                $messageId=$connection->getLastInsertID();
                $sql2="insert into $user_message_detail_table (`messageId`, `content`) values($messageId,'$content')";
                $command = $connection->createCommand($sql2)->execute(); 
                $param = array();
                $param['type'] = 4;
                $param['approver'] = $v;
                $param['sendId'] = $sendId;
                $param['firsteHead'] = '您好，您收到一条调班申请抄送';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '调班申请';
                $param['footer'] = $sender_name.'申请调班';
                $param['url'] = 'news';
                $a[] =httpPostByYii($param);
            }*/
            $transaction->commit();

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "成功提交申请";
            return $a;
            
        } catch (\Exception $e) {

            $transaction->rollback();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = isset($result['message'])?$result['message']:"提交审核失败";
        }

    }


    /**
     * @SWG\Post(path="/web-chat-shift/shift",
     *     tags={"微信-shift-调班申请"},
     *     summary="查询某个人的班次信息",
     *     description="查询某个人的班次信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "current_date",
     *        description = "当前日期",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回班次列表"
     *     ),
     * )
     *
    **/
    public function actionShift()
    {
        
        $post=Yii::$app->request->post();
        $start_date = $post['current_date'];
        $start_date=strtotime($start_date);
        $start_date=date('Y-m-d',$start_date);
        $empNum=$this->empNumber;
        $resultmodel=new ShiftResult;

        $confirmmodel=new ShiftResultConfirm;
        $empmodel=new Employee;
        $shiftordermodel=new ShiftOrderBy;
        $typedetailmodel=new ShiftTypeDetail;
        $schedulemodel=new Schedule;
        $typemodel=new ShiftType;
        $data=array();
        $data_no=array();
        $shift_cur=array();

        if(!strtotime($start_date)){
          
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "不是正确的时间格式";
            return [];
        }
        $data_to=date("Y-m-d",strtotime("+21 day",strtotime($start_date)));//未来一周的日期
        $data_dur=prDates($start_date,$data_to);//中间间隔的日期
        $shiftList = $resultmodel->getRosterResultAllByEmpAndDate($empNum,$start_date,$data_to);//获取员工排班
        $emp_array=array();

        //获取所在组
        $work_station=$this->workStation;

        $shiftType=$typemodel->getShifType($work_station);

        $shiftType=array_column($shiftType, NULL, 'id');

        
        if(null!=$shiftList){
            foreach ($shiftList as $key => $shift) {
            
                $schedule_id=$shift['schedule_id'];
                $date=$shift['shift_date'];
                $shiftTypeDetail=$typedetailmodel->getShitReslutFromTemp($empNum,$shift['shift_date']);
                $weekDay=get_week($date);
                $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');
                
                $weekDay=$weekArr[get_week($shift['shift_date'])];

                $cur=$confirmmodel->getConfrimById($shift,$work_station);

                if($shift['shift_date']==$start_date){
                    
                    //如果存在两个半天班情况
                    $shift_cur[$key]['emp_number']=$empNum;
                    $shift_cur[$key]['date']=$date;
                    $shift_cur[$key]['scheduleId']=$schedule_id;
                    $shift_cur[$key]['week']=$weekDay;
                    $shift_cur[$key]['name']=$cur['name'];
                    $shift_cur[$key]['hour_count']=0;
                    $shift_cur[$key]['min_count']=0;
                    if($cur['timeformat']['first']['type_id'] !=NULL &&$cur['timeformat']['first']['type_id']==$cur['timeformat']['second']['type_id']){
                        if(isset($cur['timeformat']['first']['type_id'])){
                            $shift_cur[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                            $shift_cur[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                            $shift_cur[$key]['time'][0]['end_time']=$cur['timeformat']['second']['end_time'];
                            $shift_cur[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['second']['end_time'];
                            $shift_cur[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                        }

                    }else if($cur['timeformat']['second']['type_id'] !=NULL &&$cur['timeformat']['second']['type_id']==$cur['timeformat']['third']['type_id']){
                        if(isset($cur['timeformat']['second']['type_id'])){
                            $shift_cur[$key]['time'][1]['id']=$cur['timeformat']['second']['mark'];
                            $shift_cur[$key]['time'][1]['start_time']=$cur['timeformat']['second']['start_time'];
                            $shift_cur[$key]['time'][1]['end_time']=$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][1]['time_combine']=$cur['timeformat']['second']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][1]['type_name']=$cur['timeformat']['second']['name'];
                        }

                    }else if($cur['timeformat']['first']['type_id'] !=NULL && $cur['timeformat']['first']['type_id']==$cur['timeformat']['third']['type_id']){

                        if(isset($cur['timeformat']['first']['type_id'])){
                            $shift_cur[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                            $shift_cur[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                            $shift_cur[$key]['time'][0]['end_time']=$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                        }

                    }else if($cur['timeformat']['first']['type_id'] !=NULL &&$cur['timeformat']['first']['type_id']==$cur['timeformat']['second']['type_id'] && $cur['timeformat']['third']['type_id']==$cur['timeformat']['second']['type_id']){

                        $shift_cur[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                        $shift_cur[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                        $shift_cur[$key]['time'][0]['end_time']=$cur['timeformat']['third']['end_time'];
                        $shift_cur[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                        $shift_cur[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];

                    }else if ($cur['timeformat']['first']['type_id']==NULL&&$cur['timeformat']['second']['type_id']==NULL&&$cur['timeformat']['third']['type_id']==NULL){
                            $shift_cur[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                            $shift_cur[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                            $shift_cur[$key]['time'][0]['end_time']=$cur['timeformat']['first']['end_time'];
                            $shift_cur[$key]['time'][0]['time_combine']='';
                            $shift_cur[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                        
                    }else{
                        if(isset($cur['timeformat']['first']['type_id'])){
                            $shift_cur[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                            $shift_cur[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                            $shift_cur[$key]['time'][0]['end_time']=$cur['timeformat']['first']['end_time'];
                            $shift_cur[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['first']['end_time'];
                            $shift_cur[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                        }

                        if(isset($cur['timeformat']['second']['type_id'])){

                            $shift_cur[$key]['time'][1]['id']=$cur['timeformat']['second']['mark'];
                            $shift_cur[$key]['time'][1]['start_time']=$cur['timeformat']['second']['start_time'];
                            $shift_cur[$key]['time'][1]['end_time']=$cur['timeformat']['second']['end_time'];
                            $shift_cur[$key]['time'][1]['time_combine']=$cur['timeformat']['second']['start_time'].'-'.$cur['timeformat']['second']['end_time'];
                            $shift_cur[$key]['time'][1]['type_name']=$cur['timeformat']['second']['name'];
                        }


                        if(isset($cur['timeformat']['third']['type_id'])){
                            $shift_cur[$key]['time'][2]['id']=$cur['timeformat']['third']['mark'];
                            $shift_cur[$key]['time'][2]['start_time']=$cur['timeformat']['third']['start_time'];
                            $shift_cur[$key]['time'][2]['end_time']=$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][2]['time_combine']=$cur['timeformat']['third']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                            $shift_cur[$key]['time'][2]['type_name']=$cur['timeformat']['third']['name'];
                        }
                    }

                }else{
                   
                        $emp_array[$key]['emp_number']=$empNum;
                        $emp_array[$key]['date']=$date;
                        $emp_array[$key]['scheduleId']=$schedule_id;
                        $emp_array[$key]['week']=$weekDay;
                        $emp_array[$key]['name']=$cur['name'];
                        $emp_array[$key]['hour_count']=0;
                        $emp_array[$key]['min_count']=0;

                        if($cur['timeformat']['first']['type_id'] !=NULL &&$cur['timeformat']['first']['type_id']==$cur['timeformat']['second']['type_id']){
                            if(isset($cur['timeformat']['first']['type_id'])){
                                $emp_array[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                                $emp_array[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                                $emp_array[$key]['time'][0]['end_time']=$cur['timeformat']['second']['end_time'];
                                $emp_array[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['second']['end_time'];
                                $emp_array[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                            }

                        }else if($cur['timeformat']['second']['type_id'] !=NULL &&$cur['timeformat']['second']['type_id']==$cur['timeformat']['third']['type_id']){
                            if(isset($cur['timeformat']['second']['type_id'])){
                                $emp_array[$key]['time'][1]['id']=$cur['timeformat']['second']['mark'];
                                $emp_array[$key]['time'][1]['start_time']=$cur['timeformat']['second']['start_time'];
                                $emp_array[$key]['time'][1]['end_time']=$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][1]['time_combine']=$cur['timeformat']['second']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][1]['type_name']=$cur['timeformat']['second']['name'];
                            }

                        }else if($cur['timeformat']['first']['type_id'] !=NULL && $cur['timeformat']['first']['type_id']==$cur['timeformat']['third']['type_id']){

                            if(isset($cur['timeformat']['first']['type_id'])){
                                $emp_array[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                                $emp_array[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                                $emp_array[$key]['time'][0]['end_time']=$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                            }

                        }else if($cur['timeformat']['first']['type_id'] !=NULL &&$cur['timeformat']['first']['type_id']==$cur['timeformat']['second']['type_id'] && $cur['timeformat']['third']['type_id']==$cur['timeformat']['second']['type_id']){

                            $emp_array[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                            $emp_array[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                            $emp_array[$key]['time'][0]['end_time']=$cur['timeformat']['third']['end_time'];
                            $emp_array[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                            $emp_array[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];

                        }else if ($cur['timeformat']['first']['type_id']==NULL&&$cur['timeformat']['second']['type_id']==NULL&&$cur['timeformat']['third']['type_id']==NULL){
                                $emp_array[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                                $emp_array[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                                $emp_array[$key]['time'][0]['end_time']=$cur['timeformat']['first']['end_time'];
                                $emp_array[$key]['time'][0]['time_combine']='';
                                $emp_array[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                            
                        }else{
                            if(isset($cur['timeformat']['first']['type_id'])){
                                $emp_array[$key]['time'][0]['id']=$cur['timeformat']['first']['mark'];
                                $emp_array[$key]['time'][0]['start_time']=$cur['timeformat']['first']['start_time'];
                                $emp_array[$key]['time'][0]['end_time']=$cur['timeformat']['first']['end_time'];
                                $emp_array[$key]['time'][0]['time_combine']=$cur['timeformat']['first']['start_time'].'-'.$cur['timeformat']['first']['end_time'];
                                $emp_array[$key]['time'][0]['type_name']=$cur['timeformat']['first']['name'];
                            }

                            if(isset($cur['timeformat']['second']['type_id'])){

                                $emp_array[$key]['time'][1]['id']=$cur['timeformat']['second']['mark'];
                                $emp_array[$key]['time'][1]['start_time']=$cur['timeformat']['second']['start_time'];
                                $emp_array[$key]['time'][1]['end_time']=$cur['timeformat']['second']['end_time'];
                                $emp_array[$key]['time'][1]['time_combine']=$cur['timeformat']['second']['start_time'].'-'.$cur['timeformat']['second']['end_time'];
                                $emp_array[$key]['time'][1]['type_name']=$cur['timeformat']['second']['name'];
                            }


                            if(isset($cur['timeformat']['third']['type_id'])){
                                $emp_array[$key]['time'][2]['id']=$cur['timeformat']['third']['mark'];
                                $emp_array[$key]['time'][2]['start_time']=$cur['timeformat']['third']['start_time'];
                                $emp_array[$key]['time'][2]['end_time']=$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][2]['time_combine']=$cur['timeformat']['third']['start_time'].'-'.$cur['timeformat']['third']['end_time'];
                                $emp_array[$key]['time'][2]['type_name']=$cur['timeformat']['third']['name'];
                            }
                        }
                }
                
            }

        
            $data=array_values($emp_array);
            $shift_cur=array_values($shift_cur);
            array_multisort(array_column($data,'date'),SORT_ASC,$data);

            $result['cur_date'] = $shift_cur;
            $result['shiftdata'] = $data;

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";

            return $result;

        }else{

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "期间没有排班信息";
            return [];
        }
        
    }
    /**
     * @SWG\Post(path="/web-chat-shift/get-all-shift",
     *     tags={"微信-shift-调班申请"},
     *     summary="获取周排班表",
     *     description="获取周排班表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回班次列表"
     *     ),
     * )
     *
    **/
    public function actionGetAllShift(){

        $empNum = $this->empNumber; 
        $start_date =date('Y-m-d'); //选择的日期
        $shiftdatemodel=new ShiftDate;
        $resultmodel=new ShiftResult;
        $typemodel=new ShiftType;
        $ordermodel=new ShiftOrderBy;
        $confirmmodel = new ShiftResultConfirm;
        $employeemodel=new Employee;
        $leaveenmodel=new LeaveEntitlement;
        $detailmodel=new ShiftTypeDetail;
        //判断该日期在那个计划中
        //获取所在组
        $workStation=$this->workStation;
        $shiftDate=$shiftdatemodel->getShiftDateListByStation($workStation,$start_date);

        $scheduleID=array();
        //查询当周的是否有排班计划
        $scheduleID=array_column( $shiftDate, 'schedule_id');
        $scheduleID=array_unique($scheduleID);

        if(count($scheduleID)==0){

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "该时间范围内没有班次安排";
            return [];
        }
        $assignment_list=$resultmodel->getShiftsByScheduleList($scheduleID);
        $date_list_last=$shiftdatemodel->getShiftDateLists($scheduleID);

        $emp_new=$ordermodel->getShiftOrderByAndEmp($scheduleID[0]);
        $employ_list=array_column($emp_new, 'emp_number');
 

        if(count($emp_new)>0){
            $employeeList=$employ_list;
        }else{
            $employeeList = $employeemodel->getEmpByWorkStation($workStation);
            $employ_list = array_column($employeeList, NULL, 'empNumber');
        }
 

        foreach ($assignment_list as $ks1 => $va1) {

            if($va1['shift_type_id']>0){
                $shift_on_result[$ks1]=$va1;
            }
            if($va1['leave_type']==1){
                $new_leave['holday'][$ks1]=$va1;
            }

            if($va1['leave_type']==2){
                $new_leave['halfleave'][$ks1]=$va1;
            }

            if($va1['rest_type']==1){
                $new_rest['one'][$ks1]=$va1;
            }

            if($va1['rest_type']==2){
                $new_rest['half'][$ks1]=$va1;
            }


            if($va1['rest_type']>0){
                 $restall[$va1['emp_number']][$ks1]=$va1;
            }
            
        }

        $date_list=array_column($date_list_last, 'shift_date');
        //列出每一个员工的所有天的排班
        $employee_array=array();
        foreach ($assignment_list as $k => $assignment) {
            $employee=$assignment['emp_number'];

            $employee_array[$employee][]=$assignment;
        }

        $employee_array=array_replace(array_flip($employ_list), $employee_array);
        $emarray=array();

        $emarray=array();
        $last=array();

        foreach ($employee_array as $key => $employee) {
            $i=0;
            $leavid='';
            $emarray[$key]['empnum']=$key;
           
            if(!isset($emp_new_all[$key])){
                $emp_new_all[$key]=$employeemodel->getEmpByNum($key);
            }
            $emarray[$key]['name']=$emp_new_all[$key]['emp_firstname'];

            foreach ($date_list as $ked => $date) {
                if(empty($date)){
                    $emarray[$key][$ked]='';
                }else{
                    $column_name='col'.$ked;
                    if(is_array($employee)){
                        foreach ($employee as $ks => $emday) {
                            $if_skill_ok=array();
                            $skilllist=array();
                            $type_name='';
                            $errSkillMes='';
                            $if_have_leave='';
                            if(isset($emday['shift_date'])&&$date==$emday['shift_date']){
                                $enetity=$confirmmodel->getConfrimById($emday,$workStation);
                                $emarray[$key][$column_name]=$enetity['name'];

                            }
                        }
                    }else{
                        $emarray[$key][$column_name]='';
                    }
                    
                }

            }
            ksort($emarray[$key]);
            $i++;
        }

        $emarray=array_values($emarray);

        $i=1;
        foreach ($emarray as $kv => $vv) {
            $vv['id']=$i;
            $emarray_new[$kv]=$vv;
            $i++;
        }


        foreach ($date_list as $key_date => $value_date) {
            $col_name='col'.$key_date;
            $week=get_week($value_date);
            $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');
            $week_1=$weekArr[$week];
            $dateApi[$key_date]['field']=$col_name;
            $dateApi[$key_date]['title']=$value_date.'('.$week_1.')';
        }


        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "成功";
        $result['tableData'] = $emarray_new;
        $result['columns'] = $dateApi;
        return $result;

    }

}