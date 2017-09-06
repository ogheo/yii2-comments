<?php

namespace ogheo\comments\widget;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use ogheo\comments\assets\CommentsAsset;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\Module as CommentsModule;

/**
 * Class Comments
 * @package ogheo\comments\widget
 */
class Comments extends \yii\base\Widget
{
    /**
     * Page url
     * @var string
     */
    public $url;

    /**
     * Model
     * @var object
     */
    public $model;

    /**
     * Model key
     * @var string
     */
    public $model_key;

    /**
     * Display comments form for unregistered users.
     * @var bool
     */
    public $guestComments = true;

    /**
     * Comments block display type.
     * By default comments block is displayed as a button.
     * To display comments block in full size, change that value to whatever you want.
     * Ex: extended
     * @var string
     */
    public $defaultCommentsView = 'restricted';

    /**
     * Position where comments form will be displayed.
     * By default form is displayed above the comments.
     * To display it after, change it to 'bottom'.
     * @var string
     */
    public $formPosition = 'top';

    /**
     * Maximum allowed level for comments replies.
     * @var int
     */
    public $maxNestedLevel = 5;

    /**
     * Number of displayed comments by default.
     * @var int
     */
    public $commentsPerPage = 10;

    /**
     * Order direction.
     * @var int
     */
    public $order = SORT_DESC;

    /**
     * Nested order direction.
     * @var int
     */
    public $nestedOrder = SORT_ASC;

    /**
     * Comment form id
     * @var string
     */
    public $formId = 'comment-form';

    /**
     * Comments wrapper id
     * @var string
     */
    public $wrapperId = 'comments';

    /**
     * Comments show id
     * @var string
     */
    public $showCommentsId = 'show-comments';

    /**
     * Comments full id
     * @var string
     */
    public $fullCommentsId = 'comments-full';

    /**
     * Comments pjax container id
     * @var string
     */
    public $pjaxContainerId = 'comments-container';

    /**
     * Comment form id
     * @var string
     */
    public $formContainerId = 'comments-container-form';

    /**
     * Comment form id
     * @var string
     */
    public $submitButtonId = 'submitButton';

    /**
     * @var array DataProvider config
     */
    public $dataProviderConfig = null;
    /**
     * @var array ListView config
     */
    public $listViewConfig = null;

    /**
     * @var array comment widget client options
     */
    public $clientOptions = [];

    /**
     * @var string
     */
    public $commentsView = '@vendor/ogheo/yii2-comments/src/widget/views/comments';

    /**
     * @var string
     */
    public $commentView = '@vendor/ogheo/yii2-comments/src/widget/views/_comment';

    /**
     * @var string
     */
    public $formView = '@vendor/ogheo/yii2-comments/src/widget/views/_form';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->getModule('comments');

        $this->formId = $this->getId() . '-' . $this->formId;
        $this->wrapperId = $this->getId() . '-' . $this->wrapperId;
        $this->showCommentsId = $this->getId() . '-' . $this->showCommentsId;
        $this->fullCommentsId = $this->getId() . '-' . $this->fullCommentsId;
        $this->pjaxContainerId = $this->getId() . '-' . $this->pjaxContainerId;
        $this->formContainerId = $this->getId() . '-' . $this->formContainerId;
        $this->submitButtonId = $this->getId() . '-' . $this->submitButtonId;

        if ($this->url === null) {
            $this->url = Url::canonical();
            Url::remember($this->url, CommentsModule::getInstance()->urlCacheSessionKey);
        } else {
            Url::remember($this->url, CommentsModule::getInstance()->urlCacheSessionKey);
        }

        if ($this->model instanceof Model) {
            $this->model = $this->model->tableName();
        }

        $this->registerAssets();
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function run()
    {
        $commentClass = CommentsModule::getInstance()->commentModelClass;
        $commentsCounter = $commentClass::getCommentsCounter([
            'url' => $this->url,
            'model' => $this->model,
            'model_key' => $this->model_key
        ]);

        $dataProvider = new ActiveDataProvider(
            array_merge(
                [
                    'query' => $commentClass::getComments([
                        'url' => $this->url,
                        'model' => $this->model,
                        'model_key' => $this->model_key,
                        'nestedOrder' => $this->nestedOrder,
                        'loadComments' => true
                    ])
                ], $this->getDataProviderConfig()
            )
        );

        return $this->render($this->commentsView, [
            'dataProvider' => $dataProvider,
            'commentsCounter' => $commentsCounter,
            'commentModel' => Yii::createObject($commentClass, [[
                'url' => $this->url,
                'model' => $this->model,
                'model_key' => $this->model_key,
                'email' => Yii::$app->user->isGuest ? CommentsHelper::getEmail() : null,
                'username' => Yii::$app->user->isGuest ? CommentsHelper::getUsername() : null,
                'scenario' => Yii::$app->user->isGuest ? $commentClass::SCENARIO_GUEST : $commentClass::SCENARIO_USER,
                'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->getId()
            ]]),
            'widget' => $this
        ]);
    }

    /**
     * @return array
     */
    public function getDataProviderConfig()
    {
        if ($this->dataProviderConfig === null) {
            $this->dataProviderConfig = [
                'key' => function ($model) {
                    return CommentsHelper::encodeId($model->id);
                },
                'pagination' => [
                    'defaultPageSize' => $this->commentsPerPage
                ],
                'sort' => [
                    'attributes' => ['created_at'],
                    'defaultOrder' => [
                        'created_at' => $this->order
                    ]
                ]
            ];
        }

        return $this->dataProviderConfig;
    }

    /**
     * @return array
     */
    public function getListViewConfig()
    {
        if ($this->listViewConfig === null) {
            $this->listViewConfig = [
                'layout' => '{items}<div class="text-center">{pager}</div>',
                'options' => ['class' => 'comments-list'],
                'itemOptions' => ['class' => 'media'],
                'itemView' => function ($model, $key, $index, $widget) {
                    return $this->render($this->commentView, [
                        'maxNestedLevel' => $this->maxNestedLevel,
                        'nestedLevel' => 1,
                        'widget' => $this,
                        'model' => $model,
                    ]);
                },
                'emptyText' => '',
                'pager' => [
                    'class' => \yii\widgets\LinkPager::className(),
                    'options' => ['class' => 'pagination pagination-sm'],
                    'maxButtonCount' => 5
                ]
            ];
        }

        return $this->listViewConfig;
    }

    /**
     * @return string
     */
    public function getClientOptions()
    {
        $this->clientOptions['wrapperId'] = '#' . $this->wrapperId;
        $this->clientOptions['formSelector'] = '#' . $this->formId;
        $this->clientOptions['showCommentsId'] = '#' . $this->showCommentsId;
        $this->clientOptions['fullCommentsId'] = '#' . $this->fullCommentsId;
        $this->clientOptions['pjaxContainerId'] = '#' . $this->pjaxContainerId;
        $this->clientOptions['formContainerId'] = '#' . $this->formContainerId;
        $this->clientOptions['submitButtonId'] = '#' . $this->submitButtonId;
        $this->clientOptions['postButtonName'] = Yii::t('comments', 'Post');
        $this->clientOptions['replyButtonName'] = Yii::t('comments', 'Reply');
        $this->clientOptions['ratingUrl'] = Url::to(['comments/default/rate']);

        return Json::encode($this->clientOptions);
    }

    /**
     * Register assets.
     */
    public function registerAssets()
    {
        $view = $this->getView();
        CommentsAsset::register($view);
        $view->registerJs("jQuery('#{$this->wrapperId}').comment({$this->getClientOptions()});");
    }
}
