<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 11.06.2018
 * Time: 8:30
 */

namespace yozh\userworkactivity\models;

use common\models\User;
use yozh\crud\models\BaseActiveRecord as ActiveRecord;

class LogUserWorkActivity extends ActiveRecord
{
	const GAP_TIMEf = 5; // time between last calculated and next activity of user
	
	public static function tableName()
	{
		return '{{%yozh_log_user_work_activity}}';
	}
	
	public function rules( $rules = [], $update = false )
	{
		static $_rules;
		
		if( !$_rules || $update ) {
			
			$_rules = parent::rules( \yozh\base\components\validators\Validator::merge( [
				
				[ [ 'url', 'route', 'user_id', ], 'required' ],
				[ [ 'user_id' ], 'integer' ],
				[ [ 'url' ], 'string', 'max' => 1023 ],
				[ [ 'route' ], 'string', 'max' => 255 ],
			
			], $rules ) );
			
		}
		
		return $_rules;
		
	}
	
	public function getUser()
	{
		return $this->hasOne( User::class, [ 'id' => 'user_id' ] );
	}
	
	
}