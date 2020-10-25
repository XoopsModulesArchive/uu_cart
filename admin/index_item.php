<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';
if (file_exists(__DIR__ . '/../language/' . $xoopsConfig['language'] . '/main.php')) {
    require dirname(__DIR__) . '/language/' . $xoopsConfig['language'] . '/main.php';
} else {
    require dirname(__DIR__) . '/language/english/main.php';
}

if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
    trigger_error('Access Denied');

    exit('Access Denied');
}

require_once __DIR__ . '/admin_items.class.php';

$uu_item = new uu_item_admin();

$mod = '';
$item = '';
$err = '';

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return trim(strip_tags($a));'),
        $_GET
    );
}

xoops_cp_header();

switch (true) {
    case (isset($NEW_GET['uu_cart']) && 'pickup' == $NEW_GET['uu_cart']):
        if (isset($NEW_GET['item']) && '--' != $NEW_GET['item']) {
            $item = (int)$NEW_GET['item'];
        }
        $uu_item->uu_admin_pickup($item);
        break;
    case ($_POST && (isset($_POST['pickup']))):
        $result = $uu_item->uu_admin_pickup_set();
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup', 5, _AD_UUCART_SUCCESSDB);
        }
        $err = $uu_item->get_error();
        redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup', 5, _AD_UUCART_ERR01 . '<br >' . $err);
        break;
    case (isset($NEW_GET['uu_cart']) && 'pickupoff' == $NEW_GET['uu_cart']):
        if (isset($NEW_GET['item'])) {
            $item = (int)$NEW_GET['item'];
        }
        $result = $uu_item->uu_admin_pickup_off($item);
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup', 5, _AD_UUCART_SUCCESSDB);
        }
        redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup', 5, _AD_UUCART_ERR01);
        break;
    case (isset($NEW_GET['uu_cart']) && 'imageadmin' == $NEW_GET['uu_cart']):
        $uu_item->imageadminister();
        break;
    case (isset($NEW_GET['uu_cart']) && 'image' == $NEW_GET['uu_cart']):
        uu_item_admin::imagedelete(htmlspecialchars($NEW_GET['im'], ENT_QUOTES | ENT_HTML5));
        redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=imageadmin', 5, _AD_UUCART_IM_DEL);
        break;
    case (isset($NEW_GET['uu_cart']) && 'pic' == $NEW_GET['uu_cart']):
        $im = htmlspecialchars($NEW_GET['im'], ENT_QUOTES | ENT_HTML5);
        $gid = (int)$NEW_GET['n'];
        $res = $uu_item->imagedelete_db($gid, $im);
        if ($res) {
            redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=imageadmin', 5, _AD_UUCART_IM_DEL);
        }
        $err = $uu_item->get_error();
        redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=imageadmin', 5, $err);
        break;
    default:
        break;
}

xoops_cp_footer();
