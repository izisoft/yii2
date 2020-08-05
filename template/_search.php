<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model izi\template\models\TemplatesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="templates-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'ref_code') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'parent_id') ?>

    <?php // echo $form->field($model, 'state') ?>

    <?php // echo $form->field($model, 'is_active') ?>

    <?php // echo $form->field($model, 'is_mobile') ?>

    <?php // echo $form->field($model, 'is_extension') ?>

    <?php // echo $form->field($model, 'layout') ?>

    <?php // echo $form->field($model, 'is_invisible') ?>

    <?php // echo $form->field($model, 'bizrule') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
