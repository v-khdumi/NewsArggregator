<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 28.04.15
 * Time: 10:49
 */

include "../application/config.php";

$link = mysql_connect($settings['db']['host'], $settings['db']['user'], $settings['db']['password']);

if (!$link) {
    die('Fail: ' . mysql_error());
}






mysql_close($link);