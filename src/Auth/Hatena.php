<?php
/**
 * Hatena Authentication API
 * see: http://auth.hatena.ne.jp/
 *
 * Yuichi Tateno <secondlife@no.spam@hatena.ne.jp>
 * TSURUOKA Naoya <tsuruoka@labs.cybozu.co.jp>
 *
 * distributed under the MIT licence.
 *
 * example of use:
 * <code>
 * $hatena_auth = new Hatena_API_Auth('your api key', 'secret key');
 * $hatena_auth->uri_to_login();
 * // http://auth.hatena.ne.jp/auth?api_key=eab1f476f8f053ae4af086068017abe8&api_sig=9b192e80287aa75e4739105cfe7a9c45
 * $user = $hatena_auth->login($_GET['cert']);
 * </code>
 *
 */
class Auth_Hatena
{
    var $VERSION = '1.0.0';
    var $api_key;
    var $secret;
    var $json;

    /**
     * json_parser
     * @var string (json | jsphon | services_json)
     */
    var $json_parser = 'jsphon';

    var $base_uri = 'http://auth.hatena.ne.jp';
    var $auth_path = '/auth';
    var $json_path = '/api/auth.json';

    function Auth_Hatena($api_key, $secret)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
    }

    function uri_to_login() {
        $uri = $this->base_uri . $this->auth_path;
        return $uri . $this->_get_query_string( array(
            'api_key' => $this->api_key,
        ));
    }

    function login($cert) {
        $uri = $this->base_uri . $this->json_path;
        $uri .= $this->_get_query_string( array(
            'api_key' => $this->api_key,
            'cert' => $cert,
        ));
        $handle = fopen($uri, 'rb');
        $contents = '';
        while (!feof($handle)) { $contents .= fread($handle, 8192); }
        fclose($handle);

        $json = $this->_to_json($contents);

        if ($json['has_error'] === true) {
            return false;
        } else {
            return $json['user'];
        }
    }

    function _get_query_string($request) {
        $query = array_merge(
            $request,
            array(
                'api_sig' => $this->api_sig($request)
            )
        );
        return $this->_querynize($query);
    }

    function _to_json($contents) {

        switch(strtolower($this->json_parser)) {
        case 'json' :
            $result = $this->convertObject2Array(json_decode($contents));
            break;
        case 'jsphon' :
            require_once 'Jsphon/Decoder.php';
            $this->json = new Jsphon_Decoder();
            $result = $this->json->decode($contents);
            break;
        case 'services_json' :
            require_once('JSON.php');
            $this->json = new Services_JSON();
            $result = $this->convertObject2Array($this->json->decode($contents));
            break;
        }

        return $result;

    }

    function convertObject2Array($array)
    {
        if (!is_object($array) && !is_array($array)) {
            return false;
        }

        if (is_object($array)) {
            $array = get_object_vars($array);
        }

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $array[$key] = get_object_vars($value);
                $this->convertObject2Array($array);
            }
        }

        return $array;
    }

    function api_sig($args) {
        $sig = $this->secret;
        $keys = array_keys($args);
        sort($keys);
        foreach($keys as $key) {
            $sig .= $key . $args[$key];
        }
        return md5($sig);
    }

    function _querynize($query) {
        $ary = array();
        foreach($query as $key => $value) {
            $ary[] = "${key}=${value}";
        }
        return '?' . join('&', $ary);
    }

    function getVersion()
    {
        return $this->VERSION;
    }
}
?>
