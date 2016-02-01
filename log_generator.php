<?php
/**
 * Генерирует файлы логов для тестирования
 */
header('Content-Type: text/html; charset=UTF-8');

$user_agent = $_SERVER['HTTP_USER_AGENT'];

function getOS(){

    global $user_agent;

    $os_platform = "Unknown OS Platform";

    $os_array = array(
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value){
        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }
    }

    return $os_platform;
}

function getBrowser(){

    global $user_agent;

    $browser = "Unknown Browser";

    $browser_array = array(
        '/msie/i'       =>  'Internet Explorer',
        '/trident/i'    =>  'Internet Explorer 11',
        '/firefox/i'    =>  'Firefox',
        '/safari/i'     =>  'Safari',
        '/chrome/i'     =>  'Chrome',
        '/edge/i'       =>  'Edge',
        '/opera/i'      =>  'Opera',
        '/netscape/i'   =>  'Netscape',
        '/maxthon/i'    =>  'Maxthon',
        '/konqueror/i'  =>  'Konqueror',
        '/mobile/i'     =>  'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }
    return $browser;
}

$user_os      = getOS();
$user_browser = getBrowser();
$ip           = $_GET['mode'] == 1 ? file_get_contents("http://ipecho.net/plain") : $_SERVER['REMOTE_ADDR'];

if ($_GET['mode'] == 1){
    $ip = file_get_contents("http://ipecho.net/plain");
    $url_from = 'https://e.mail.ru/messages/inbox/';
    $url_to = 'https://www.sencha.com/forum/?mkt_tok=3RkMMJWWfF9wsRols6TBZKXonjHpfsX54uslWqGzg4kz2EFye%2BLIHETpodcMTctrPa%2BTFAwTG5toziV8R7PCKM1338YQWhPj';
}
else{
    $ip = $_SERVER['REMOTE_ADDR'];;
    $url_from = 'http://docs.sencha.com/extjs/6.0/6.0.0-classic';
    $url_to = 'http://examples.sencha.com/extjs/6.0.0/examples/kitchensink/?profile=crisp#paging-grid';
}

$device_details = "<strong>Browser: </strong>".$user_browser."<br /><strong>Operating System: </strong>".$user_os."";

print_r($device_details);

echo("<br /><br /><br />".$_SERVER['HTTP_USER_AGENT']."");

$handle = fopen("E:/log_viewer/log/log1.log", "a+");
for ($i = 0; $i < 100; $i++){
    $s = date('d.m.Y').'|'.date('H:i:s', time() + $i) .'|'.$ip.'|'.$url_from.'|'.$url_to."\r\n";
    fwrite($handle, $s);
}

fclose($handle);

$handle = fopen("E:/log_viewer/log/log2.log", "a+");
$s = $ip.'|'.$user_browser.'|'.$user_os."\r\n";
fwrite($handle, $s);
fclose($handle);

echo '<br>Логи сгенерированы';