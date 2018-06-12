<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 11.06.2018
 * Time: 8:30
 */

namespace yozh\userworkactivity\models;

use common\models\User;
use yozh\crud\models\BaseModel as ActiveRecord;

class LogUserWorkActivity extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%log_user_work_activity}}';
	}
	
	public function rules()
	{
		return [
			[ [ 'url', 'route', 'user_id', ], 'required' ],
			[ [ 'user_id' ], 'integer' ],
			[ [ 'url' ], 'string', 'max' => 1023 ],
			[ [ 'route' ], 'string', 'max' => 255 ],
		
		];
	}
	
	public function getUser()
	{
		return $this->hasOne( User::class, [ 'id' => 'user_id' ] );
	}
	
	
}