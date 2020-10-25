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

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.class.php';

require_once __DIR__ . '/admin_sales.class.php';

$uu_sales = new admin_sales();

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return trim(htmlspecialchars(strip_tags($a)));'),
        $_GET
    );
}

xoops_cp_header();

$src = $uu_sales->get_notation();

if (!is_writable(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads')) {
    xoops_error(_AD_UUCART_ERR04);
} elseif (!is_array($src)) {
    xoops_error(_AD_UUCART_ERR00);
} elseif (3 == $uu_sales->get_use_im()) {
    xoops_error(_AD_UUCART_ERR05);
}

switch (true) {
    case (isset($NEW_GET['uu_cust']) && 'sales' == $NEW_GET['uu_cust']):
        $uu_sales->_sales();
        break;
    case (isset($NEW_GET['uu_cust']) && 'sales_view' == $NEW_GET['uu_cust']):
        $sday = $NEW_GET['sday_y'] . '-' . $NEW_GET['sday_m'] . '-' . $NEW_GET['sday_d'];
        $eday = $NEW_GET['eday_y'] . '-' . $NEW_GET['eday_m'] . '-' . $NEW_GET['eday_d'];
        $order = $NEW_GET['order'];
        $res = $uu_sales->_buy($sday, $eday, $order);
        $uu_sales->_format($res, $sday, $eday);
        break;
    case ($_POST):
        foreach ($_POST as $key => $val) {
            if (false !== strpos($key, "change_")) {
                [, $buy] = explode('_', $key);

                break;
            }
        }
        foreach ($_POST as $key => $val) {
            if ($key == 'receipt_' . $buy) {
                $num = $val;

                break;
            }
        }
        if (0 == $num) {
            redirect_header('index.php', 3, _AD_UUCART_ERR08);

            exit;
        }
        $res = $uu_sales->receipt($num, $buy);
        redirect_header('index.php', 3, _AD_UUCART_SUCCESSDB);
        exit;
    default:
        break;
}

xoops_cp_footer();
