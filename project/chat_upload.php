<?php

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $udata = sql_query1('SELECT id FROM user WHERE real_name = ? LIMIT 1', [$user]);
    if (! $udata)
      throw new Exception();
    $dir = c::STATIC_DIR . '/' . text_random(16);
    mkdir($dir);
    rename($_FILE['file']['tmp_name'], $dir . '/' . $_FILE['file']['name']);
    $url = $STATIC_URL_PREFIX . '/' . $new_name;
    $time = time_microtime();
    if (sql_exec_count('INSERT INTO chat (time, edited_time, user_id, type, msg, folded) VALUES (?, ?, ?, "file", ?, 0)', [
      $time, $time, $udata['id'], $url
    ])) {
      api_callback(1, [ 'url' => $url ]);
    }
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
