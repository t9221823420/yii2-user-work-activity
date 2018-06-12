<?php

namespace yozh\userworkactivity\controllers;

use common\models\User;
use Yii;
use DateTime;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yozh\base\controllers\DefaultController as Controller;
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
		$searchModel  = new LogUserWorkActivitySearch;
		$dataProvider = $searchModel->search( Yii::$app->request->queryParams );
		
		/**
		 * @var $dataProvider ActiveDataProvider
		 */
		
		$gap = 5;
		
		$dataProvider->query
			->asArray();
		
		if( !Yii::$app->user->can( User::RBAC_ADMIN_ROLE ) ){
			$dataProvider->query
				->andWhere(['user_id' => Yii::$app->user->getId()]);
		}
		
		$records    = $dataProvider->query->asArray()->all();
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
				
				$resultTree[ $record->user_id ]['data'][ $currentDay ]['intervals'][ $currentInterval['timeFrom'] ]['interval'] = $diff->i;
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
		
		$dataProvider = new ArrayDataProvider( [
			'allModels' => $resultFlat,
		] );
		
		return $this->_render( 'index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
			'resultFlat'   => $resultFlat,
			'resultTree'   => $resultTree,
		] );
		
	}
	
	
}
