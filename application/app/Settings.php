<?php

namespace App;

use Monolog\Logger;

class Settings implements SettingsInterface
{
    /**
     * @var array
     */
    private $settings;

    /**
     * Settings constructor.
     * @param array $settings
     */
    public function __construct()
    {
        $this->settings = [
            'slim.error.display_details' => true,
            'slim.error.log' => false,
            'slim.error.log_details' => false,
            'dbDev' => false,
            'logger' => [
                'name' => 'slim-app',
                'level' => Logger::DEBUG,
                'slack.webhook' => getenv('SLACK_WEBHOOK')
            ],
            'db' => [
                'driver' => 'mysql',
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'database' => getenv('DB_NAME'),
                'username' => getenv('DB_USER'),
                'password' => getenv('DB_PASSWORD'),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_bin'
            ]
        ];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
    }

    public function set(string $key = '', $value)
    {
        if (isset($this->settings[$key])) {
            $this->settings[$key] = $value;
        }
    }
}
