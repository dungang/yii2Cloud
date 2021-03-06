<?php

namespace common\models\attendance;

use Yii;
use \common\models\attendance\base\AttendanceRecord as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;

use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\shift\ShiftTypeDetail;
use common\models\overtime\Overtime;

/**
 * This is the model class for table "ohrm_attendance_record".
 */
class AttendanceRecord extends BaseAttendanceRecord
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }

    public function getAttendanceRecord($empNumber,$date,$iszheng = 1){
        if(!empty($date)){
            $from = $date . " " . "00:" . "00:" . "00";
            $end = $date . " " . "23:" . "59:" . "59";
        }

         $query = self::find();

         $query->andWhere("employee_id = :empNumber",[':empNumber'=>$empNumber]);

         $query->andWhere("first_daka_time >= :from",[':from'=>$from]);
         $query->andWhere("first_daka_time <= :end",[':end'=>$end]);

         if($iszheng==1){
            $query->andWhere("is_in_status = 0");
         }else{
            $query->andWhere("is_in_status > 0");
         }
         
         $query->andWhere("is_out_status = 0");

         $list = $query->one();

         return $list;

    }

    public function getAttendanceRecordByOver($empNumber,$date){
        if(!empty($date)){
            $from = $date . " " . "00:" . "00:" . "00";
            $end = $date . " " . "23:" . "59:" . "59";
        }

         $query = self::find();

         $query->andWhere("employee_id = :empNumber",[':empNumber'=>$empNumber]);

         $query->andWhere("first_daka_time >= :from",[':from'=>$from]);
         $query->andWhere("first_daka_time <= :end",[':end'=>$end]);

         $query->andWhere("is_in_status = -1");
         $query->andWhere("is_out_status = 0");

         $list = $query->one();

         return $list;

    }

    /*
     * 获取用户班次信息   
     */
    public function getEmployeeWorkDetail($search){

        $query = ShiftResult::find();
        $query->joinWith('schedule');
        //$query->joinWith('shiftType');

        if(!empty($search['empNumber'])){
            $query->andWhere('ohrm_work_shift_result.emp_number = :empNumber',[':empNumber'=>$search['empNumber']]);
        }
        if(!empty($search['date'])){
            $query->andWhere("ohrm_work_shift_result.shift_date = :date",[':date'=>$search['date']]);
        }

        if(!empty($search['id'])){
            $query->andWhere("ohrm_work_shift_result.id = :id",[':id'=>$search['id']]);
        }

         $query->andWhere('ohrm_work_schedule.is_show = 1');
         $query->andWhere('ohrm_work_schedule.is_confirm = 1');
        

        $list = $query->asArray()->one();
        return $list;
    }
    /**
     * 获取换班信息
     * @param  [type] $search [description]
     * @return [type]         [description]
     */
    public function getHuanWorkshift($search){
        $query = ShiftTypeDetail::find();
        $query->joinWith('shiftType');
        if(!empty($search['empNumber'])){
            $query->andWhere('ohrm_work_shift_type_detail.emp_number = :empNumber',[':empNumber'=>$search['empNumber']]);
        }
        if(!empty($search['date'])){
            $query->andWhere("ohrm_work_shift_type_detail.shift_date = :date",[':date'=>$search['date']]);
        }
        $list = $query->asArray()->all();

        return $list;
    }
    /**
     * 根据日期获取员工的班次时间
     * @param  [type] $empNumber [description]
     * @param  [type] $date      [description]
     * @return [type]            [description]
     */
    public function getWorkShiftByDate($empNumber,$date){
        $work_start_time = null;
        $work_middend_time = null;
        $work_middstart_time = null;
        $work_end_time = null;
        $work_thirdstart_time = null;
        $work_thirdend_time = null;

        $work_date = null;
        $wname = '';
        $shiftId =null ; 
        $remark = '';
        $is_amont_work = 0;

        $is_daka_half = 0;
        $clock_in = 0;

        $noeDate = $date; 
        $isWork = true;  //是否有班次 
        $isJia = false; // 是否加班
        $overStatTime = null;
        $overStat_time = null;
        $overEnd_time = null;
        $overName = null;
        $over_date = null;

        $arr = array('empNumber'=>$empNumber,'date'=>$noeDate);
        //$arr = array('empNumber'=>736,'date'=>'2018-06-06');
        //表结构修改了 不用换班表了
        
        //#################表结构变了 下面重写
        $arr = array('empNumber'=>$empNumber,'date'=>$noeDate);
        $shift = $this->getEmployeeWorkDetail($arr);

        if($shift){
            if($shift['frist_type_id']||$shift['second_type_id']||$shift['third_type_id']){
                $date = $noeDate;
            }else{   //查询夜班
                $date = date('Y-m-d',strtotime('-1 days',strtotime($noeDate)));
                $arr = array('empNumber'=>$empNumber,'date'=>$date);
                $shift = $this->getEmployeeWorkDetail($arr);
            }

        }else{
            $date = date('Y-m-d',strtotime('-1 days',strtotime($noeDate)));
            $arr = array('empNumber'=>$empNumber,'date'=>$date);
            $shift = $this->getEmployeeWorkDetail($arr);         
        }

        if(!empty($shift)){
            if(empty($shift['frist_type_id'])&&empty($shift['second_type_id'])&&empty($shift['third_type_id'])){
                $isWork = false;
                $date = $noeDate;
            }else{
                
                $shiftId = $shift['id'];
                $firstWorkTime = $shift['shift_date'];

                $shiftTypeId = 0;
                if($shift['frist_type_id']){
                    $shiftTypeId = $shift['frist_type_id'];
                    $shiftType = $this->getShiftTypeById($shift['frist_type_id']);

                    if($shift['shift_date']!=$noeDate&&$shiftType->is_night_shift!=1){
                        $isWork = false;
                        $date = $noeDate;
                    }else{

                        $work_start_time =$shiftType->start_time;
                        $work_middend_time =$shiftType->end_time_afternoon;
                        $wname = $shiftType->name;

                        if(!empty($work_middend_time)&&$work_middend_time!='00:00:00'){
                            $overStatTime= $work_middend_time;
                        }
                        
                        $work_start_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->start_time));
                        $work_middend_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->end_time_afternoon));

                        if($shiftType->is_daka_half){
                            $is_daka_half = 1 ;
                        }
                        if($shiftType->clock_in){
                            $clock_in = 1 ;
                        }
                    }
                }

                if($shift['second_type_id']){
                    $shiftType = $this->getShiftTypeById($shift['second_type_id']);
                    if($shift['shift_date']!=$noeDate&&$shiftType->is_night_shift!=1){
                        $isWork = false;
                        $date = $noeDate;
                    }else{

                        $work_middstart_time =$shiftType->start_time_afternoon;
                        $work_end_time =$shiftType->end_time;

                        if($shiftTypeId!=$shift['second_type_id']){
                            $wname .= '/'.$shiftType->name;
                        }
                        
                        $shiftTypeId = $shift['second_type_id'];

                        if(!empty($work_end_time)&&$work_end_time!='00:00:00'){
                            $overStatTime= $work_end_time;
                        }
                        $work_middstart_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->start_time_afternoon));
                        $work_end_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->end_time));
                        if($shiftType->is_daka_half){
                            $is_daka_half = 1 ;
                        }
                        if($shiftType->clock_in){
                            $clock_in = 1 ;
                        }
                    }
                }

                if($shift['third_type_id']){
                    $shiftType = $this->getShiftTypeById($shift['third_type_id']);
                    if($shift['shift_date']!=$noeDate&&$shiftType->is_night_shift!=1){
                        $isWork = false;
                        $date = $noeDate;
                    }else{

                        $work_thirdstart_time =$shiftType->time_start_third;
                        $work_thirdend_time =$shiftType->time_end_third;
                        if($shiftTypeId!=$shift['third_type_id']){
                            $wname .= '/'.$shiftType->name;
                        }
                        
                        $shiftTypeId = $shift['third_type_id'];
                        
                        if(!empty($work_thirdend_time)&&$work_thirdend_time!='00:00:00'){
                            $overStatTime= $work_thirdend_time;
                        }
                        $work_thirdstart_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->time_start_third));
                        $work_thirdend_time = date('H:i',strtotime($shift['shift_date'].' '.$shiftType->time_end_third));
                        if($shiftType->is_daka_half){
                            $is_daka_half = 1 ;
                        }
                        if($shiftType->clock_in){
                            $clock_in = 1 ;
                        }
                    }
                }  
            }
        }else{
            $isWork = false;
            //return false;
        }
            
        

        //判断是否有加班
        $Overtime = new Overtime();

        $status = 2;
        $over = $Overtime->getOvertimeByDate($empNumber,$noeDate,$status,$overStatTime);

        if($over){
            $isJia = true;

            $overStat_time = $over->stat_time;
            $overEnd_time = $over->end_time;
            $over_date = $over->current_day;

            $overStat_time = date('H:i',strtotime($over_date.' '.$overStat_time));
            $overEnd_time = date('H:i',strtotime($over_date.' '.$overEnd_time));
            $overName = '加班';
        }else{
            if(!$isWork){
                return false;
            }
        }

        //end

        $back['isWork'] = $isWork;
        $back['isJia'] = $isJia;

        $back['overStat_time'] = $overStat_time;
        $back['overEnd_time'] = $overEnd_time;
        $back['overName'] = $overName;
        $back['over_date'] = $over_date;

        $back['work_start_time'] = $work_start_time;
        $back['work_middend_time'] = $work_middend_time;
        $back['work_middstart_time'] = $work_middstart_time;
        $back['work_end_time'] = $work_end_time;

        $back['work_thirdstart_time'] = $work_thirdstart_time;
        $back['work_thirdend_time'] = $work_thirdend_time;

        $back['work_date'] = $date;
        $back['work_name'] = trim($wname,'/');
        $back['is_daka_half'] = $is_daka_half;
        $back['clock_in'] = $clock_in;
        $back['shiftId'] = $shiftId;

        $back['remark'] = trim($remark,'/');
        $back['is_amont_work'] = $is_amont_work;
        return $back ;
    }
    /**
     * 根据ID 获取打卡信息
     * @param  [type] $empNumber [description]
     * @param  [type] $date      [description]
     * @return [type]            [description]
     */
    public function getAttendanceRecordById($id){
        
         $query = self::find();

         $query->where("id = :id",[':id'=>$id]);

         $list = $query->one();

         return $list;

    }

    /**
     * 给吴斌写的函数 查找员工当天的打卡情况并返回可以调班
     * @param  [type] $employeeId [description]
     * @param  [type] $date       [description]
     * @return  -1 不能调班 0全天可以调班 1上午可以调班 2下午可以调班         
     */
    public function getAttendanceRecordByWB($employeeId, $date,$iszheng=1) {

        $nowTime = date('Y-m-d');
        if($nowTime<$date){
            return 0;
        }

        if(!empty($date)){
            $from = $date . " " . "00:" . "00:" . "00";
            $end = $date . " " . "23:" . "59:" . "59";
        }

        $query = self::find();
        $query->where('employee_id = :employeeId',[':employeeId'=>$employeeId]);

        if(!empty($date)){
            $query->andWhere("first_daka_time >=:from ",[':from'=>$from]);
            $query->andWhere("first_daka_time <=:end ",[':end'=>$end]);
            //$query->andWhere("first_daka_time <= ?", $end);
        }

        if($iszheng==1){
            $query->andWhere('is_in_status = 0 '); 
        }else if($iszheng == 2){
            $query->andWhere('is_in_status > 0 '); 
        }
        $query->andWhere('is_out_status = 0 '); 
        $query->orderBy('first_daka_time desc');


              
        $records = $query->asArray()->all();

        if (empty($records)) {
            return 0;
        } else {
            foreach($records as $attr){
                if($attr['state']=='PUNCHED IN'){
                    if(empty($attr['punch_in_user_time'])){
                        return 1;
                    }else if(empty($attr['punch_out_user_time'])){
                        return 2;
                    }else{
                        return -1;
                    }
                }else{
                    return -1;
                }
            }
            
        }
    }


    public function searchAttendanceRecordsByAPI($arr){
        $empNumber = !empty($arr['empNumber'])?$arr['empNumber']:null;
        $queryType = isset($arr['queryType'])?$arr['queryType']:null;
        $is_in_status = !empty($arr['is_in_status'])?$arr['is_in_status']:0;
        $id = !empty($arr['id'])?$arr['id']:null;

        $q = AttendanceRecord::find();

        $q->joinWith("employee");
//                

        if( !empty($empNumber)){
            if(is_array($empNumber)){
                $q->amdWhere(['in',"ohrm_attendance_record.employee_id", $empNumber]);
            } else {
                $q->andWhere(" ohrm_attendance_record.employee_id =:empNumber",[':empNumber'=>$empNumber]);
            }            
        } 
        if(1||$is_in_status===0){
            $q->andWhere("ohrm_attendance_record.is_in_status != 0");
        }
  
        if(!is_null($queryType)){     
            if($queryType==0){
                $q->andWhere('ohrm_attendance_record.is_in_status = 1 ');
            }else if($arr['queryType']==-1){
                $q->andWhere('ohrm_attendance_record.is_out_status = -1 ');
            }else{
                $q->andWhere('ohrm_attendance_record.is_out_status = 0 ');
            }
            $q->andWhere("ohrm_attendance_record.is_pro = :queryType",[':queryType'=>$queryType]);
        }else{
            // $q->andWhere('a.punchIsOutStatus = 0 ');
        }

        if($id){
            if (is_numeric($id) && $id> 0) {
                $q->andWhere('ohrm_attendance_record.id = :id', [':id'=>$id]);
                $result = $q->one();
            }else if (is_array($id)) {
                $q->andWhere(['in','ohrm_attendance_record.id',$id]);
                $q->orderBy('ohrm_attendance_record.create_time DESC');
                $result = $q->all();
            }
            
        }else{
            $q->orderBy('ohrm_attendance_record.create_time DESC');
            $result = $q->all();
        }

        //echo $q->createCommand()->getRawSql();die;
        return $result;
      
    }

     /**
     * 根据ID 获取班次信息
     * @param  [type] $empNumber [description]
     * @param  [type] $date      [description]
     * @return [type]            [description]
     */
    public function getShiftTypeById($id){
        
         $query = ShiftType::find();

         $query->where("id = :id",[':id'=>$id]);

         $list = $query->one();

         return $list;

    }

    public function getPunchRecordByPunchedIn($employeeId,$date) {
        if($date){
            $statdate = $date.' 00:00:00';
            $enddate = $date.' 23:59:59';
        }

      
        $query = self::find();
        $query->where("employee_id = :employeeId",[':employeeId'=> $employeeId]);
        
        $query->andWhere('is_in_status = 0 ');
        $query->andWhere('is_out_status = 0 ');

        if($date){
            $query->andWhere('first_daka_time < :enddate',[':enddate'=>$enddate]);
            $query->andWhere('first_daka_time > :statdate',[':statdate'=>$statdate]);
        }
        $query->orderBy('first_daka_time DESC');
        $lastReocord = $query->one();

        return $lastReocord;

    }

    public function deleteAtteById($id) {
        $q = AttendanceRecord::deleteAll('id = :id ', [':id' => $id] );
        return $q;

    }


}
