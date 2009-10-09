<?php
require_once dirname(dirname(__FILE__)) . '/Auth/Hatena.php';
require_once 'config.php';

$hatena_auth = new Auth_Hatena(
            $config['api_key'],
            $config['secret']
);
//$hatena_auth->json_parser = 'json';
//$hatena_auth->json_parser = 'Jsphon';
//$hatena_auth->json_parser = 'Services_JSON';

$message = '';

if(array_key_exists('cert', $_GET)) {
    if($user = $hatena_auth->login($_GET['cert'])) {
        $message = "Welcome " . htmlspecialchars($user['name']) . "!";
    } else {
        $message = "Authorization Failed";
    }
}
?>
<html>
<body>
<h1>Hatena API Auth Sample(PHP)</h1>
<p>
<?php
if($message){
    echo($message);
} else {
    echo('<a href="' . htmlspecialchars($hatena_auth->uri_to_login()) . '">login</a>');
}
?>
</p>
</body>
</html>
