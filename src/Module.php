<?php

namespace ogheo\comments;

use Yii;

/**
 * Class Comments module definition class
 * @package ogheo\comments
 */
class Module extends \yii\base\Module
{
    /**
     * User model class name
     * @var
     */
    public $userModel;

    /**
     * @var string
     */
    public $commentModelClass = 'ogheo\comments\models\Comments';

    /**
     * @var string
     */
    public $commentRatingModelClass = 'ogheo\comments\models\CommentsRating';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'ogheo\comments\controllers';

    /**
     * Sessions key required for saving guest username
     * @var string
     */
    public $guestUsernameSessionKey = '_username';

    /**
     * Sessions key required for saving guest email
     * @var string
     */
    public $guestEmailSessionKey = '_email';

    /**
     * Cookie key required for saving guest username
     * @var string
     */
    public $guestUsernameCookieName = '_username';

    /**
     * Cookie key required for saving guest email
     * @var string
     */
    public $guestEmailCookieName = '_email';

    /**
     * Cookie key required for saving guest username
     * @var string
     */
    public $upRatedCookieName = '_uprated';

    /**
     * Cookie key required for saving guest email
     * @var string
     */
    public $downRatedCookieName = '_downrated';

    /**
     * Number of seconds how long the information should be stored in cookie, default 360 days.
     * @var int
     */
    public $ratingCookieDuration = 31104000;

    /**
     * Number of seconds how long the information should be stored in cookie, default 30 days.
     * @var int
     */
    public $guestCookieDuration = 2592000;

    /**
     * Sessions key required to invalidate cache
     * @var string
     */
    public $urlCacheSessionKey = '_url';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->userModel === null) {
            $this->userModel = Yii::$app->getUser()->identityClass;
        }
    }
}
