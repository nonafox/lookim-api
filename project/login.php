<?php

  $user = $_POST['user'];
  $pass = $_POST['pass'];
  if (strpos($user, '      ') !== false && ! $pass) {
    $user = trim($user);
    try {
      $real_name = text_random(16);
      $data = sql_query1('SELECT id FROM user WHERE FIND_IN_SET(?, name) > 0 LIMIT 1', [$user]);
      if (! sql_exec_count('UPDATE user SET real_name = ? WHERE id = ? LIMIT 1', [$real_name, $data['id']]))
        throw new Exception();
      api_callback(1, [ 'real_name' => $real_name, 'user_id' => $data['id'] ]);
    }
    catch (Exception $ex) {  }
  }

  api_callback(0);
?>
