<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.05.2018
 * Time: 17:09
 */

// $path = $this->context->module->getParentPath( 'views' . DIRECTORY_SEPARATOR . $this->context->id ) . DIRECTORY_SEPARATOR . basename( __FILE__ );

use yozh\widget\widgets\Modal;
use yozh\widget\widgets\ActiveButton;
use yii\widgets\Pjax;
use yozh\widget\widgets\grid\GridView;
use kartik\helpers\Html;
use yii\helpers\Url;
use yozh\base\components\helpers\ArrayHelper;

include __DIR__ . '/_header.php';

$columns = [
	//'url' => 'url',
	//'user_id' => 'user_id',
	
	[
		'attribute' => 'user_id',
		'rowSpan'   => function( $Model ) use ( $resultTree ) {
			
			$Model = (object)$Model;
			
			return $resultTree[ $Model->user_id ]['recordsCount'];
		},
		'format'    => 'html',
		'value'     => function( $Model ) use ( $resultTree ) {
			return ''
                . $Model['username'] . "<br />"
                . $Model['email'] . "<br />"
                . Yii::t( 'app', 'Sum' )
				. ": {$resultTree[$Model['user_id']]['sum']} min";
		},
	],
	
	[
		'attribute' => 'date',
		'rowSpan'   => function( $Model ) use ( $resultTree ) {
			
			$Model = (object)$Model;
			
			return count( $resultTree[ $Model->user_id ]['data'][ $Model->date ]['intervals'] );
		},
		'format'    => 'html',
		'value'     => function( $Model ) use ( $resultTree, $resultReplies ) {
			
			$repliesCount = $resultReplies[ $Model['user_id'] ][ $Model['date'] ] ?? 0;
			
			return $Model['date']
				. "<br />" . Yii::t( 'app', 'Sum' ) . ": {$resultTree[$Model['user_id']]['data'][$Model['date']]['sum']} min"
				. "<br />" . Yii::t( 'app', 'Replies' ) . ": $repliesCount"
				;
		},
  
	],
	
	// поменяны местами из-за обратной сортировки
	'timeTo:ntext:' . Yii::t('app', 'Time From'),
	'timeFrom:ntext:' . Yii::t('app', 'Time To'),
    
	'interval:text:Min',

];

?>

<?= $this->render( '_search', $_params_ ); ?>

<div class="<?= "$modelId-$actionId" ?>">

    <h1><?= Html::encode( $this->title ) ?></h1>
    
	<?php Pjax::begin( [ 'id' => 'pjax-container' ] ); ?>
	
	<?php // echo $this->render('_search', ['Model' => $ModelSearch]); ?>
	
	<?= GridView::widget( [
		'dataProvider' => $dataProvider,
		//'filterModel' => $ModelSearch,
		//'layout'       => "{items}\n{pager}",
		//'showHeader'   => false,
		'tableOptions' => [
			'class' => 'table table-striped table-hover',
		],
		
		'columns' => $columns,
	
	] ); ?>
	
	<?php Pjax::end(); ?>

</div>

<?php $this->registerJs( $this->render( '_js.php', [ 'section' => 'onload' ] ), $this::POS_END ); ?>
<?php $this->registerJs( $this->render( '_js.php', [ 'section' => 'modal' ] ), $this::POS_END ); ?>
