<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.05.2018
 * Time: 17:09
 */

use kartik\date\DatePicker;
use yozh\form\ActiveForm;

?>

<div class="filters">
	
	<?php $form = $form ?? ActiveForm::begin( [
			'method' => 'get',
		] ); ?>
	
	<?php
	$fields['user'] = $form->field( $ModelSearch, 'user_id' )
	                       ->label( Yii::t( 'app', 'User' ) )
	                       ->dropDownList( $userList, [
		                       'prompt' => Yii::t( 'app', 'Select user' ),
	                       ] )
	;
	?>
	
	<?php include( Yii::getAlias( $parentViewPath . '/_search.php' ) ); ?>

</div>
