<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\hold\base;

use Yii;

/**
 * This is the base-model class for table "hs_hr_hold".
 *
 * @property integer $id
 * @property integer $emp_number
 * @property string $society
 * @property string $job
 * @property string $term
 * @property string $start_time
 * @property string $remark
 * @property string $aliasModel
 */
abstract class Hold extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hs_hr_hold';
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
            [['emp_number'], 'integer'],
            [['start_time'], 'safe'],
            [['society', 'job', 'remark'], 'string', 'max' => 255],
            [['term'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_number' => 'Emp Number',
            'society' => 'Society',
            'job' => 'Job',
            'term' => 'Term',
            'start_time' => 'Start Time',
            'remark' => 'Remark',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'society' => '学会名称',
            'job' => '职务',
            'term' => '任期',
            'start_time' => '开始时间',
            'remark' => '备注',
        ]);
    }




}