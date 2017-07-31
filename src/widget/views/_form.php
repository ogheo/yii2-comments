<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use ogheo\comments\helpers\CommentsHelper;

/** @var $commentModel */
/** @var $widget */

?>
<div id="<?= $widget->formContainerId ?>" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 comments-input">

        <?php if (Yii::$app->user->isGuest && ($widget->guestComments === false)): ?>

            <div class="disabled-form">
                <?= Yii::t('comments', '<a href="{url}">Log in</a> to post a comment.', ['url' => Url::to(Yii::$app->getUser()->loginUrl)]) ?>
            </div>

        <?php else: ?>

            <?php $form = ActiveForm::begin([
                'action' => Url::to(
                    [
                        'comments/default/create', 'data' => CommentsHelper::encryptData(
                            [
                                'url' => $commentModel->url,
                                'model' => $commentModel->model,
                                'model_key' => $commentModel->model_key
                            ]
                        )
                    ]
                ),
                'validationUrl' => Url::to(['comments/default/validate']),
                'validateOnChange' => false,
                'validateOnBlur' => false,
                'options' => [
                    'id' => $widget->formId
                ]
            ]) ?>

            <div id="media" class="media">
                <div class="media-left">
                    <?= $commentModel->getAuthorUrl() === null ? (
                    $commentModel->getAuthorAvatar() === null ?
                        Html::tag(
                            'span', '', ['class' => 'media-object img-rounded without-image']
                        ) : Html::img(
                            $commentModel->getAuthorAvatar(),
                            [
                                'class' => 'media-object img-rounded',
                                'alt' => $commentModel->getAuthorName()
                            ]
                        )
                    ) : Html::a(
                        $commentModel->getAuthorAvatar() === null ?
                            Html::tag(
                                'span', '', ['class' => 'media-object img-rounded without-image']
                            ) : Html::img(
                                $commentModel->getAuthorAvatar(), [
                                    'class' => 'media-object img-rounded',
                                    'alt' => $commentModel->getAuthorName()
                                ]
                            ),
                        [$commentModel->getAuthorUrl()]
                    ) ?>
                </div>
                <div class="media-body">
                    <?= $form->field($commentModel, 'content', ['template' => '{input}{error}'])->textarea(['placeholder' => Yii::t('comments', 'Share your thoughts...')]) ?>
                    <div class="media-buttons">
                        <div class="row nospace">
                            <?php if (Yii::$app->user->isGuest): ?>
                                <?php if ($commentModel->username === null || $commentModel->email === null) { ?>
                                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 user-data">
                                        <?= $form->field($commentModel, 'username', [
                                            'enableClientValidation' => true,
                                            'enableAjaxValidation' => true
                                        ])->textInput([
                                            'maxlength' => true,
                                            'class' => 'form-control input-sm',
                                            'placeholder' => Yii::t('comments', 'Name')
                                        ])->label(false) ?>
                                    </div>
                                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 user-data">
                                        <?= $form->field($commentModel, 'email')->textInput([
                                            'maxlength' => true,
                                            'email' => true,
                                            'class' => 'form-control input-sm',
                                            'placeholder' => Yii::t('comments', 'Email')
                                        ])->label(false) ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 user-data">
                                        <?= Yii::t('comments', 'As') . ' <b>' . $commentModel->username . '</b>'; ?>
                                    </div>
                                <?php } ?>
                            <?php else: ?>
                                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 user-data">
                                    <?= Yii::t('comments', 'As') . ' <b>' . Yii::$app->user->identity->username . '</b>'; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (Yii::$app->user->isGuest && ($commentModel->username === null || $commentModel->email === null)) { ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-right">
                                    <?= Html::button(
                                        Yii::t('comments', 'Cancel'), [
                                            'class' => 'btn btn-default reply-cancel',
                                            'type' => 'reset',
                                            'data' => [
                                                'action' => 'cancel-reply'
                                            ]
                                        ]
                                    ) ?>
                                    <?= Html::submitButton(Yii::t('comments', 'Post'), [
                                        'id' => $widget->submitButtonId,
                                        'class' => 'btn btn-primary',
                                        'data' => [
                                            'action' => Url::to(
                                                [
                                                    '/comments/default/create', 'data' => CommentsHelper::encryptData(
                                                    [
                                                        'url' => $commentModel->url,
                                                        'model' => $commentModel->model,
                                                        'model_key' => $commentModel->model_key
                                                    ]
                                                )
                                                ]
                                            )
                                        ]
                                    ]) ?>
                                </div>
                            <?php } else { ?>
                                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-right">
                                    <?= Html::button(
                                        Yii::t('comments', 'Cancel'), [
                                            'class' => 'btn btn-default btn-xs reply-cancel',
                                            'type' => 'reset',
                                            'data' => [
                                                'action' => 'cancel-reply'
                                            ]
                                        ]
                                    ) ?>
                                    <?= Html::submitButton(Yii::t('comments', 'Post'), [
                                        'id' => $widget->submitButtonId,
                                        'class' => 'btn btn-primary btn-xs',
                                        'data' => [
                                            'action' => Url::to(
                                                [
                                                    '/comments/default/create', 'data' => CommentsHelper::encryptData(
                                                        [
                                                            'url' => $commentModel->url,
                                                            'model' => $commentModel->model,
                                                            'model_key' => $commentModel->model_key
                                                        ]
                                                    )
                                                ]
                                            )
                                        ]
                                    ]) ?>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <!-- To check -->
                    <noscript>
                        <div class="media-buttons active-media-buttons text-right">
                            <?= Html::button(
                                Yii::t('comments', 'Cancel'), [
                                    'id' => 'reply-cancel',
                                    'class' => 'btn btn-default btn-xs reply-cancel',
                                    'type' => 'reset',
                                    'data' => [
                                        'action' => 'cancel-reply'
                                    ]
                                ]
                            )
                            ?>
                            <?= Html::submitButton(
                                Yii::t('comments', 'Post'), [
                                    'class' => 'btn btn-primary btn-xs comment-submit',
                                ]
                            )
                            ?>
                        </div>
                    </noscript>

                </div>
            </div>

            <?php $form->end(); ?>

        <?php endif; ?>

    </div>
</div>
