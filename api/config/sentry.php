<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/29/2020
 * Time: 9:59 AM
 */

return array(
    'dsn' => env('SENTRY_DSN', 'http://e0d62da1c6784f92b7d0402bf6a77858@ec2-3-10-211-84.eu-west-2.compute.amazonaws.com/2'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
);
