Yii2 Redis Counter
==================
Redis Counter implements fast atomic counters using Redis storage.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist drsdre/yii2-redis-counter "*"
```

or add

```
"drsdre/yii2-redis-counter": "*"
```

to the require section of your `composer.json` file.


Usage
-----

You need to setup the client as application component:

```php
'components' => [
    'redisCounter' => [
        'class' => 'drsdre\redis\Counter',
    ],
    ...
]
```

Optional the parameter 'redis' can be added to specify a specific Redis connection.

Usage
-----

Once the extension is installed, use it in your code like :

```php
    // Create a hourly counter
    $counter_key = 'hourly_statistics';
    
    // Check if counter is setup
    if (Yii::$app->redisCounter->exists($counter_key)) {
        // Atomic increment counter with 1
        Yii::$app->redisCounter->incr($counter_key);
    } else {
        // Create counter set value to 1 and let it exist for 1 hour
        Yii::$app->redisCounter->set($counter_key, 1, 60*60);
    }
```