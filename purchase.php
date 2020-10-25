<?php

require '../../mainfile.php';
if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require XOOPS_ROOT_PATH . '/header.php';

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/cart.session.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/Myformdate.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/function.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/transporter.class.php';

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

$UU = new u_cart_session($sessions, $sess_ip);
$error = '';
$_error = '';
$form = [];

switch (true) {
    case (isset($NEW_GET['ucart']) && 'finish' == $NEW_GET['ucart']):
        $uid = (int)$NEW_GET['u'];
        $res = $UU->u_cart_uid2buyuser(10, $uid);
        $user = $UU->_get_user();
        $GLOBALS['xoopsOption']['template_main'] = 'u_cart.thanks.html';
        $xoopsTpl->assign('user', $user);
        require XOOPS_ROOT_PATH . '/footer.php';
        exit;
    case (isset($NEW_GET['ucart']) && 'order_status' == $NEW_GET['ucart']):
        $goods = $UU->u_cart_sessions_order_status();
        unset($goods['t1_total']);
        unset($goods['t2_total']);
        $other['flg'] = ($goods) ? '' : 'null';
        $other['mes'] = _UUCART_NO_GOODS_STATUS;
        $GLOBALS['xoopsOption']['template_main'] = 'u_cart.orderstatus.html';
        $xoopsTpl->assign('other', $other);
        $xoopsTpl->assign('goods', $goods);
        require XOOPS_ROOT_PATH . '/footer.php';
        exit;
    case ($_POST && isset($_POST['decision'])):
        unset($_POST['decision']);
        $n_post = $UU->u_cart_buyuser_sanitiz($_POST);
        $res = $UU->u_cart_order_decision($n_post);
        unset($_SESSION['UU_NEXT']);
        $old = 'UUSESS_' . session_id();
        $_SESSION[$old] = session_id();
        session_regenerate_id();
        redirect_header('purchase.php?ucart=finish&amp;u=' . $res['id'], 3, _UCART_BUY_THANKS);
        exit;
    case ($_POST && isset($_POST['passsend'])):
        $n_post = $UU->u_cart_buyuser_sanitiz($_POST);
        if (!isset($n_post['pass'])) {
            $error = sprintf(_UUCART_USER_ERROR00, _UUCART_USER_PASS0);
        } elseif (!isset($n_post['tel']) && !isset($n_post['email'])) {
            $error = sprintf(_UUCART_USER_PM_ERR);
        } else {
            $form = $UU->u_cart_buyuser_DB_passwd($n_post);
        }
        if (!$form) {
            $form = $UU->u_cart_input_form($n_post);
        }
        $error .= $UU->u_cart_get_err();
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
    case ($_POST && isset($_POST['submit'])):
        unset($_POST['submit']);
        $res = false;
        $n_post = $UU->u_cart_buyuser_sanitiz($_POST);
        if (!isset($n_post['name'])) {
            $error = sprintf(_UUCART_USER_ERROR00, _UUCART_USER_NAME);
        }
        if (!isset($n_post['email'])) {
            $error .= '<br>' . sprintf(_UUCART_USER_ERROR00, _UUCART_USER_MAIL);
        }
        if (isset($n_post['email'])) {
            $_error = (checkEmail($n_post['email'])) ? '' : _UUCART_USER_ERR_MAIL;

            $error .= $_error;
        }
        if (!isset($n_post['tel'])) {
            $error .= '<br>' . sprintf(_UUCART_USER_ERROR00, _UUCART_USER_TEL);
        }
        if (!$n_post['zip']) {
            $error .= '<br>' . sprintf(_UUCART_USER_ERROR00, _UUCART_USER_ZIP);
        }
        if (!isset($n_post['pref'])) {
            $error .= '<br>' . sprintf(_UUCART_USER_ERROR01, _UUCART_USER_PREF);
        }
        if (!isset($n_post['address'])) {
            $error .= '<br>' . sprintf(_UUCART_USER_ERROR00, _UUCART_USER_CITY);
        }
        if (false === $n_post) {
            $NEW_GET['gid'] = null;

            $NEW_GET['bid'] = null;
        }
        if ('' == $error) {
            $res = $UU->u_cart_buyuser_DB_FalsenessSet($n_post, $mod = false);

            if ($res) {
                redirect_header('purchase2.php?ucart=next', 3, _UUCART_USER_INPUT);

                exit;
            }  

            redirect_header($_SERVER['SCRIPT_NAME'], 3, _UUCART_DB_ERROR);

            exit;
        }
        $form = $UU->u_cart_input_form($n_post);
        break;
    default:
        break;
}
if (isset($NEW_GET['bid'])) {
    $src = $UU->u_cart_session2buy($NEW_GET['gid'], $NEW_GET['bid']);
} else {
    $src = $UU->u_cart_session2buy();
}
if (is_string($src)) {
    redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, $src);

    exit;
}
$other['course'] = _UCART_COURSE1_IMG;
$other['sub_total'] = $src['sub_total'];
$other['map'] = '<map name="course">
		  <area href="' . $src['map_url'] . '" shape="rect" coords="310,0,405,30">
		  <area shape="default" nohref>
		</map>';
unset($src['map_url']);
unset($src['sub_total']);
$GLOBALS['xoopsOption']['template_main'] = 'u_cart.transport.html';
if (!$form) {
    $form = $UU->u_cart_input_form();
}
if (isset($error)) {
    $xoopsTpl->assign('error', $error);
}
$xoopsTpl->assign('other', $other);
$xoopsTpl->assign('goods', $src);
$xoopsTpl->assign('form', $form);

//Particularly I am not entering the completion tag
require XOOPS_ROOT_PATH . '/footer.php';
