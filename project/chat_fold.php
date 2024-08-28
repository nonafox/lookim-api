<?php

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $udata = sql_query1('SELECT id FROM user WHERE FIND_IN_SET(?, name) > 0 LIMIT 1', [$user]);
    if (! $udata)
      throw new Exception();
    if (sql_exec_count('UPDATE chat SET folded = 1 - folded, edited_time = ? WHERE time = ? LIMIT 1', [
        time_microtime(), $_POST['time_id']
      ]))
      api_callback(1);
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
