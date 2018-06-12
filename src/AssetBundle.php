<?php

namespace yozh\userworkactivity;

class AssetBundle extends \yozh\base\AssetBundle
{

    public $sourcePath = __DIR__ .'/../assets/';

    public $css = [
        //'css/yozh-userworkactivity.css',
	    //['css/yozh-userworkactivity.print.css', 'media' => 'print'],
    ];
	
    public $js = [
        //'js/yozh-userworkactivity.js'
    ];
	
    public $depends = [
        //'yii\bootstrap\BootstrapAsset',
    ];	
	
	public $publishOptions = [
		//'forceCopy'       => true,
	];
	
}