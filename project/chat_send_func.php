<?php

  error_reporting(E_ALL);

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $me = sql_query1('SELECT id, SMS_template_id, SMS_template_slot FROM user WHERE real_name = ? LIMIT 1', [$user]);
    if (! $me)
      throw new Exception();
    $time = time_microtime();

    if ($_POST['name'] == 'notify') {
      $you = sql_query1('SELECT phone FROM user WHERE id <> ? LIMIT 1', [$me['id']]);
      $phone = '' . $you['phone'];
      $template_id = $me['SMS_template_id'];
      vaptcha_sms_send($phone, $template_id, [$me['SMS_template_slot']]);
    }
    else {
      throw new Exception();
    }

    if (sql_exec_count('INSERT INTO chat (time, edited_time, user_id, type, msg, submsg, folded) VALUES (?, ?, ?, "function", "", ?, 0)', [
        $time, $time, $me['id'], $_POST['name']
      ])) {
      api_callback(1);
    }
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
