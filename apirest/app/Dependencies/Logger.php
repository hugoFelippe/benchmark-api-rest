<?php

namespace App\Dependencies;

use App\SettingsInterface;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * @var SettingsInterface
     */
    protected $settings;

    public function __constructor(SettingsInterface $settings)
    {
        $this->settings = $settings->get('logger');

        $slack = new SlackWebhookHandler(
            $this->settings['slack.webhook'],
            null,
            null,
            true,
            "🔥",
            false,
            true,
            MonologLogger::DEBUG
        );

        $handlers = [$slack];
        $processors = [];

        parent::__construct($this->settings['name'], $handlers, $processors);
    }
}
