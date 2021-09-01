<?php

namespace App;

use App\Dependencies\Logger;

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
            'dbDev' => false,
            'displayErrorDetails' => true, // Should be set to false in production
            'logError'            => false,
            'logErrorDetails'     => false,
            'logger' => [
                'name' => 'slim-app',
                'level' => Logger::DEBUG,
                'slack.webhook' => 'https://hooks.slack.com/services/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'
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
