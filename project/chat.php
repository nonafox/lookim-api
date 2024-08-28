<?php

  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    $udata = sql_query1('SELECT id FROM user WHERE FIND_IN_SET(?, name) > 0 LIMIT 1', [$user]);
    $data = sql_query('SELECT * FROM chat ORDER BY time DESC LIMIT ' . $_POST['n'], []);
    $max_edited_time = sql_query1('SELECT MAX(edited_time) FROM chat LIMIT 1', []);
    $curr_version = [$max_edited_time['MAX(edited_time)'], count($data)];
    if (json_encode(json_decode($_POST['version'], true)) != json_encode($curr_version))
      api_callback(1, [ 'list' => array_reverse($data), 'version' => $curr_version ]);
  }
  catch (Exception $ex) {  }

  api_callback(0);
?>
