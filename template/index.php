<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel izi\template\models\TemplatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="templates-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Templates', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'ref_code',
            'title',
            'parent_id',
            //'state',
            //'is_active',
            //'is_mobile',
            //'is_extension',
            //'layout',
            //'is_invisible',
            //'bizrule:ntext',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
