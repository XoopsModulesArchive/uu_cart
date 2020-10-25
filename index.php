<?php

require '../../mainfile.php';
if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/function.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/cart.session.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/transporter.class.php';

$gid = '';
$gcid = '';
$gid = '';

if ($_GET) {
    $NEW_GET = array_map(
        create_function(
            '$a',
            '{
			$a = strtolower($a);
			$a = str_replace(array(";", "delete", "update", "insert", "drop"), "", $a);
			return trim(htmlspecialchars(strip_tags($a), ENT_QUOTES));
		}
		'
        ),
        $_GET
    );
}

$sessions = trim($_REQUEST['PHPSESSID']);
$sess_ip = $_SERVER['REMOTE_ADDR'];

$UU = new u_cart_session($sessions, $sess_ip);

if ($_POST && isset($_POST['bid']) && isset($_POST['gid'])) {
    if (!XoopsSecurity::checkReferer()) {
        redirect_header($_SERVER['SCRIPT_NAME'], 2, _UUCART_REFERER);

        exit;
    }

    $bid = trim(htmlspecialchars(strip_tags($_POST['bid']), ENT_QUOTES | ENT_HTML5)); //if (isset($_POST['bid']))
    $gid = trim(htmlspecialchars(strip_tags($_POST['gid']), ENT_QUOTES | ENT_HTML5)); //if (isset($_POST['bid']))
    //if (isset($bid) && isset($gid)) {
    $UU->u_cart_alldelete_sessions($gid, $bid);

    redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, _UUCART_BK_CLEAR);

    exit;

    //}
}  
    switch (true) {
        case (isset($NEW_GET['target_img']) && isset($NEW_GET['width']) && isset($NEW_GET['height'])):
            uu_cart_picture($NEW_GET['target_img'], (int)$NEW_GET['width'], (int)$NEW_GET['height']);
            exit;
        case (isset($NEW_GET['ucart']) && 'main' == $NEW_GET['ucart']):
            if (isset($NEW_GET['item'])) {
                $gid = (int)$NEW_GET['item'];
            }
            $GLOBALS['xoopsOption']['template_main'] = 'u_item.main.html';
            $UU->get_main_goods_detail($gid);
            break;
        case (isset($NEW_GET['ucart']) && 'site_status' == $NEW_GET['ucart']):
            $GLOBALS['xoopsOption']['template_main'] = 'site_status.html';
            $UU->get_notation_view();
            break;
        case (isset($NEW_GET['ucart']) && 'category' == $NEW_GET['ucart']):
            if (isset($NEW_GET['categoryid'])) {
                $gcid = (int)$NEW_GET['categoryid'];
            }
            $item = false;
            $page = (isset($NEW_GET['page'])) ? (int)$NEW_GET['page'] : 0;
            $GLOBALS['xoopsOption']['template_main'] = 'u_item.category.html';
            $UU->get_category_goods_detail($gcid, $page);
            break;
        case (isset($NEW_GET['ucart']) && 'allcategory' == $NEW_GET['ucart']):
            $GLOBALS['xoopsOption']['template_main'] = 'u_all.category.html';
            $UU->get_allcategory();
            break;
        case (isset($NEW_GET['ucart']) && 'carton' == $NEW_GET['ucart'])://3
            $gid = (int)$NEW_GET['item'];
            if (32 != mb_strlen($sessions)) {
                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=main&amp;item' . $gid, 3, _UUCART_BAD_SESSION);

                exit;
            }
            $stock = (int)$NEW_GET['purchase'];
            $res = $UU->u_cart_buy_sessions($gid, $stock);
            if ($res) {
                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, _UUCART_BASKET_IN);

                exit;
            }  
                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=main&amp;item=' . $gid, 3, _UUCART_SORRY . _UUCART_DB_ERROR . '<br>' . _UUCART_DB_ERROR00);
                exit;

            break;
        case (isset($NEW_GET['ucart']) && 'view_basket' == $NEW_GET['ucart']):
            $GLOBALS['xoopsOption']['template_main'] = 'u_cart.basket.html';
            $res = $UU->u_cart_disp_Basket();
            break;
        case (isset($NEW_GET['ucart']) && 'cartedit' == $NEW_GET['ucart']):
            $s1 = (int)$NEW_GET['v'];
            $s2 = (int)$NEW_GET['s'];
            if ($s1 == $s2) {
                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, _UUCART_BK_SAME);

                exit;
            }
            $flg = false;
            $diff = abs($s1 - $s2);
            if ($s1 < $s2) {
                $flg = true;
            }
            $UU->u_cart_edit_sessions((int)$NEW_GET['item'], $NEW_GET['ses'], $diff, $flg);
            redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, sprintf(_UUCART_BK_CHANGE, $s1, $s2));
            exit;
        case (isset($NEW_GET['ucart']) && 'cartdelline' == $NEW_GET['ucart']):
            $UU->u_cart_delete_sessions((int)$NEW_GET['item'], $NEW_GET['ses']);
            redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=view_basket', 3, _UUCART_BK_DELETE_L);
            exit;
        case (isset($NEW_GET['ucart']) && 'postmail' == $NEW_GET['ucart']):
            $item = (int)$NEW_GET['item'];
            $form = recommend_friendmail($item);
            $dat = $UU->make_goods_detail($item);
            $GLOBALS['xoopsOption']['template_main'] = 'recommend_friendmail.html';
            $xoopsTpl->assign('dat', $dat);
            $xoopsTpl->assign('title', _UCART_ITEM_PMAIL);
            $xoopsTpl->assign('form', $form);
            break;
        case ($_POST && isset($_POST['mail'])):
            $item = (int)$_POST['gid'];
            if (empty($_POST['fromname'])) {
                $_SESSION['mail_post'] = $_POST;

                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=postmail&amp;item=' . $item, 3, sprintf(_UUCART_USER_ERROR00, _UCART_MAILS_YOURNAME));

                exit;
            }
            if (empty($_POST['mailfrom'])) {
                $_SESSION['mail_post'] = $_POST;

                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=postmail&amp;item=' . $item, 3, sprintf(_UUCART_USER_ERROR00, _UCART_MAILS_YOURMAIL));

                exit;
            }
            if (empty($_POST['tomail'])) {
                $_SESSION['mail_post'] = $_POST;

                redirect_header($_SERVER['SCRIPT_NAME'] . '?ucart=postmail&amp;item=' . $item, 3, sprintf(_UUCART_USER_ERROR00, _UCART_MAILS_TO));

                exit;
            }
            unset($_SESSION['mail_post']);
            $res = recommend_sent($_POST, $item);
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _UCART_RECOMMEND_SENT . '<br>' . $res);
            exit;
        case (isset($NEW_GET['ucart']) && 'policy' == $NEW_GET['ucart']):
            $GLOBALS['xoopsOption']['template_main'] = 'uu.policy.html';
            $UU->get_policy_view();
            break;
        case (isset($NEW_GET['ucart']) && 'pickup' == $NEW_GET['ucart']):
            if (isset($NEW_GET['mod'])) {
                $gcid = (int)$NEW_GET['mod'];
            }
            $GLOBALS['xoopsOption']['template_main'] = 'u_item.center.html';
            $UU->get_default_main($gcid);
            break;
        case (isset($NEW_GET['ucart']) && 'ranking' == $NEW_GET['ucart']):
            $GLOBALS['xoopsOption']['template_main'] = 'u_item.ranking.html';
            $UU->make_center_ranking();
            break;
        default:
            $GLOBALS['xoopsOption']['template_main'] = 'module.welcome.html';
            $UU->get_welcome();
            break;
    }

require XOOPS_ROOT_PATH . '/footer.php';

//Particularly I am not entering the completion tag
