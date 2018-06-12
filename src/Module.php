<?php

namespace yozh\userworkactivity;

use Yii;
use yozh\base\Module as BaseModule;
use yozh\userworkactivity\models\LogUserWorkActivity;

class Module extends BaseModule
{
	
	const MODULE_ID = 'userworkactivity';
	
	public $controllerNamespace = 'yozh\\' . self::MODULE_ID . '\controllers';
	
	public static function log( $event )
	{
		if( $user_id = Yii::$app->getUser()->getId() ) {
			
			$routeExcaptions = [
				'debug/default/toolbar',
			];
			
			if( ($route = $event->action->controller->route) && !in_array( $route, $routeExcaptions ) ) {
				( new LogUserWorkActivity( [
					'url'   => Yii::$app->request->getUrl(),
					'route'   => $route,
					'user_id' => $user_id,
				] ) )->save();
				
			}
			
		}
		
	}
	
}
