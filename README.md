# yii2-comments [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE) [![Scrutinizer](https://img.shields.io/scrutinizer/g/ogheo/yii2-comments.svg?style=flat-square)](https://scrutinizer-ci.com/g/ogheo/yii2-comments/)

Comments module for Yii2.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

try

```
composer require "ogheo/yii2-comments:*"
```

or add

```
"ogheo/yii2-comments": "*"
```

to the require section of your `composer.json` file.

## Configuration

### Database migrations

```
php yii migrate/up --migrationPath=@vendor/ogheo/yii2-comments/src/migrations
```

### Module setup

```
'modules' => [
    'comments' => [
        'class' => 'ogheo\comments\Module',
    ]
]
```

All available options can be viewed in Comments module file.

## Usage

Add one of the following examples to the view file.

### Basic example

Model is not mandatory, comments will be associated to the current url.

```
use ogheo\comments\widget\Comments;
    
echo Comments::widget();
```

### Advanced example

To change default settings, you can do as follows:

```
use ogheo\comments\widget\Comments;
    
echo Comments::widget([
    'model' => $model,
    'model_key' => $model_key,
]);
```

All available options can be viewed in Comments widget file.

Enjoy ;)

## Demo pictures

![demo](/docs/images/demo.png)

