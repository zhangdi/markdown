<?php

define('K_PATH_FONTS', sys_get_temp_dir() . '/tcpdf/fonts/');

if (!file_exists(K_PATH_FONTS)) {
    mkdir(K_PATH_FONTS, 0777, true);
}

defined('FONTS_PATH') or define('FONTS_PATH', K_PATH_FONTS);
