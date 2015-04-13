<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class Album
 * @package common\models
 */
class UserProduct extends ActiveRecord
{
	public function getProduct()
	{
		return $this
			->hasOne('\common\models\Product', ['id' => 'id_product']);
	}
}