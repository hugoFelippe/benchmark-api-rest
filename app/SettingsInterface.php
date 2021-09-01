<?php

declare(strict_types=1);

namespace App;

interface SettingsInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '');

    public function set(string $key = '', $value);
}
