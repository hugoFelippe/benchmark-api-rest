<?php

namespace App\Dependencies;

use App\SettingsInterface;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    /**
     * @var SettingsInterface
     */
    protected $settings;

    protected $logger;

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

        $this->logger = new MonologLogger($this->settings['name'], $handlers, $processors);
    }

    public function get(): ?MonologLogger
    {
        return $this->logger;
    }
}
