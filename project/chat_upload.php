<?php

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $udata = sql_query1('SELECT id FROM user WHERE real_name = ? LIMIT 1', [$user]);
    if (! $udata)
      throw new Exception();
    $dir = text_random(16);
    mkdir(c::$STATIC_DIR . '/' . $dir);
    move_uploaded_file($_FILES['file']['tmp_name'], c::$STATIC_DIR . '/' . $dir . '/' . $_FILES['file']['name']);
    $url = c::$STATIC_URL_PREFIX . '/' . $dir . '/' . $_FILES['file']['name'];
    $time = time_microtime();
    if (sql_exec_count('INSERT INTO chat (time, edited_time, user_id, type, msg, submsg, folded) VALUES (?, ?, ?, ?, ?, ?, 0)', [
        $time, $time, $udata['id'], $_POST['type'], $url, '' . $_FILES['file']['size']
      ])) {
      api_callback(1, [ 'url' => $url ]);
    }
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
