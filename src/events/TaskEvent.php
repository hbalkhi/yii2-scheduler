<?php

namespace hbalkhi\scheduler\events;

use Exception;
use hbalkhi\scheduler\Task;
use yii\base\Event;

class TaskEvent extends Event {
    /**
     * @var Task
     */
    public $task;

    /**
     * @var Exception
     */
    public $exception;

    /**
     * @var bool
     */
    public $success;

    /**
     * @var string
     */
    public $output;

    public $cancel = false;
}
