<?php

namespace common\models\user;

use Yii;
use \common\models\user\base\Piction as BasePiction;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_picture".
 */
class Piction extends BasePiction
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
}
