<?php

namespace ogheo\comments;

use yii\web\GroupUrlRule;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Class Bootstrap registers translations and needed application components.
 * @package ogheo\comments
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Url rules
     * @var array
     */
    public $urlRules = [
        'prefix' => 'comments',
        'rules' => [
            'rate' => 'default/rate',
            'create' => 'default/create',
            'validate' => 'default/validate',
            'manage/view' => 'manage/view',
            'manage/index' => 'manage/index',
            'manage/update' => 'manage/update',
            'manage/delete' => 'manage/delete',
        ],
    ];

    /**
     * @inheritdoc
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            // register module routes
            $app->getUrlManager()->addRules([new GroupUrlRule($this->urlRules)]);
            // register module translations
            if (!isset($app->get('i18n')->translations['comments*'])) {
                $app->get('i18n')->translations['comments*'] = [
                    'class' => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                    'sourceLanguage' => 'en'
                ];
            }
        }
    }
}
