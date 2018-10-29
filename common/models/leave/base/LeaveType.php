<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\leave\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_leave_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $deleted
 * @property integer $exclude_in_reports_if_no_entitlement
 * @property integer $operational_country_id
 * @property integer $leave_is_disable
 * @property integer $islimit
 * @property integer $orderid
 *
 * @property \common\models\OhrmLeave[] $ohrmLeaves
 * @property \common\models\OhrmLeaveAdjustment[] $ohrmLeaveAdjustments
 * @property \common\models\OhrmLeaveEntitlement[] $ohrmLeaveEntitlements
 * @property \common\models\OhrmLeaveRequest[] $ohrmLeaveRequests
 * @property \common\models\OhrmOperationalCountry $operationalCountry
 * @property string $aliasModel
 */
abstract class LeaveType extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_leave_type';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('orangehrm');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['operational_country_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['deleted', 'exclude_in_reports_if_no_entitlement', 'leave_is_disable', 'islimit'], 'string', 'max' => 1],
            [['orderid'], 'string', 'max' => 4],
            //[['operational_country_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\OhrmOperationalCountry::className(), 'targetAttribute' => ['operational_country_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'deleted' => 'Deleted',
            'exclude_in_reports_if_no_entitlement' => 'Exclude In Reports If No Entitlement',
            'operational_country_id' => 'Operational Country ID',
            'leave_is_disable' => 'Leave Is Disable',
            'islimit' => 'Islimit',
            'orderid' => 'Orderid',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'leave_is_disable' => '是否禁用 1禁用 0否',
            'islimit' => '1有限 0无限',
            'orderid' => '扣假顺序',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOhrmLeaves()
    {
        return $this->hasMany(\common\models\leave\Leave::className(), ['leave_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOhrmLeaveAdjustments()
    {
        return $this->hasMany(\common\models\LeaveAdjustment::className(), ['leave_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOhrmLeaveEntitlements()
    {
        return $this->hasMany(\common\models\leave\LeaveEntitlement::className(), ['leave_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOhrmLeaveRequests()
    {
        return $this->hasMany(\common\models\leave\LeaveRequest::className(), ['leave_type_id' => 'id']);
    }

    // /**
    //  * @return \yii\db\ActiveQuery
    //  */
    // public function getOperationalCountry()
    // {
    //     return $this->hasOne(\common\models\OhrmOperationalCountry::className(), ['id' => 'operational_country_id']);
    // }




}