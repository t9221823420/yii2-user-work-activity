<?php

/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 11.06.2018
 * Time: 8:19
 */

use yozh\base\components\db\Migration;
use yozh\base\components\db\Schema;
use yozh\base\components\helpers\ArrayHelper;
use yozh\settings\models\Settings;
use yozh\userworkactivity\models\LogUserWorkActivity;

class m000000_000000_000_log_user_work_activity_table_dev extends Migration
{
	protected static $_table;
	
	public function __construct( array $config = [] ) {
		
		static::$_table = static::$_table ?? LogUserWorkActivity::getRawTableName();
		
		parent::__construct( $config );
		
	}
	
	public function safeUp( $params = [] )
	{
		
		Settings::addSystemParam( LogUserWorkActivity::class . '::GAP_TIME', 5 );
		
		parent::safeUp( [
			'mode' => 1 ? static::ALTER_MODE_UPDATE : static::ALTER_MODE_IGNORE,
		] );
		
	}
	
	public function getColumns( $columns = [] )
	{
		
		return parent::getColumns( [
			//'id' => $this->primaryKey(),
			
			'url'       => $this->string( 1023 ),
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