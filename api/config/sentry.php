<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/29/2020
 * Time: 9:59 AM
 */

return array(
    'dsn' => env('SENTRY_DSN', 'http://d31e58ab1f8e476fbd2c6161b41fc15a@172.30.30.125:9000/7'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
);
