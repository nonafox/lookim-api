<?php
    require_once __DIR__ . '/config/index.php';
    require_once __DIR__ . '/require/index.php';
    
    function text_url2host($url = '') {
        return strtolower(explode('/', $url)[2]);
    }
    function text_format_dir($dir = '') {
        if (! $dir) return '/';
        return rtrim(trim(preg_replace('/\\/+/iu', '/', $dir)), '/');
    }
    
    function __pregQuote($pattern = '', $delimiter = '/') {
        return preg_replace_callback(
            '/[\\.\\\\\\+\\*\\?\\^\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:\\-\\#\\' . $delimiter . ']/u',
            function ($matches) {
                return '\\' . $matches[0];
            },
            $pattern
        );
    }
    function __pregMatch($pattern = '', $str = '', &$dict = [], $pathmode = false) {
        global $__keys, $__pathmode;
        $__keys = [];
        $__pathmode = $pathmode;
        $pattern = preg_replace('/\\s/u', '', $pattern);
        $pattern = __pregQuote($pattern, '/');
        $pattern = preg_replace_callback(
            '/\\[(.+?)\\]/iu',
            function ($matches) {
                global $__keys, $__pathmode;
                $__keys[] = $matches[1][0];
                return $__pathmode ? '(.+?)' : '([^\\.]+?)';
            },
            $pattern,
            -1, $_count,
            PREG_OFFSET_CAPTURE
        );
        $pattern = str_ireplace('\\*', $pathmode ? '(.+?)' : '([^\\.]+?)', $pattern);
        $res = preg_match('/^' . $pattern . '$/iu', $str, $matches, PREG_OFFSET_CAPTURE);
        if (! $res)
            return false;
        array_splice($matches, 0, 1);
        foreach ($matches as $k => $v) {
            $dict[$__keys[$k]] = $v[0];
        }
        unset($__keys);
        unset($__pathmode);
        return true;
    }
    function __dictReplace($dict = [], $str = '', $url_encode = false) {
        global $__dict;
        $__dict = $dict;
        $res = preg_replace_callback(
            '/\\$\\{(.+?)\\}/iu',
            function ($matches) {
                global $__dict, $url_encode;
                $key = $matches[1][0];
                if ($url_encode)
                    return urlencode($__dict[$key]);
                else
                    return $__dict[$key];
            },
            $str,
            -1, $_count,
            PREG_OFFSET_CAPTURE
        );
        unset($__dict);
        return $res;
    }
    function __parseUrl($dir = '', $host = '') {
        $table = c::$ROUTER;
        $dict = [];
        $domain = strtolower($host);
        $match = null;
        foreach ($table as $k_ => $v) {
            $k_ = explode('|', $k_);
            $ok = false;
            foreach ($k_ as $k) {
                if (__pregMatch($k, $domain, $dict)) {
                    $match = $v;
                    foreach ($v as $k2_ => $v2) {
                        if (! is_string($k2_)) continue;
                        $k2_ = explode('|', $k2_);
                        $ok2 = false;
                        foreach ($k2_ as $k2) {
                            if (__pregMatch($k2, $dir, $dict, true)) {
                                $match = $v2;
                                $ok2 = true;
                                break;
                            }
                        }
                        if ($ok2) break;
                    }
                    $ok = true;
                    break;
                }
            }
            if ($ok) break;
        }
        if ($match === null)
            return false;
        $template = '' . $match[0];
        $model = $match[1] ?: 'default';
        
        $parts = explode('?', $template);
        $res = $parts[0];
        $params = $parts[1] ?: '';
        $res = __dictReplace($dict, $res);
        $params = __dictReplace($dict, $params, true);
        $res = str_ireplace('$', $dir, $res);
        $params = str_ireplace('$', urlencode($dir), $params);
        $base = __DIR__ . '/project/';
        $base2 = __DIR__ . '/model/' . $model . '/';
        $paths = [];
        $paths[] = $base . $res;
        $paths[] = $base . $res . '.php';
        $paths[] = $base . $res . '/index.php';
        
        $ok = false;
        foreach ($paths as $k => $v) {
            if (file_exists($v) && (! is_dir($v))) {
                $ok = $v;
            }
        }
        if (! $ok) {
            $ok = $base2 . '/404.php';
            http_response_code(404);
        }
        $ok = text_format_dir($ok);
        
        return ['url' => $ok, 'params' => $params, 'model' => $model];
    }
    function __parseDomain($domain = '') {
        foreach (c::$ROUTER as $k => $v)
            if (__pregMatch($k, $domain))
                return true;
        return false;
    }
    function __handleParams($params = '') {
        if (! $params)
            return;
        foreach (explode('&', $params) as $v) {
            $parts = explode('=', $v);
            $_GET[urldecode($parts[0])] = urldecode($parts[1]);
        }
    }
    
    $__url = text_format_dir($_GET['__url']);
    $__host = $_SERVER['HTTP_HOST'];
    $__urlData = __parseUrl($__url, $__host);
    $__model_url = __DIR__ . '/model/' . $__urlData['model'];
    chdir(__DIR__ . '/model');
    __handleParams($__urlData['params']);
    include_once $__model_url . '/head.php';
    $finfo = finfo_open(FILEINFO_MIME);
    $mime = finfo_file($finfo, $__urlData['url']);
    if (pathinfo($__urlData['url'])['extension'] == 'php') {
        chdir(pathinfo($__urlData['url'])['dirname']);
        include_once $__urlData['url'];
    }
    else {
        header('Content-type: ' . $mime);
        echo(file_get_contents($__urlData['url']));
    }
    finfo_close($finfo);
    chdir(__DIR__ . '/model');
    include_once $__model_url . '/tail.php';
?>