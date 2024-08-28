<?php

    error_reporting(0);
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    // 正则表达式长度限制解除，参考：https://blog.csdn.net/leafgw/article/details/50381298
    ini_set('pcre.backtrack_limit', -1);

    function text_random($dig = 16, $md5mode = false) {
        $str = $md5mode ? 'abcdef0123456789' : 'abcdefghijklmnopqrstuvwxyz0123456789';
        $res = '';
        for ($i = 0; $i < $dig; $i ++) {
            $res .= $str[rand(0, strlen($str) - 1)];
        }
        return $res;
    }
    function time_microtime() {
        // 这个命名错了哈（应为 毫秒），但是已经用在老多地方了，就懒得改了，有这意思就行
        return intval(microtime(true) * 1000);
    }
    function api_callback($status = 1, $data = null, $msg = '') {
        header('Content-type: application/json;');
        $resp = ['status' => $status];
        if ($data)
            $resp['data'] = $data;
        if ($msg)
            $resp['msg'] = $msg;
        exit(json_encode($resp));
    }
    
    $_sql_pdo = new PDO('mysql:host=' . s::$SQL_CONFIG['host'] . ';dbname=' . s::$SQL_CONFIG['dbname'], s::$SQL_CONFIG['user'], s::$SQL_CONFIG['pass']);
    // 注意加了这个才能在sql执行错误时抛出Exception
    $_sql_pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    function sql_exec($sql = '', $param = []) {
        try {
            global $_sql_pdo;
            $psm = $_sql_pdo -> prepare($sql);
            $psm -> execute($param);
            return $psm;
        }
        catch (Exception $ex) {
            api_callback(0, null, '操作数据库失败了呢~' . $ex -> getMessage() );
        }
    }
    function sql_exec_count($sql = '', $param = []) {
        return sql_exec($sql, $param) -> rowCount();
    }
    function sql_query($sql = '', $param = []) {
        // 不输出含num => value（只有key => value）形式的结果数组的秘密在这：(PDO::FETCH_ASSOC)参数。
        return sql_exec($sql, $param) -> fetchAll(PDO::FETCH_ASSOC);
    }
    function sql_query1($sql = '', $param = []) {
        return sql_exec($sql, $param) -> fetch(PDO::FETCH_ASSOC);
    }
    function sql_query_count($sql = '', $param = []) {
        // TNND被坑了，columnCount拿的是列数，就是字段名数，不是行数。脑塞了，一开始竟然用columnCount取了行数
        return count(sql_exec($sql, $param) -> fetchAll());
    }
    function sql_newId($table = '', $key = 'id', $firstval = 1) {
        return sql_query1('SELECT MAX(' . $key . ') FROM ' . $table)['MAX(' . $key . ')'] + $firstval;
    }
    function sql_fieldsExcept($table = '', $exceptFields = []) {
        // 查询表的所有字段名，参考：https://www.cnblogs.com/TTonly/p/12132651.html
        $result = sql_query('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA LIKE ? AND TABLE_NAME LIKE ? ORDER BY ORDINAL_POSITION', [s::$SQL_CONFIG['dbname'], $table]);
        $return = [];
        foreach ($result as $k => $v) {
            $n = $v['COLUMN_NAME'];
            if (! in_array($n, $exceptFields)) {
                $return[] = $n;
            }
        }
        return implode(', ', $return);
    }
    function sql_errInfo($psm = null) {
        return $psm -> errorInfo();
    }
    
    function http($url = '', $data = null, $header = [], $cookie = [], $auto2JSON = true) {
        /* 来源：https://www.cnblogs.com/dadiaomengmei/p/11447689.html */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if (! empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        if (! empty($cookie)) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        
        if ($auto2JSON) {
            $json = json_decode($result, true);
            if ($json)
                return $json;
        }
        return $result;
    }
    function http_json($url = '', $dataArray = [], $header = [], $cookie = []) {
        $jsonHeader = [
            'Content-type: application/json; charset=\'utf-8\'',
            'Accept: application/json'
        ];
        return http($url, json_encode($dataArray), array_merge($jsonHeader, $header), $cookie);
    }
?>