<?php

use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\Module as CommentsModule;

/** @var $commentModel */
/** @var $commentsCounter */
/** @var $dataProvider */
/** @var $widget */

$cacheKey = $commentModel->url . $commentModel->model . $commentModel->model_key;
$cacheTag = Url::previous(CommentsModule::getInstance()->urlCacheSessionKey);
$cacheProperties = CommentsHelper::getCacheProperties($cacheTag);

?>

<div id="<?= $widget->wrapperId ?>" class="comments">

    <?php if ($widget->defaultCommentsView === 'restricted') : ?>
        <div id="<?= $widget->showCommentsId ?>" class="show-comments text-center">
            <a href="#" data-action="show-comments">
                <?= Yii::t('comments', 'Comments') ?>
            </a>
        </div>
    <?php endif; ?>

    <div id="<?= $widget->fullCommentsId ?>" <?= $widget->defaultCommentsView === 'restricted' ? 'class="hidden"' : '' ?>>

        <?php Pjax::begin([
            'id' => $widget->pjaxContainerId
        ]); ?>

        <div id="comments-container-header" class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 comments-header">

                <?php if ($widget->defaultCommentsView === 'restricted') : ?>
                    <div class="hide-comments">
                        <a href="#" data-action="hide-comments">
                            <?= Yii::t('comments', 'Hide') ?> <i class="glyphicon glyphicon-remove"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($this->beginCache($cacheKey . '-counter', $cacheProperties)) { ?>
                    <h2 class="page-header">
                        <?= Yii::t('comments', 'Comments') ?>
                        <small>
                            (<?= $commentsCounter->count() ?>)
                        </small>
                    </h2>
                <?php $this->endCache(); } ?>

            </div>
        </div>

        <?php if ($widget->formPosition === 'top') {
            echo $this->render($widget->formView, [
                'commentModel' => $commentModel,
                'widget' => $widget
            ]);
        } ?>

        <?php if ($this->beginCache($cacheKey, $cacheProperties)) { ?>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <?= ListView::widget(
                        array_merge(
                            [
                                'dataProvider' => $dataProvider,
                            ], $widget->getListViewConfig()
                        )
                    ) ?>
                </div>
            </div>
        <?php $this->endCache(); } ?>

        <?php if ($widget->formPosition === 'bottom') {
            echo $this->render($widget->formView, [
                'commentModel' => $commentModel,
                'widget' => $widget
            ]);
        } ?>

        <?php Pjax::end(); ?>

    </div>

</div>
