<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require_once XOOPS_ROOT_PATH . '/modules/uu_cart/include/user_function.class.php';

function b_uu_cart_category()
{
    $u_cart = uu_cart_user::getInstance();

    $block = [];

    $row = $u_cart->get_category_view($on_view = 'ON');

    $block['title'] = _UCART_PLUS_IMG . _MB_U_CART_ALL_CATEGORY;

    $block['url'] = XOOPS_URL . '/modules/uu_cart/index.php?ucart=allcategory';

    $block['arrow'] = _UCART_ARROWR_IMG;

    $i = 0;

    foreach ($row as $key => $val) {
        $val['_url'] = XOOPS_URL . '/modules/uu_cart/index.php?ucart=category&amp;categoryid=' . $val['gcid'];

        $block['categorys'][] = $val;

        $i++;

        if (5 == $i) {
            break;
        }
    }

    return $block;
}

function b_uu_cart_pickup_random()
{
    require_once XOOPS_ROOT_PATH . '/modules/uu_cart/include/user_image.class.php';

    $im = uu_cart_image::getInstance();

    $u_cart = uu_cart_user::getInstance();

    $block = [];

    $row = $u_cart->_pickup($on_view = 'ON');

    if ($row) {
        [$msec, $sec] = preg_split(' ', microtime());

        mt_srand($msec * 123456);

        shuffle($row);

        $block = $row[0];

        $block['im'] = '<a href="' . XOOPS_URL . '/modules/uu_cart/index.php?ucart=main&amp;item=' . $block['gid'] . '">' . $im->image($block['images'], 'block') . '</a>';

        $block['price'] = sprintf(_UCART_PRICE, number_format($block['price']));
    } else {
        $block['top'] = _MB_U_CART_BLOCK_PICK_NONE;
    }

    $block['title'] = _MB_U_CART_BLOCK_PICKUP_R;

    return $block;
}

function b_uu_cart_pickup_lock()
{
    require_once XOOPS_ROOT_PATH . '/modules/uu_cart/include/user_image.class.php';

    $im = uu_cart_image::getInstance();

    $u_cart = uu_cart_user::getInstance();

    $block = [];

    $row = $u_cart->_pickup_lock();

    if ($row) {
        $block = $row;

        $block['im'] = '<a href="' . XOOPS_URL . '/modules/uu_cart/index.php?ucart=main&amp;item=' . $block['gid'] . '">' . $im->image($block['images'], 'block') . '</a>';

        $block['price'] = sprintf(_UCART_PRICE, number_format($block['price']));
    } else {
        $block['top'] = _MB_U_CART_BLOCK_PICKUP_NONE;
    }

    $block['title'] = _MB_U_CART_BLOCK_PICKUP_R;

    return $block;
}

function b_uu_cart_welcome()
{
    $u_cart = uu_cart_user::getInstance();

    $block = [];

    $block = $u_cart->get_welcome();

    return $block;
}

function b_uu_cart_information()
{
    $block = [];

    $block['arrow'] = _UCART_TRIANGLE_IMG;

    $block['url_1'] = XOOPS_URL . '/modules/uu_cart/index.php?ucart=view_basket';

    $block['url_2'] = XOOPS_URL . '/modules/uu_cart/purchase.php?ucart=order_status';

    $block['url_3'] = XOOPS_URL . '/modules/uu_cart/index.php?ucart=site_status';

    $block['url_4'] = XOOPS_URL . '/modules/uu_cart/index.php?ucart=policy';

    $block['basket'] = _UCART_BASKET;

    $block['order_status'] = _UCART_ORDER_STATUS;

    $block['site_status'] = _UCART_SITE_STATUS;

    $block['policy'] = _UCART_SITE_POLICY;

    return $block;
}
