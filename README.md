# yii2-scheduler

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A scheduled task manager for Yii2 applications. Easily schedule and manage recurring tasks with a user-friendly interface and CLI commands.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Install using the following command:

```bash
composer require hbalkhi/yii2-scheduler
```

After installing the package, you need to configure the module in your application.

## Configuration

Update your `config/console.php` file:

```php
'bootstrap' => ['log', 'scheduler'],
'modules' => [
    'scheduler' => ['class' => 'hbalkhi\scheduler\Module'],
],
'components' => [
    'errorHandler' => [
        'class' => 'hbalkhi\scheduler\ErrorHandler'
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\EmailTarget',
                'mailer' =>'mailer',
                'levels' => ['error', 'warning'],
                'message' => [
                    'to' => ['admin@example.com'],
                    'from' => [$params['adminEmail']],
                    'subject' => 'Scheduler Error - ####SERVERNAME####'
                ],
                'except' => [
                ],
            ],
        ],
    ],
]
```

Also add this to the top of your `config/console.php` file to handle scheduler events:

```php
\yii\base\Event::on(
    \hbalkhi\scheduler\console\SchedulerController::className(),
    \hbalkhi\scheduler\events\SchedulerEvent::EVENT_AFTER_RUN,
    function ($event) {
        if (!$event->success) {
            foreach($event->exceptions as $exception) {
                throw $exception;
            }
        }
    }
);
```

To use the scheduler GUI in your web application, add this to your `config/web.php`:

```php
'bootstrap' => ['log', 'scheduler'],
'modules' => [
    'scheduler' => ['class' => 'hbalkhi\scheduler\Module'],
],
```

After configuration, create a `tasks` directory in your project root and run the migrations to set up the database tables:

```bash
php yii migrate up --migrationPath=vendor/hbalkhi/yii2-scheduler/src/migrations
```

## GUI Controller

To enable the scheduler GUI in your admin panel, create a controller like this:

```php
<?php

namespace app\modules\admin\controllers;

use yii\web\Controller;

/**
 * Class SchedulerController
 * @package app\modules\admin\controllers
 */
class SchedulerController extends Controller
{
    public function actions()
    {
        return [
            'index' => [
                'class' => 'hbalkhi\scheduler\actions\IndexAction',
                'view' => '@scheduler/views/index',
            ],
            'update' => [
                'class' => 'hbalkhi\scheduler\actions\UpdateAction',
                'view' => '@scheduler/views/update',
            ],
            'view-log' => [
                'class' => 'hbalkhi\scheduler\actions\ViewLogAction',
                'view' => '@scheduler/views/view-log',
            ],
        ];
    }
}
```

## Creating Tasks

Create task files in your project's `tasks` directory. All task class names must end with `Task`.

### Example Task

Create `AlphabetTask.php` in your `tasks` directory:

```php
<?php
namespace app\tasks;

/**
 * Class AlphabetTask
 * @package app\tasks
 */
class AlphabetTask extends \hbalkhi\scheduler\Task
{
    public $description = 'Prints the alphabet';
    public $schedule = '0 * * * *';
    
    public function run()
    {
        foreach (range('A', 'Z') as $letter) {
            echo $letter;
        }
    }
}
```

The `$schedule` property uses [Cron Expression](http://en.wikipedia.org/wiki/Cron#Examples) syntax. In the example above, the task runs at the start of every hour.


### Running the tasks

Scheduler provides an intuitive CLI for executing tasks, below are some examples

```bash
# list all tasks and their status
$ php yii scheduler

# run the task if due
$ php yii scheduler/run --taskName=AlphabetTask

# force the task to run regardless of schedule
$ php yii scheduler/run --taskName=AlphabetTask --force

# run all tasks
$ php yii scheduler/run-all

# force all tasks to run
$ php yii scheduler/run-all --force
```

In order to have your tasks run automatically simply setup a crontab like so

```bash
*/5 * * * * admin php /path/to/my/app/yii scheduler/run-all > /dev/null &
```

### Events

Events are triggered before and after task execution at both the task and global level.

#### Task Level

```php
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_BEFORE_RUN, function ($event) {
    Yii::trace($event->task->className . ' is about to run');
});
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_AFTER_RUN, function ($event) {
    Yii::trace($event->task->className . ' just ran '.($event->success ? 'successfully' : 'and failed'));
});
```

#### Global Level

```php
$application->on(\hbalkhi\scheduler\events\SchedulerEvent::EVENT_AFTER_RUN, function ($event) {
    if (!$event->success) {
        foreach($event->exceptions as $exception) {
            throw $exception;
        }
    }
});
```

You could throw the exceptions at the task level, however this will prevent further tasks from running.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
