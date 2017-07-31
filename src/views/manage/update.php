<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model ogheo\comments\models\Comments */

$this->title = Yii::t('comments', 'Update {modelClass}: ', [
    'modelClass' => 'Comments',
]) . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('comments', 'Comments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('comments', 'Update');
?>
<div class="comments-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="comments-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'model')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'model_key')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'main_parent_id')->textInput() ?>

        <?= $form->field($model, 'parent_id')->textInput() ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'language')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'created_by')->textInput() ?>

        <?= $form->field($model, 'updated_by')->textInput() ?>

        <?= $form->field($model, 'created_at')->textInput() ?>

        <?= $form->field($model, 'updated_at')->textInput() ?>

        <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'status')->textInput() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('comments', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
