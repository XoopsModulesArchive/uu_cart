<?php

function uu_cart_picture($target_img, $width, $height)
{
    if (!file_exists('./images/uploads/' . $target_img)) {
        $target_img = _UCART_NOWPRINT1_IMG;
    }

    header('Content-type: text/html; charset=EUC-JP');

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja-JP">
<head>
<title>Expand Image</title>
</head>
<body>
<div style="font-size : 13px; text-align : center;">' . _UCART_TOP_CLOSE . '<br>
<a href="javascript:void(0);" onclick="window.close()"><img src="./images/uploads/' . $target_img . '" width="' . $width . '" height="' . $height . '" alt="expand image" style="border:none;"></a></div>
<p style="font-size : 13px; text-align : center;">' . _UCART_C_CLOSE . '<a href="javascript:void(0);" onclick="window.close()"><b style="background-color : #DDDDDD;"> X </b></a></p>
</body>
</html>';
}

function uu_cart_day_options($d, $limit_y)
{
    $date_option = [];

    $ylist = ($limit_y) ? range(1926, $limit_y) : range(1925, 2010);

    $date_option['year']['--'] = '--年';

    $date_option['month']['--'] = '--月';

    $date_option['day']['--'] = '--日';

    $loop = count($ylist);

    for ($i = 0; $i < $loop; $i++) {
        $wareki = '';

        if (1926 == $ylist[$i]) {
            $wareki = ' 大正15年 昭和元年';
        } elseif ($ylist[$i] < 1989) {
            $wareki = ' 昭和' . ($ylist[$i] - 1925) . '年';
        } elseif (1989 == $ylist[$i]) {
            $wareki = ' 昭和' . ($ylist[$i] - 1925) . '年 平成元年';
        } elseif ($ylist[$i] > 1989) {
            $wareki = ' 平成' . ($ylist[$i] - 1988) . '年';
        }

        $date_option['year'][$ylist[$i]] = (string)$ylist[$i] . '年' . $wareki;
    }

    for ($i = 1; $i < 13; $i++) {
        $date_option['month'][$i] = (string)$i . '月';
    }

    if ($d) {
        for ($i = 1; $i < 32; $i++) {
            $date_option['day'][$i] = (string)$i . '日';
        }
    }

    return $date_option;
}

function recommend_friendmail($item)
{
    global $_SESSION;

    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    $from_m = '';

    $m_from = '';

    $tomail = [];

    if (isset($_SESSION['mail_post'])) {
        $from_m = $_SESSION['mail_post']['fromname'];

        $m_from = $_SESSION['mail_post']['mailfrom'];

        $tomail = $_SESSION['mail_post']['tomail'];
    }

    $form = new XoopsThemeForm(_UCART_MAILS_TO_MES, 'recommend', 'index.php', 'POST');

    for ($i = 0; $i < 5; $i++) {
        $form->addElement(new XoopsFormText(_UCART_MAILS_TO . ' ' . ($i + 1), 'tomail[]', 70, 100, $tomail[$i]));
    }

    $form->addElement(new XoopsFormText(_UCART_MAILS_YOURNAME, 'fromname', 40, 100, $from_m));

    $form->addElement(new XoopsFormText(_UCART_MAILS_YOURMAIL, 'mailfrom', 70, 100, $m_from));

    $form->addElement(new XoopsFormHidden('gid', $item));

    $form->addElement(new XoopsFormButton('', 'mail', _UCART_ITEM_SUBMIT, 'submit'));

    return $form->render();
}

function recommend_sent($arr, $item)
{
    global $xoopsConfig;

    $UU = new uu_cart_user();

    $src = $UU->get_goods_form($item);

    $from = trim(htmlspecialchars(strip_tags($arr['mailfrom']), ENT_QUOTES));

    $name = trim(htmlspecialchars(strip_tags($arr['fromname']), ENT_QUOTES));

    $_mail = '';

    $to = [];

    foreach ($arr['tomail'] as $val) {
        $mail = trim(htmlspecialchars(strip_tags($val), ENT_QUOTES));

        if ($_mail == $mail) {
            continue;
        }

        if (checkEmail($mail)) {
            $to[] = $mail;
        }

        $_mail = $mail;
    }

    $to[] = $from;

    $exp = $src['sub'] . "\n" . str_replace(["\r\n", "\r"], "\n", $src['exp']);

    $price = sprintf(_UCART_PRICE, $src['price']);

    $subject = sprintf(_UCART_MAILS_RECOMMEND, $name, $xoopsConfig['sitename']);

    $assign = [
        'UU_NAME' => $name,
        'UU_ITEM' => _UUCART_MAIL00 . $src['top'],
        'UU_EXP' => _UUCART_MAIL01 . $exp,
        'UU_PRICE' => $price,
        'ITEM_URL' => XOOPS_URL . '/modules/uu_cart/index.php?ucart=main&item=' . $item,
        'SLOGAN' => $xoopsConfig['slogan'],
    ];

    return u_cart_Mailer('recommend.tpl', $assign, $to, $subject, $from);
}

function u_cart_Mailer($mailtpl, $assign, $to, $subject, $from = '')
{
    global $xoopsConfig, $xoopsModule, $xoopsMailerConfig;

    $dir = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/japanese/mail_template/';

    if (is_array($to)) {
        $to[] = $xoopsConfig['adminmail'];
    } else {
        $to = [$to, $xoopsConfig['adminmail']];
    }

    $from = (!$from) ? $xoopsConfig['adminmail'] : $from;

    $xoopsMailer = getMailer();

    $xoopsMailer->useMail();

    $xoopsMailer->setTemplateDir($dir);

    $xoopsMailer->setTemplate($mailtpl);

    $xoopsMailer->assign($assign);

    $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);

    $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);

    $xoopsMailer->assign('SITEURL', XOOPS_URL . '/');

    $xoopsMailer->setToEmails($to);

    $xoopsMailer->setFromEmail($from);

    $xoopsMailer->setFromName($xoopsConfig['sitename']);

    $xoopsMailer->setSubject($subject);

    $xoopsMailer->send(true);

    return $xoopsMailer->getSuccess();
}

function zip_format_check($zip)
{
    $zip = preg_replace('/[^0-9]/i', '', $zip);

    if (preg_match('/[0-9]{7}/', $zip)) {
        return preg_replace('/([0-9]{3})([0-9]{4,})/', '\1-\2', $zip);
    }

    return $zip;
}

function tel_format_check($tel)
{
    $tel = preg_replace('/[^0-9]/i', '', $tel);

    $tel_start = '';

    $tel_mid = '';

    $format_tel = '';

    $tel_tail = mb_substr($tel, -4);

    if (10 == mb_strlen($tel) && preg_match('/(^[0][3|6])(.*)/s', $tel)) {
        $tel_start = mb_substr($tel, 0, 2);

        $tel_mid = mb_substr($tel, 3, 4);

        $format_tel = $tel_start . '-' . $tel_mid . '-' . $tel_tail;
    } elseif (10 == mb_strlen($tel)) {
        $tel_start = mb_substr($tel, 0, 3);

        $tel_mid = mb_substr($tel, 4, 7);

        $format_tel = $tel_start . '-' . $tel_mid . '-' . $tel_tail;
    } elseif (11 == mb_strlen($tel)) {
        $tel_start = mb_substr($tel, 0, 4);

        $tel_mid = mb_substr($tel, 5, 3);

        $format_tel = $tel_start . '-' . $tel_mid . '-' . $tel_tail;
    } elseif (preg_match('/(^[0][0-9][0])(.*)/s', $tel)) {
        $_tel = mb_substr($tel, 0, -4);

        $tel_mid = preg_replace('/(^[0][0-9][0])(.*)/si', '\\1-\\2-', $_tel);

        $format_tel = $tel_start . $tel_mid;
    } elseif (mb_strlen($tel) > 6) {
        $_tel = mb_substr($tel, 0, -4);

        $format_tel = $_tel . '-' . $tel_tail;
    }

    return $format_tel;
}

function _ex($src)
{
    echo '<pre>';

    var_export($src);

    echo '</pre>';
}
