<?php
  class c {
    public static $ROUTER = [
      'api.lookim.cn' => [
        0 => '/$'
      ]
    ];
    public static $STATIC_DIR = '/www/wwwroot/i.lookim.cn';
    public static $STATIC_URL_PREFIX = 'https://i.lookim.cn';
    public static $VAPTCHA_SMS_URL = 'https://sms.vaptcha.com/send';
  }
  
  require_once __DIR__ . '/secrets.php';
?>