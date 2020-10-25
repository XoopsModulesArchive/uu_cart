<?php

require '../../mainfile.php';
if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require XOOPS_ROOT_PATH . '/header.php';

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/cart.session.transporter.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/cart.session.class.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

$NEW_GET = [];

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return htmlspecialchars(trim(strip_tags($a)), ENT_QUOTES);'),
        $_GET
    );
}

$uid = ($xoopsUser && is_object($xoopsUser)) ? $xoopsUser->uid() : 0;
$sessions = trim($_REQUEST['PHPSESSID']);
$sess_ip = $_SERVER['REMOTE_ADDR'];

$UU = new u_cart_transporter($sessions, $sess_ip);
$error = '';
$_error = '';
$form = [];

switch (true) {
    case (isset($NEW_GET['ucart']) && 'next' == $NEW_GET['ucart']):
        $form = $UU->u_cart_addressee_form();
        break;
    case ($_POST && isset($_POST['addcheck'])):
        $form = $UU->u_cart_addressee_form();
        break;
    case ($_POST && isset($_POST['zipaddress'])):
        if (!isset($_POST['zip'])) {
            $error = sprintf(_UUCART_USER_ERROR00, _UUCART_USER_ZIP);
        }
        $form = $UU->u_cart_zip2address($_POST);
        break;
    case ($_POST && isset($_POST['addresscity'])):
        if (!isset($_POST['pref']) || '--' == $_POST['pref']) {
            $error = sprintf(_UUCART_USER_ERROR01, _UUCART_USER_PREF);
        }
        $form = $UU->u_cart_pref2city($_POST);
        break;
    case ($_POST && isset($_POST['citytown'])):
        if (!isset($_POST['s_citys']) || '--' == $_POST['s_citys']) {
            $error = sprintf(_UUCART_USER_ERROR01, _UUCART_USER_S_CITY);
        }
        $form = $UU->u_cart_city2town($_POST);
        break;
    case ($_POST && isset($_POST['addtozip'])):
        $form = $UU->u_cart_add2zip($_POST);
        break;
    case (isset($NEW_GET['user']) && 'check' == $NEW_GET['user']):
        if (isset($NEW_GET['buy']) && !preg_match('/[a-z0-9]{32}/i', $NEW_GET['buy'])) {
            redirect_header(XOOPS_URL . '/', 3, _UUCART_USER_ERROR02);

            exit;
        }
        $form = $UU->u_cart_customer_form('check', $NEW_GET['buy']);
        if (!$form) {
            redirect_header(XOOPS_URL . '/', 3, _UUCART_USER_ERROR02);

            exit;
        }
        $form2 = $UU->u_cart_customer_addressee_form($NEW_GET['buy']);
        $src = $UU->u_cart_sessions_order_status($NEW_GET['buy']);
        $other['total1'] = number_format($src['t1_total']);
        $other['total2'] = number_format($src['t2_total']);
        $other['total'] = number_format($src['t1_total'] + $src['t2_total']);
        unset($src['t1_total']);
        unset($src['t2_total']);
        $GLOBALS['xoopsOption']['template_main'] = 'u_cart.transport3.html';
        $xoopsTpl->assign('other', $other);
        $xoopsTpl->assign('goods', $src);
        $xoopsTpl->assign('form', $form);
        $xoopsTpl->assign('form2', $form2);
        require XOOPS_ROOT_PATH . '/footer.php';
        exit;
    case ($_POST && isset($_POST['pasubmit'])):
        unset($_POST['pasubmit']);
        $n_post = $UU->u_cart_buyuser_sanitiz($_POST);
        $src = $UU->u_cart_session2DB($n_post);
        $_SESSION['UU_NEXT'] = session_id();
        $total = number_format($src['total_1'] + $src['total_2']);
        $other = [
            'transporter_name' => $src['transporter_name'],
            'mypref' => $src['mypref'],
            'customerpref' => $src['customerpref'],
            'course' => _UCART_COURSE3_IMG,
            'total1' => number_format($src['total_1']),
            'total2' => number_format($src['total_2']),
            'packages' => $src['packages'],
            'allnums' => $src['allnums'],
            'total' => $total,
        ];
        $other['map'] = '<map name="course">
		  <area href="purchase.php" shape="rect" coords="15,0,245,30">
		  <area shape="default" nohref>
		</map>';
        $other['cargo'] = _UUCART_FORM . ' ' . $src['mypref'] . ' ==&gt;&gt; ' . _UUCART_TO . ' ' . $src['customerpref'] . ' ( ' . $src['transporter_name'] . ' )';
        unset($src['transporter_name']);
        unset($src['mypref']);
        unset($src['customerpref']);
        unset($src['total_1']);
        unset($src['total_2']);
        unset($src['packages']);
        unset($src['allnums']);
        unset($src['fax']);
        $form = $UU->u_cart_customer_form();
        $form2 = $UU->u_cart_customer_addressee_form();
        $GLOBALS['xoopsOption']['template_main'] = 'u_cart.transport3.html';
        $xoopsTpl->assign('other', $other);
        $xoopsTpl->assign('goods', $src);
        $xoopsTpl->assign('form', $form);
        $xoopsTpl->assign('form2', $form2);
        require XOOPS_ROOT_PATH . '/footer.php';
        exit;
    default:
        break;
}
$src = $UU->u_cart_session2buy();
$GLOBALS['xoopsOption']['template_main'] = 'u_cart.transport2.html';
$other['course'] = _UCART_COURSE2_IMG;
$other['sub_total'] = $src['sub_total'];
$other['map'] = '<map name="course">
		  <area href="purchase.php" shape="rect" coords="15,0,130,30">
		  <area shape="default" nohref>
		</map>';
unset($src['map_url']);
unset($src['sub_total']);
if (isset($error)) {
    $xoopsTpl->assign('error', $error);
}
$xoopsTpl->assign('other', $other);
$xoopsTpl->assign('goods', $src);
$xoopsTpl->assign('form', $form);

//Particularly I am not entering the completion tag
require XOOPS_ROOT_PATH . '/footer.php';
