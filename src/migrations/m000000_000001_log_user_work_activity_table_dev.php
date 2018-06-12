<?php

/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 11.06.2018
 * Time: 8:19
 */

use yozh\base\components\db\Migration;
use yozh\base\components\db\Schema;
use yozh\base\components\utils\ArrayHelper;

class m000000_000001_log_user_work_activity_table_dev extends Migration
{
	protected static $_table = 'log_user_work_activity';
	
	public function safeUp( $params = [] )
	{
		
		parent::safeUp( [
			'mode' => 1 ? self::ALTER_MODE_UPDATE : self::ALTER_MODE_IGNORE,
		] );
		
	}
	
	public function getColumns( $columns = [] )
	{
		
		return parent::getColumns( [
			//'id' => $this->primaryKey(),
			
			'url'       => $this->string(1023),
			'route'     => $this->string(),
			'timestamp' => $this->timestamp(),
			'user_id'   => $this->integer(),
		] );
	}
	
	public function getReferences( $references = [] )
	{
		return ArrayHelper::merge( [
			
			[
				'refTable'   => \common\models\User::tableName(),
				'refColumns' => 'id',
				'columns'    => 'user_id',
				//'onDelete'   => self::CONSTRAINTS_ACTION_RESTRICT,
			],
		
		], $references );
	}
}