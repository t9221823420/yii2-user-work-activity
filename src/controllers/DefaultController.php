<?php

namespace yozh\userworkactivity\controllers;

use common\models\User;
use Yii;
use DateTime;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yozh\base\controllers\DefaultController as Controller;
use yozh\helpdesk\models\HelpdeskReply;
use yozh\userworkactivity\models\LogUserWorkActivity;
use yozh\userworkactivity\models\LogUserWorkActivitySearch;

class DefaultController extends Controller
{
	public static function defaultModelClass()
	{
		return LogUserWorkActivity::class;
	}
	
	public function actionIndex()
	{
		$params = $this->_actionIndex();
		
		return $this->render( 'index', $params );
		
	}
	
	protected function _actionIndex()
	{
		$ModelSearch  = new LogUserWorkActivitySearch;
		$dataProvider = $ModelSearch->search( Yii::$app->request->queryParams );
		
		/**
		 * @var $dataProvider ActiveDataProvider
		 */
		$dataProvider->query
			->andWhere( [
				'not', [
					'user_id' => 1,
				],
			] )
			->asArray()
		;
		
		if( !Yii::$app->user->can( User::RBAC_ADMIN_ROLE ) ) {
			$dataProvider->query
				->andWhere( [ 'user_id' => Yii::$app->user->getId() ] );
		}
		
		if( $records = $dataProvider->query->asArray()->all() ) {
			
			$gap = Yii::$app->settings->get( 'yozh\userworkactivity\models\LogUserWorkActivity::GAP_TIME', 8 );
			
			$resultTree = [];
			$resultFlat = [];
			foreach( $records as $record ) {
				
				$record = (object)$record;
				
				//$timestamp = date( 'H:i d.m.Y', strtotime( $record->timestamp ) );
				$timestamp = $record->timestamp;
				
				if( !$record->user_id ) {
					continue;
				}
				
				$currentDay = date( 'd.m.Y', strtotime( $timestamp ) );
				
				$newDate = function( $record ) use ( $currentDay ) {
					return [
						'sum'       => 0,
						'intervals' => [],
					];
				};
				
				$newInterval = function( $timestamp ) {
					return [
						'timeFrom' => $timestamp,
						'timeTo'   => $timestamp,
						'interval' => 0,
					];
				};
				
				if( !isset( $resultTree[ $record->user_id ] ) ) {
					
					$resultTree[ $record->user_id ] = [
						'user_id'      => $record->user_id,
						'username'     => $record->username,
						'email'        => $record->email,
						'sum'          => 0,
						'recordsCount' => 0,
						'data'         => [],
					];
					
				}
				
				if( !isset( $resultTree[ $record->user_id ]['data'][ $currentDay ] ) ) {
					$resultTree[ $record->user_id ]['data'][ $currentDay ] = $newDate( $currentDay );
				}
				
				$currentInterval = end( $resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'] );
				
				if( !$currentInterval ) {
					
					$resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $timestamp ] = $newInterval( $timestamp );
					continue;
				}
				
				$diff = ( new DateTime( $currentInterval['timeTo'] ) )->diff( new DateTime( $timestamp ) );
				
				if( $diff->i > $gap ) {
					
					$resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $timestamp ] = $newInterval( $timestamp );
					
					if( $currentInterval['interval'] == 0 ) {
						unset( $resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $currentInterval['timeFrom'] ] );
						continue;
					}
					
				}
				else {
					
					$diff = ( new DateTime( $currentInterval['timeFrom'] ) )->diff( new DateTime( $timestamp ) );
					
					$resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $currentInterval['timeFrom'] ]['interval'] = $diff->d * 24 * 60 + $diff->h * 60 + $diff->i;
					$resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $currentInterval['timeFrom'] ]['timeTo']   = $timestamp;
					
				}
				
			}
			
			foreach( $resultTree as $user_id => &$userData ) {
				foreach( $userData['data'] as $date => &$dateData ) {
					foreach( $dateData['intervals'] as $timestamp => &$interval ) {
						
						$userData['recordsCount']++;
						
						$userData['sum'] += $interval['interval'];
						$dateData['sum'] += $interval['interval'];
						
						$key = $user_id . ' - ' . $interval['timeFrom'] . ' - ' . $interval['interval'];
						
						$resultFlat[ $key ] = [
							'user_id'  => $userData['user_id'],
							'username' => $userData['username'],
							'email'    => $userData['email'],
							'date'     => $date,
							'timeFrom' => date( 'H:i:s', strtotime( $interval['timeFrom'] ) ),
							'timeTo'   => date( 'H:i:s', strtotime( $interval['timeTo'] ) ),
							'interval' => $interval['interval'],
						];
						
					}
					
				}
			}
			
			$records = ( new Query() )
				->from( HelpdeskReply::tableName() )
				->select( [
					'DATE(created_at) AS dt',
					'user_id',
					'COUNT(*) AS count',
				] )
				->where( [
					'user_id' => array_keys( $resultTree ),
				] )
				->groupBy( [
					'user_id',
					'DATE(created_at)',
				] )
				->orderBy( 'created_at DESC' )
				->all()
			;
			
			$resultReplies = [];
			foreach( $records as $record ) {
				$resultReplies[ $record['user_id'] ][ date( 'd.m.Y', strtotime( $record['dt'] ) ) ] = $record['count'];
			}
			
		}
		
		$dataProvider = new ArrayDataProvider( [
			'allModels' => $resultFlat ?? [],
		] );
		
		return [
			'ModelSearch'   => $ModelSearch,
			'dataProvider'  => $dataProvider,
			'resultFlat'    => $resultFlat ?? [],
			'resultTree'    => $resultTree ?? [],
			'resultReplies' => $resultReplies ?? [],
			'userList'      => User::getList( [], null, 'username' ),
		];
	}
	
	
}
