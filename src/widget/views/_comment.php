<?php

use yii\helpers\Html;
use ogheo\comments\helpers\CommentsHelper;

/** @var $model */
/** @var $nestedLevel */
/** @var $maxNestedLevel */
/** @var $widget */

?>

<div class="media-container">
    <div class="media-left">
        <?= $model->getAuthorUrl() === null ? (
        $model->getAuthorAvatar() === null ?
            Html::tag(
                'span', '', ['class' => 'media-object img-rounded without-image']
            ) : Html::img(
                $model->getAuthorAvatar(),
                [
                    'class' => 'media-object img-rounded',
                    'alt' => $model->getAuthorName()
                ]
            )
        ) : Html::a(
            $model->getAuthorAvatar() === null ?
                Html::tag(
                    'span', '', ['class' => 'media-object img-rounded without-image']
                ) : Html::img(
                    $model->getAuthorAvatar(), [
                        'class' => 'media-object img-rounded',
                        'alt' => $model->getAuthorName()
                    ]
                ), [$model->getAuthorUrl()]
        ) ?>
    </div>
    <div class="media-body">
        <div class="media-info">
            <h4 class="media-heading">
                <?= $model->getAuthorUrl() === null ? $model->getAuthorName() : Html::a(
                    $model->getAuthorName(), [$model->getAuthorUrl()]
                ) ?>
                <small><?= $model->getPostedDate() ?></small>
            </h4>

            <?= Html::encode($model->content); ?>

            <div class="row nospace">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="comment-info">
                        <?php if ($nestedLevel < $maxNestedLevel): ?>
                            <div class="comment-reply" data-action="reply">
                                <a href="#">Reply</a>
                            </div>
                        <?php endif; ?>
                        <div class="comment-rating text-right">
                            <span class="score">
                                <?php $ratingCounter = $model->getRatingCounter() ?>
                                <span id="score" class="<?= $ratingCounter < 0 ? 'bad' : 'good' ?>">
                                    <?= $ratingCounter ?>
                                </span>
                            </span>
                            <span class="thumbs-up <?= CommentsHelper::isUprated($model->id) ? 'rated' : null ?>" data-action="uprate">
                                <a href="#">
                                    <i class="glyphicon glyphicon-thumbs-up"></i>
                                </a>
                            </span>
                            <span class="thumbs-down <?= CommentsHelper::isDownrated($model->id) ? 'rated' : null ?>" data-action="downrate">
                                <a href="#">
                                    <i class="glyphicon glyphicon-thumbs-down"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($nestedLevel < $maxNestedLevel): ?>
            <?php if ($model->hasChildren()) : ?>
                <?php $nestedLevel++; ?>
                <?php foreach ($model->getChildren() as $children) : ?>
                    <div class="media" data-key="<?php echo CommentsHelper::encodeId($children->id); ?>">
                        <?= $this->render('_comment', [
                            'model' => $children,
                            'nestedLevel' => $nestedLevel,
                            'maxNestedLevel' => $maxNestedLevel,
                            'widget' => $widget
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
