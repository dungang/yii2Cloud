<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\performance\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_bonus_calculation".
 *
 * @property integer $id
 * @property integer $bonus_id
 * @property string $customerId
 * @property string $bonusDate
 * @property integer $groupId
 * @property integer $status
 * @property string $sheetName
 * @property string $receiveTime
 * @property string $submitTime
 * @property string $aliasModel
 */
abstract class BonusCalculation extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_bonus_calculation';
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
            [['bonus_id', 'groupId'], 'integer'],
            [['bonusDate', 'receiveTime', 'submitTime','status'], 'safe'],
            [['customerId'], 'string', 'max' => 20],
            [['sheetName'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bonus_id' => 'Bonus ID',
            'customerId' => 'Customer ID',
            'bonusDate' => 'Bonus Date',
            'groupId' => 'Group ID',
            'status' => 'Status',
            'sheetName' => 'Sheet Name',
            'receiveTime' => 'Receive Time',
            'submitTime' => 'Submit Time',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'bonus_id' => '关联ohrm_bonus_calculation_manage 表 id',
            'customerId' => '客户id',
            'bonusDate' => '日期',
            'groupId' => '组id',
            'status' => '状态 0默认1已上报',
            'sheetName' => 'excel名',
            'receiveTime' => '接收时间',
            'submitTime' => '上报时间',
        ]);
    }

    public function getSubunit()
    {
        return $this->hasOne(\common\models\subunit\Subunit::className(), ['id' => 'groupId']);
    }


}