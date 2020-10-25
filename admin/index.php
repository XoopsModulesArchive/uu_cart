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

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.class.php';
require_once __DIR__ . '/admin_function.class.php';

$uu_form = new uu_cart_admin();

$mod = '';
$file_err = '';
$item = '';

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return trim(strip_tags($a));'),
        $_GET
    );
}

xoops_cp_header();

$src = $uu_form->get_notation();

if (!is_writable(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads')) {
    xoops_error(_AD_UUCART_ERR04);
} elseif (!is_array($src)) {
    xoops_error(_AD_UUCART_ERR00);
} elseif (3 == $uu_form->get_use_im()) {
    xoops_error(_AD_UUCART_ERR05);
}

switch (true) {
    case (isset($NEW_GET['uu_cart']) && false !== strpos($NEW_GET['uu_cart'], "new_")):
        [, $type] = explode('_', $NEW_GET['uu_cart']);
        if (isset($NEW_GET['mod']) && '--' != $NEW_GET['mod']) {
            $mod = (int)$NEW_GET['mod'];
        }
        $uu_form->get_type($type, $mod);
        break;
    case (isset($NEW_GET['uu_cart']) && false !== strpos($NEW_GET['uu_cart'], "mod_")):
        if (isset($NEW_GET['item']) && '--' != $NEW_GET['item']) {
            $item = (int)$NEW_GET['item'];
        }
        $uu_form->get_type($NEW_GET['uu_cart'], $item);
        break;
    case (isset($NEW_GET['uu_cart']) && 'notation' == $NEW_GET['uu_cart']):
        $uu_form->uu_admin_notationbasedonlaw();
        break;
    case (isset($NEW_GET['uu_cart']) && 'general' == $NEW_GET['uu_cart']):
        $uu_form->uu_admin_general_setting();
        break;
    case ($_POST && (isset($_POST['submit']) || isset($_POST['generalset']))):
        $result = $uu_form->uu_cart_post4data();
        $file_err = $uu_form->get_file_err();
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB . '<br>' . $file_err);
        }
        redirect_header('index.php', 3, _AD_UUCART_ERR01);
        break;
    case ($_POST && (isset($_POST['edit']))):
        $result = $uu_form->uu_cart_post4data();
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB);
        }
        redirect_header('index.php', 3, _AD_UUCART_ERR01);
        break;
    case ($_POST && (isset($_POST['addim']))):
        $result = $uu_form->uu_cart_post4data();
        $file_err = $uu_form->get_file_err();
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB . '<br>' . $file_err);
        }
        redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_ERR01);
        break;
    case ($_POST && (isset($_POST['goods']))):
        $result = $uu_form->uu_cart_post4data();
        $file_err = $uu_form->get_file_err();
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB . '<br>' . $file_err);
        }
        redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_ERR01);
        break;
    case ($_POST && (isset($_POST['delete']))):
        $gcid = (int)$_POST['gcid'];
        $result = $uu_form->category_delte($gcid);
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB);
        }
        redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_ERR01);
        break;
    case ($_POST && (isset($_POST['goodsdelete']))):
        $gcid = (int)$_POST['gid'];
        $result = $uu_form->goods_delete($gid);
        if ($result) {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_SUCCESSDB);
        }
        redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_ERR01);
        break;
    default:
        echo '<h4>' . _AD_UUCART_ADMIN_MENU . '</h4>';
        uu_cart_admin_menu();
        break;
}

xoops_cp_footer();

function uu_cart_admin_menu()
{
    global $xoopsModule, $_SERVER, $xoopsDB;

    $zipinstall = ' ';

    $zipfile = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/sql/2KEN_ALL.csv';

    if (file_exists($zipfile)) {
        $sql = 'SELECT count(zid) FROM ' . $xoopsDB->prefix('zipcode');

        $result = $xoopsDB->fetchRow($xoopsDB->query($sql));

        if (!$result || 0 == $result[0]) {
            $zipinstall = '<a href="zipinstall.php">' . _AD_UUCART_ZIP_INSTALL . '</a>';
        } elseif ($result[0] > 0) {
            $zipinstall = '<a href="zipdelete.php">' . _AD_UUCART_ZIP_DELETE . '</a>';
        }
    } else {
        $zipinstall = _AD_UUCART_ZIP_INSTALL . _AD_UUCART_NZIP_FILE;
    }

    echo '
<div class="content" style="text-align:center;">
<table border="0" cellpadding="3" cellspacing="5" width="100%">
    <tbody>
        <tr>
            <td class="even" style="text-align:center;"><a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=general">' . _AD_UUCART_ADMIN . '</a></td>
            <td class="odd" style="text-align:center;"><a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_category">' . _AD_UUCART_NEW_CATEGORY . '</a></td>
            <td class="even" style="text-align:center;"><a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_goods">' . _AD_UUCART_NEW_GOODS . '</a></td>
            <td class="odd" style="text-align:center;">' . $zipinstall . '</td>
        </tr>
        <tr>
            <td class="odd" style="text-align:center;"><a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=notation">' . _AD_UUCART_NOTATION_BASED . '</a></td>
            <td class="even" style="text-align:center;"><a href="index_item.php?uu_cart=pickup">' . _AD_UUCART_PICKUP . '</a></td>
            <td class="odd" style="text-align:center;"><a href="index_item.php?uu_cart=imageadmin">' . _AD_UUCART_IM_MANAGER . '</a></td>
            <td class="even" style="text-align:center;"></td>
        </tr>
        <tr>
            <td class="even" style="text-align:center;"><a href="admin_customer.php?uu_cust=view">' . _AD_UUCART_CUSTOMER . '</a></td>
            <td class="odd" style="text-align:center;"><a href="admin_sales.php?uu_cust=sales">' . _AD_UUCART_ORDERSTATUS . '</a></td>
            <td class="even" style="text-align:center;"><a href="admin_customer.php?uu_cust=sent">' . _AD_UUCART_ORDERSEND . '</a></td>
            <td class="odd" style="text-align:center;"><a href="index.php?uu_cart=new_addimage">' . _AD_ADMIN_MENU_38 . '</a></td>
        </tr>
            <td class="odd" style="text-align:center;">' . _AD_UUCART_STATISTICAL . '</td>
            <td class="even" style="text-align:center;"></td>
            <td class="odd" style="text-align:center;"></td>
            <td class="even" style="text-align:center;"><a href="admin_customer.php?uu_cust=gc">' . _AD_UUCART_CLEANING . '</a></td>
        </tr>
	</tbody>
</table>
</div>';
}
