<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\thesis\base;

use Yii;

/**
 * This is the base-model class for table "hs_hr_thesis".
 *
 * @property integer $id
 * @property string $thesis_name
 * @property integer $first_author_type
 * @property string $first_author
 * @property string $first_author_unit
 * @property integer $corresponding_author_type
 * @property string $corresponding_author
 * @property string $corresponding_author_unit
 * @property string $publication
 * @property string $volume
 * @property string $issn
 * @property string $influence
 * @property string $url
 * @property integer $is_include
 * @property integer $thesis_type_id
 * @property integer $article_type_id
 * @property integer $publication_type_id
 * @property string $remarks
 * @property string $aliasModel
 */
abstract class Thesis extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hs_hr_thesis';
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
            [['thesis_type_id', 'article_type_id', 'publication_type_id'], 'integer'],
            [['remarks'], 'string'],
            [['thesis_name', 'volume', 'issn'], 'string', 'max' => 100],
            [['first_author_type', 'corresponding_author_type', 'is_include'], 'string', 'max' => 4],
            [['first_author', 'first_author_unit', 'corresponding_author', 'corresponding_author_unit', 'publication', 'influence', 'url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thesis_name' => 'Thesis Name',
            'first_author_type' => 'First Author Type',
            'first_author' => 'First Author',
            'first_author_unit' => 'First Author Unit',
            'corresponding_author_type' => 'Corresponding Author Type',
            'corresponding_author' => 'Corresponding Author',
            'corresponding_author_unit' => 'Corresponding Author Unit',
            'publication' => 'Publication',
            'volume' => 'Volume',
            'issn' => 'Issn',
            'influence' => 'Influence',
            'url' => 'Url',
            'is_include' => 'Is Include',
            'thesis_type_id' => 'Thesis Type ID',
            'article_type_id' => 'Article Type ID',
            'publication_type_id' => 'Publication Type ID',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'thesis_name' => '论文题目',
            'first_author_type' => '第一作者类型',
            'first_author' => '第一作者',
            'first_author_unit' => '第一作者单位',
            'corresponding_author_type' => '通讯作者类型',
            'corresponding_author' => '通讯作者',
            'corresponding_author_unit' => '通讯作者单位',
            'publication' => '发表的刊物、论文集',
            'volume' => '年、卷、期、页码',
            'issn' => 'issn号',
            'influence' => '影响',
            'url' => '网络连接',
            'is_include' => '是否收录',
            'thesis_type_id' => '论文类别id',
            'article_type_id' => '文章类型id',
            'publication_type_id' => '刊物类型id',
            'remarks' => '备注',
        ]);
    }




}
