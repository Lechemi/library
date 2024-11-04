<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function redirect($url, $permanent = false): void
{
    header("Location: $url", true, $permanent ? 301 : 302);
    exit();
}