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

require_once __DIR__ . '/admin_customer.class.php';

$uu_customer = new admin_customer();

$mod = '';
$file_err = '';
$item = '';
$buy = '';

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return trim(htmlspecialchars(strip_tags($a)));'),
        $_GET
    );
}

xoops_cp_header();

$src = $uu_customer->get_notation();

if (!is_writable(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads')) {
    xoops_error(_AD_UUCART_ERR04);
} elseif (!is_array($src)) {
    xoops_error(_AD_UUCART_ERR00);
} elseif (3 == $uu_customer->get_use_im()) {
    xoops_error(_AD_UUCART_ERR05);
}

switch (true) {
    case (isset($NEW_GET['uu_cust']) && false !== strpos($NEW_GET['uu_cust'], "mod_")):
        break;
    case (isset($NEW_GET['uu_cust']) && 'sent' == $NEW_GET['uu_cust']):
        $res = $uu_customer->_make_sent(1);
        if (!$res) {
            echo '<h4>' . _AD_UUCART_GOMENU . '</h4>' . _AD_UUCART_NOTHING;
        }
        break;
    case ($_POST && isset($_POST['deposit'])):
        if ('' == $_POST['receipt']) {
            redirect_header('admin_customer.php?uu_cust=msend&amp;buy=' . (int)$_POST['buy'], 3, _AD_UUCART_EMPTYRECEIPT);

            exit;
        }
        unset($_POST['deposit']);
        unset($_POST['slipnum']);
        $n_post = $uu_customer->_post_sanitiz($_POST);
        $res = $uu_customer->receipt($n_post['receipt'], (int)$n_post['buy']);
        if ($res) {
            redirect_header('admin_customer.php?uu_cust=sent', 3, _AD_UUCART_RECEIPT . '<br>' . _AD_UUCART_SUCCESSDB);

            exit;
        }  
            redirect_header('admin_customer.php?uu_cust=sent', 3, _AD_UUCART_ERR01);
            exit;

        break;
    case (isset($NEW_GET['uu_cust']) && 'msend' == $NEW_GET['uu_cust']):
        $buy = (int)$NEW_GET['buy'];
        $res = $uu_customer->_msent($buy);
        break;
    /*
case (isset($NEW_GET['uu_cust']) && $NEW_GET['uu_cust'] == 'sales'):
    if (isset($NEW_GET['buy'])) $buy = intval($NEW_GET['buy']);
    $res = $uu_customer->_sales();
    _ex($res);exit;
    break;
    */
    case ($_POST && isset($_POST['cancel'])):
        $buy = (int)$_POST['buy'];
        echo '
	<h4>' . _AD_UUCART_CANCEL_DB . '</h4>
	<h4>' . _AD_UUCART_GOMENU . '</h4>
	<form name="delete" id="delete" action="admin_customer.php" method="POST">' . _AD_UUCART_CANCEL_DB . ' ::
	<input type="submit" name="canceltrue" id="canceltrue" value="' . _AD_UUCART_CANCEL_DB . '"><input type="hidden" name="buy" id="buy" value="' . $buy . '"></form>';
        break;
    case ($_POST && isset($_POST['canceltrue'])):
        $buy = (int)$_POST['buy'];
        $res = $uu_customer->cancel($buy);
        if ($res) {
            redirect_header('index.php', 3, _AD_UUCART_SUCCESSDB);

            exit;
        }  
            redirect_header('index.php', 3, _AD_UUCART_ERR01);
            exit;

        break;
    case ($_POST && (isset($_POST['sent_mail']) || isset($_POST['sent_n']))):
        $mail = (isset($_POST['sent_mail'])) ? true : false;
        if (isset($_POST['sent_mail'])) {
            unset($_POST['sent_mail']);
        }
        if (isset($_POST['sent_n'])) {
            unset($_POST['sent_n']);
        }
        $n_post = $uu_customer->_post_sanitiz($_POST);
        $res = $uu_customer->delivery($slipnum, (int)$n_post['buy'], $mail);
        if (true === $mail) {
            redirect_header('index.php', 3, $res);

            exit;
        } elseif ($res) {
            redirect_header('admin_customer.php?uu_cust=sent', 3, _AD_UUCART_SENTSUCCESS);

            exit;
        }  
            redirect_header('admin_customer.php?uu_cust=sent', 3, _AD_UUCART_ERR01);
            exit;

        break;
    case ($_POST && isset($_POST['editid'])):
        $n_post = $uu_customer->_post_sanitiz($_POST);
        $res = $uu_customer->_user_profile($n_post);
        if ($res) {
            redirect_header('index.php', 3, _AD_UUCART_SUCCESSDB);

            exit;
        }  
            redirect_header('index.php', 3, _AD_UUCART_ERR01);
            exit;

        break;
    case (isset($NEW_GET['uu_cust']) && 'view' == $NEW_GET['uu_cust']):
        $id = (isset($NEW_GET['id'])) ? (int)$NEW_GET['id'] : null;
        $page = (isset($NEW_GET['page'])) ? (int)$NEW_GET['page'] : 0;
        $sort = $NEW_GET['sort'] ?? 'id';
        $order = $NEW_GET['order'] ?? 'ASC';
        if ($id) {
            $src = $uu_customer->_buy_user($id);

            $uu_customer->_personal_history($src, $id);

            break;
        }
        $uu_customer->customer_modify($id, $page, $sort, $order);
        break;
    case (isset($NEW_GET['uu_cust']) && 'mail' == $NEW_GET['uu_cust']):
        $id = (int)$NEW_GET['id'];
        $uu_customer->_mail($id);
        break;
    case (isset($NEW_GET['uu_cust']) && 'custmod' == $NEW_GET['uu_cust']):
        $id = (int)$NEW_GET['id'];
        $uu_customer->customer_modify($id);
        break;
    case ($_POST && isset($_POST['mail_sent'])):
        $n_post = $uu_customer->_post_sanitiz($_POST);
        $mes = $uu_customer->_mail_send($n_post);
        redirect_header('admin_customer.php?uu_cust=view&amp;id=' . (int)$n_post['id'], 3, $mes);
        exit;
    case (isset($NEW_GET['uu_cust']) && 'gc' == $NEW_GET['uu_cust']):
        $res = $uu_customer->_cart_sessions_gc();
        redirect_header('index.php', 3, _AD_UUCART_GC);
        exit;
    default:
        break;
}

xoops_cp_footer();
