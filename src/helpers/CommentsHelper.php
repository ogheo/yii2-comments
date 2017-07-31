<?php

namespace ogheo\comments\helpers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use ogheo\comments\Module as CommentsModule;
use yii\web\BadRequestHttpException;

/**
 * Class CommentsHelper
 * @package ogheo\comments\helpers
 */
class CommentsHelper
{
    /**
     * Encode comment id
     * @param $id
     * @return string
     */
    public static function encodeId($id)
    {
        return base_convert($id, 10, 36);
    }

    /**
     * Decode comment id
     * @param $id
     * @return string
     */
    public static function decodeId($id)
    {
        return base_convert($id, 36, 10);
    }

    /**
     * Encrypt data
     * @param $decryptedData
     * @return string
     */
    public static function encryptData($decryptedData)
    {
        return utf8_encode(
            Yii::$app->getSecurity()->encryptByKey(
                Json::encode($decryptedData), CommentsModule::getInstance()->id
            )
        );
    }

    /**
     * Decrypt data
     * @param $encryptedData
     * @return mixed
     * @throws BadRequestHttpException
     */
    public static function decryptData($encryptedData)
    {
        $decryptedData = Yii::$app->getSecurity()->decryptByKey(
            utf8_decode($encryptedData), CommentsModule::getInstance()->id
        );

        if ($decryptedData !== false) {
            return Json::decode($decryptedData);
        }

        throw new BadRequestHttpException(Yii::t('comments', 'Sorry, something went wrong. Please try again later.'));
    }

    /**
     * Set username in session and cookies
     * @param $username
     */
    public static function setUsername($username)
    {
        Yii::$app->session[CommentsModule::getInstance()->guestUsernameSessionKey] = $username;
        if (CommentsModule::getInstance()->guestCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->guestUsernameCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->guestCookieDuration,
                'value' => $username,
            ]));
        }
    }

    /**
     * Get username from session or cookies
     * @return mixed|null
     */
    public static function getUsername()
    {
        $username = Yii::$app->session->get(CommentsModule::getInstance()->guestUsernameSessionKey);

        if ($username === null) {
            $cookies = Yii::$app->request->cookies;
            if ($username === null && $cookies->has(CommentsModule::getInstance()->guestUsernameCookieName)) {
                $username = !empty($cookies[CommentsModule::getInstance()->guestUsernameCookieName]->value) ?
                    $cookies[CommentsModule::getInstance()->guestUsernameCookieName]->value : null;
            }
        }

        return $username;
    }

    /**
     * Set email in session and cookies
     * @param $email
     */
    public static function setEmail($email)
    {
        Yii::$app->session[CommentsModule::getInstance()->guestEmailSessionKey] = $email;
        if (CommentsModule::getInstance()->guestCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->guestEmailCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->guestCookieDuration,
                'value' => $email,
            ]));
        }
    }

    /**
     * Get email from session or cookies
     * @return mixed|null
     */
    public static function getEmail()
    {
        $email = Yii::$app->session->get(CommentsModule::getInstance()->guestEmailSessionKey);

        if ($email === null) {
            $cookies = Yii::$app->request->cookies;
            if ($email === null && $cookies->has(CommentsModule::getInstance()->guestEmailCookieName)) {
                $email = !empty($cookies[CommentsModule::getInstance()->guestEmailCookieName]->value) ?
                    $cookies[CommentsModule::getInstance()->guestEmailCookieName]->value : null;
            }
        }

        return $email;
    }

    /**
     * Get uprated comments
     * @return null|string
     */
    public static function getUprated()
    {
        $cookies = Yii::$app->request->cookies;
        if ($cookies->has(CommentsModule::getInstance()->upRatedCookieName)) {
            return !empty($cookies[CommentsModule::getInstance()->upRatedCookieName]->value) ?
                $cookies[CommentsModule::getInstance()->upRatedCookieName]->value : null;
        }

        return null;
    }

    /**
     * Check if comment is rated by user
     * @param $id
     * @return bool
     */
    public static function isUprated($id)
    {
        $uprated = self::getUprated();
        $uprated_arr = explode(',', $uprated);
        if (in_array($id, $uprated_arr)) {
            return true;
        }

        return false;
    }

    /**
     * Set uprated comment
     * @param $id
     */
    public static function setUprated($id)
    {
        if (CommentsModule::getInstance()->ratingCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $uprated = self::getUprated();

            if ($uprated === null) {
                $uprated = $id;
            } else {
                $uprated_arr = explode(',', $uprated);
                if (!in_array($id, $uprated_arr)) {
                    array_push($uprated_arr, $id);
                }

                $uprated = implode(',', $uprated_arr);
            }

            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->upRatedCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->ratingCookieDuration,
                'value' => $uprated,
            ]));
        }
    }

    /**
     * Delete uprated comment
     * @param $id
     */
    public static function deleteUprated($id)
    {
        if (CommentsModule::getInstance()->ratingCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $uprated = self::getUprated();

            $uprated_arr = explode(',', $uprated);
            if (($key = array_search($id, $uprated_arr)) !== false) {
                unset($uprated_arr[$key]);
            }

            $uprated = implode(',', $uprated_arr);
            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->upRatedCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->ratingCookieDuration,
                'value' => $uprated,
            ]));
        }
    }

    /**
     * Get downrated comments
     * @return null|string
     */
    public static function getDownrated()
    {
        $cookies = Yii::$app->request->cookies;
        if ($cookies->has(CommentsModule::getInstance()->downRatedCookieName)) {
            return !empty($cookies[CommentsModule::getInstance()->downRatedCookieName]->value) ?
                $cookies[CommentsModule::getInstance()->downRatedCookieName]->value : null;
        }

        return null;
    }

    /**
     * Check if comment is downrated by user
     * @param $id
     * @return bool
     */
    public static function isDownrated($id)
    {
        $downrated = self::getDownrated();
        $downrated_arr = explode(',', $downrated);
        if (in_array($id, $downrated_arr)) {
            return true;
        }

        return false;
    }

    /**
     * Set downrated comment
     * @param $id
     */
    public static function setDownrated($id)
    {
        if (CommentsModule::getInstance()->ratingCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $downrated = self::getDownrated();

            if ($downrated === null) {
                $downrated = $id;
            } else {
                $downrated_arr = explode(',', $downrated);
                if (!in_array($id, $downrated_arr)) {
                    array_push($downrated_arr, $id);
                }

                $downrated = implode(',', $downrated_arr);
            }

            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->downRatedCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->ratingCookieDuration,
                'value' => $downrated,
            ]));
        }
    }

    /**
     * Delete downrated comment
     * @param $id
     */
    public static function deleteDownrated($id)
    {
        if (CommentsModule::getInstance()->ratingCookieDuration) {
            $cookies = Yii::$app->response->cookies;
            $downrated = self::getDownrated();

            $downrated_arr = explode(',', $downrated);
            if (($key = array_search($id, $downrated_arr)) !== false) {
                unset($downrated_arr[$key]);
            }

            $downrated = implode(',', $downrated_arr);
            $cookies->add(new \yii\web\Cookie([
                'name' => CommentsModule::getInstance()->downRatedCookieName,
                'expire' => time() + (int)CommentsModule::getInstance()->ratingCookieDuration,
                'value' => $downrated,
            ]));
        }
    }

    /**
     * Build comments tree
     * @param $comments
     * @param int $parentId
     * @return array
     */
    public static function buildCommentsTree(&$comments, $parentId = 0)
    {
        $tree = [];

        foreach ($comments as &$comment) {
            if ($comment->parent_id == $parentId) {
                $children = self::buildCommentsTree($comments, $comment->id);
                if ($children) {
                    $comment->children = $children;
                }
                $tree[$comment->id] = $comment;
                unset($comment);
            }
        }

        return $tree;
    }

    /**
     * Get cache properties
     * @param $tag
     * @param int $duration
     * @return array
     */
    public static function getCacheProperties($tag, $duration = 3600)
    {
        return [
            'duration' => $duration,
            'variations' => [
                Yii::$app->language,
                Url::current()
            ],
            'dependency' => [
                'class' => 'yii\caching\TagDependency',
                'tags' => $tag
            ]
        ];
    }
}