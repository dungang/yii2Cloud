<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\curriculum\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_curriculum_empfile".
 *
 * @property string $id
 * @property string $file_url
 * @property string $file_name
 * @property integer $cur_id
 * @property integer $emp_number
 * @property integer $cur_emp_id
 * @property string $aliasModel
 */
abstract class CurriculumEmpfile extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_curriculum_empfile';
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
            /*[['cur_id', 'emp_number', 'cur_emp_id'], 'integer'],
            [['file_url'], 'string', 'max' => 100],
            [['file_name'], 'string', 'max' => 200]*/
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_url' => 'File Url',
            'file_name' => 'File Name',
            'cur_id' => 'Cur ID',
            'emp_number' => 'Emp Number',
            'cur_emp_id' => 'Cur Emp ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'file_url' => '文件途径',
            'cur_id' => '课程id',
            'emp_number' => '员工id',
        ]);
    }




}