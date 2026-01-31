<?php

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $udata = sql_query1('SELECT id FROM user WHERE real_name = ? LIMIT 1', [$user]);
    if (! $udata)
      throw new Exception();
    $time = time_microtime();
    if (sql_exec_count('INSERT INTO chat (time, edited_time, user_id, type, msg, submsg, folded) VALUES (?, ?, ?, "common", ?, "", 0)', [
      $time, $time, $udata['id'], $_POST['msg']
    ])) {
      api_callback(1);
    }
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
