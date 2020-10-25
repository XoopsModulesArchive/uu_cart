<?php

require '../../mainfile.php';
if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require XOOPS_ROOT_PATH . '/header.php';

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/function.class.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

if ($_GET) {
    $NEW_GET = array_map(
        create_function('$a', 'return htmlspecialchars(trim(strip_tags($a)), ENT_QUOTES);'),
        $_GET
    );
}

$uid = ($xoopsUser && is_object($xoopsUser)) ? $xoopsUser->uid() : 0;
$UU = new uu_cart_function();

switch (true) {
    case (isset($NEW_GET['ucart']) && 'postreview' == $NEW_GET['ucart']):
        $gid = (int)$NEW_GET['item'];
        $detail = $UU->make_goods_detail($gid);
        $form = new XoopsThemeForm(_UCART_ITEM_POSTREVIEW, 'review', 'review.php', 'POST');
        $im = str_replace('" alt', '" float:left; alt', $detail['itemimg']);
        $str = $im . '<span style="font-size:12px;">' . $detail['top'] . '</span><br>' . $detail['exp'];
        $form->addElement(new XoopsFormLabel(_UCART_ITEM_NAME, $str));
        $form->addElement(new XoopsFormDhtmlTextArea(_UCART_ITEM_REVPOST, 'exp', '', 7, 50), true);
        $formrank = new XoopsFormSelect(_UCART_ITEM_EVALUATION, 'rank');
        $formrank->addOptionArray(['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5']);
        $form->addElement($formrank);
        $form->addElement(new XoopsFormHidden('gid', $gid));
        $form->addElement(new XoopsFormHidden('id', $uid));
        $form->addElement(new XoopsFormButton('', 'submitrev', _UCART_ITEM_SUBMIT, 'submit'));
        $form->display();
        break;
    case (isset($NEW_GET['ucart']) && 'review' == $NEW_GET['ucart']):
        $page = (isset($NEW_GET['page'])) ? (int)$NEW_GET['page'] : 0;
        $gid = (int)$NEW_GET['item'];
        $result = $UU->u_rev_select($gid, $page);
        $check = ($result) ? 't' : 'f';
        $page = $UU->u_rev_pages($gid);
        $detail = $UU->make_goods_detail($gid);
        $detail['revtitle'] = _UCART_ITEM_REVIEW0;
        $GLOBALS['xoopsOption']['template_main'] = 'u_item.review.html';
        $xoopsTpl->assign('check', $check);
        $xoopsTpl->assign('detail', $detail);
        $xoopsTpl->assign('result', $result);
        break;
    case ($_POST && isset($_POST['submitrev'])):
        unset($_POST['submitrev']);
        $n_post = array_map(
            create_function(
                '$a',
                'if (is_int($a)) {
				return intval($a);
			} else {
				return $a;
			}
			'
            ),
            $_POST
        );
        $result = $UU->u_rev_insert($n_post);
        //redirect_header('index.php', 3, _UCART_POSTREVIEW_THANKS);
        redirect_header('review.php?ucart=review&amp;item=' . $n_post['gid'], 3, _UCART_POSTREVIEW_THANKS);
        exit;
    default:
        break;
}

//Particularly I am not entering the completion tag
require XOOPS_ROOT_PATH . '/footer.php';
