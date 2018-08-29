<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 11.06.2018
 * Time: 9:14
 */

namespace yozh\userworkactivity\models;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yozh\base\interfaces\models\ActiveRecordSearchInterface;


class LogUserWorkActivitySearch extends LogUserWorkActivity implements ActiveRecordSearchInterface
{
    // public $filter_search;
	// public $filter_relation_title;
	public $filter_dateFrom;
	public $filter_dateTo;
	
	public function rules()
	{
		return [
		    //[ [ 'filter_search', ], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process' ],
			 [ [ 'user_id', ], 'integer' ], // from parent
			// [ [ 'title', ], 'string' ], // from parent
			[ [ 'filter_dateFrom', 'filter_dateTo', ], 'date', 'format' => 'php:d.m.Y' ],
			// [ [ 'filter_relation_title', ], 'string' ],
			// [ [ 'filter_dateFrom', 'filter_dateTo', ], 'date', 'format' => 'php:d.m.Y' ],
		];
	}
	
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}
	
	/**
	 * @param array $params
	 * @return ActiveDataProvider
	 */
	public function search( $params )
	{
		
		$tableName_LogUserWorkActivity = static::getRawTableName();
		$tableName_User                = User::getRawTableName();
		
		/**
		 * @var $query ActiveQuery
		 */
		$query = parent::find()
			->select( "$tableName_LogUserWorkActivity.*, $tableName_User.username, $tableName_User.email" )
			->joinWith( 'user' )
			//->from( self::tableName() . ' selfAlias' )
			//->joinWith( 'relation relationAlias' )
			->orderBy("$tableName_LogUserWorkActivity.id DESC");
		;
		
		$dataProvider = new ActiveDataProvider( [
			'query' => $query,
			'sort' => [ 'defaultOrder' => [ 'id' => SORT_DESC ] ],
		] );
		
		if( !( $this->load($params) && $this->validate() ) ) {
			
			$this->filter_dateFrom = date( 'd.m.Y', strtotime("-10 days") );
			
			$query->andWhere( [ '>=', 'timestamp', date( "Y-m-d", strtotime( $this->filter_dateFrom ) ) ] );
			
			return $dataProvider;
		}
		
		if( $this->filter_dateFrom ?? false ) {
			$query->andWhere( [ '>=', 'timestamp', date( "Y-m-d", strtotime( $this->filter_dateFrom ) ) ] );
		}
		else {
			//$this->filter_dateFrom = date( 'Y-m-d H:i:s' ); // some default params
		}
		
		if( $this->filter_dateTo ?? false  ) {
			$query->andWhere( [ '<=', 'timestamp', date( "Y-m-d", strtotime( $this->filter_dateTo . ' +1 day' ) ) ] );
		}
		else {
			//$this->filter_dateTo = $this->filter_dateFrom;  // some default params, but not less $this->dateFrom
		}
		
		/*
		// grid filtering conditions
		$query->andFilterWhere( [
			'id'    => $this->id,
			'title' => $this->title,
		] );
		
        $query->andFilterWhere( [ 'or',
			[ 'like', 'user.email', $this->filter_search ],
			[ 'like', 'user.phone', $this->filter_search ],
		] );
		
		$query->andFilterWhere( [ 'like', 'relationAlias.title', $this->filter_relation_title ] );
		*/
		
		return $dataProvider;
	}
}