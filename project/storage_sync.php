<?php

  $type = $_POST['type'];
  // `user` here stands for key `real_name`
  $user = $_POST['user'];
  try {
    if ($type == 'from') {
      if (! $_POST['json']) {
        $res = sql_query1('SELECT storage_version FROM user WHERE real_name = ? LIMIT 1', [$user]);
        api_callback(1, [ 'version' => $res['storage_version'] ]);
      }
      else {
        $res = sql_query1('SELECT storage_data, storage_version FROM user WHERE real_name = ? LIMIT 1', [$user]);
        api_callback(1, [ 'json' => json_decode($res['storage_data']), 'version' => $res['storage_version'] ]);
      }
    }
    elseif ($type == 'to') {
      $json = $_POST['json'];
      $version = $_POST['version'];
      sql_exec('UPDATE user SET storage_data = ?, storage_version = ? WHERE real_name = ? LIMIT 1', [$json, $version, $user]);
      api_callback(1);
    }
    elseif ($type == 'to_single') {
      $key = $_POST['key'];
      $val = $_POST['val'];
      $version = $_POST['version'];
      $res = sql_query1('SELECT storage_data FROM user WHERE real_name = ? LIMIT 1', [$user]);
      $json = json_decode($res['storage_data'], true);
      $json[$key] = $val;
      sql_exec('UPDATE user SET storage_data = ?, storage_version = ? WHERE real_name = ? LIMIT 1', [json_encode($json), $version, $user]);
      api_callback(1);
    }
  }
  catch (Exception $ex) {
    api_callback(0);
  }
?>
