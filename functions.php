<?php

/**
 * functions.php
 */

// 年齢認証に利用する固定ページのスラッグを指定
define('AGE_GATE_SLUG', 'gate');

require get_template_directory() . '/inc/classes/class-age-verification.php';
