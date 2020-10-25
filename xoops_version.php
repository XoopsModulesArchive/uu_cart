<?php

$modversion['name'] = _MI_U_CART_NAME;
$modversion['version'] = '0.50';
$modversion['description'] = _MI_U_CART_NAME_DESC;
$modversion['credits'] = '<a href="http://u-u-club.ddo.jp/~XOOPS/" target="_blank">xoops@unadon_v</a>';
$modversion['author'] = 'xoops@unadon_v';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 1;
$modversion['image'] = 'images/logo.gif';
$modversion['dirname'] = 'uu_cart';

// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
//$modversion['sqlfile']['pgsql'] = "sql/pgsql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = 'buy_user';
$modversion['tables'][1] = 'goods';
$modversion['tables'][2] = 'u_pickup';
$modversion['tables'][3] = 'goods_category';
$modversion['tables'][4] = 'goods_review';
$modversion['tables'][5] = 'uu_magazine';
$modversion['tables'][6] = 'cart_sessions';
$modversion['tables'][7] = 'buy_table';
$modversion['tables'][8] = 'buy_addressee';
$modversion['tables'][9] = 'u_main_setting';
$modversion['tables'][10] = 'zipcode';

//Install
$modversion['onInstall'] = 'include/install.php';

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminmenu'] = 'admin/menu.php';
$modversion['adminindex'] = 'admin/index.php';

// Menu
$modversion['hasMain'] = 1;
$modversion['sub'][1]['name'] = _MI_U_CART_BLOCK_CATEGORY;
$modversion['sub'][1]['url'] = 'index.php?ucart=allcategory';
$modversion['sub'][2]['name'] = _MI_U_CART_BLOCK_PICKUP_R;
$modversion['sub'][2]['url'] = 'index.php?ucart=pickup';
$modversion['sub'][3]['name'] = _MI_U_CART_BLOCK_PICKUP;
$modversion['sub'][3]['url'] = 'index.php?ucart=ranking';

// Templates
$modversion['templates'][1]['file'] = 'site_status.html';
$modversion['templates'][1]['description'] = 'Site Information';
$modversion['templates'][2]['file'] = 'u_item.main.html';
$modversion['templates'][2]['description'] = '';
$modversion['templates'][3]['file'] = 'u_item.category.html';
$modversion['templates'][3]['description'] = 'Category different';
$modversion['templates'][4]['file'] = 'u_all.category.html';
$modversion['templates'][4]['description'] = 'All Category different';
$modversion['templates'][5]['file'] = 'u_item.center.html';
$modversion['templates'][5]['description'] = 'Center default';
$modversion['templates'][6]['file'] = 'u_cart.basket.html';
$modversion['templates'][6]['description'] = 'Basket Display';
$modversion['templates'][7]['file'] = 'u_item.review.html';
$modversion['templates'][7]['description'] = 'Item review';
$modversion['templates'][8]['file'] = 'u_cart.transport.html';
$modversion['templates'][8]['description'] = 'Customer information input';
$modversion['templates'][9]['file'] = 'u_cart.transport2.html';
$modversion['templates'][9]['description'] = 'Customer purchase input';
$modversion['templates'][10]['file'] = 'u_cart.transport3.html';
$modversion['templates'][10]['description'] = 'Customer confirmation';
$modversion['templates'][11]['file'] = 'u_cart.thanks.html';
$modversion['templates'][11]['description'] = 'thanks';
$modversion['templates'][12]['file'] = 'u_cart.orderstatus.html';
$modversion['templates'][12]['description'] = 'Order status';
$modversion['templates'][13]['file'] = 'u_item.ranking.html';
$modversion['templates'][13]['description'] = 'Ranking';
$modversion['templates'][14]['file'] = 'module.welcome.html';
$modversion['templates'][14]['description'] = 'Module TOP';
$modversion['templates'][15]['file'] = 'recommend_friendmail.html';
$modversion['templates'][15]['description'] = 'recommend friend with mail';
$modversion['templates'][16]['file'] = 'uu.policy.html';
$modversion['templates'][16]['description'] = 'Privacy Policy';

// Blocks
$modversion['blocks'][1]['file'] = 'block.php';
$modversion['blocks'][1]['name'] = _MI_U_CART_BLOCK_CATEGORY;
$modversion['blocks'][1]['description'] = 'block category';
$modversion['blocks'][1]['show_func'] = 'b_uu_cart_category';
$modversion['blocks'][1]['template'] = 'uu_cart_block.category.html';

$modversion['blocks'][2]['file'] = 'block.php';
$modversion['blocks'][2]['name'] = _MI_U_CART_BLOCK_PICKUP_R;
$modversion['blocks'][2]['description'] = 'block picup random';
$modversion['blocks'][2]['show_func'] = 'b_uu_cart_pickup_random';
$modversion['blocks'][2]['template'] = 'uu_cart_block.pickup_r.html';

$modversion['blocks'][3]['file'] = 'block.php';
$modversion['blocks'][3]['name'] = _MI_U_CART_BLOCK_INFO;
$modversion['blocks'][3]['description'] = 'block information';
$modversion['blocks'][3]['show_func'] = 'b_uu_cart_information';
$modversion['blocks'][3]['template'] = 'uu_cart_block.siteinfo.html';

$modversion['blocks'][4]['file'] = 'block.php';
$modversion['blocks'][4]['name'] = _MI_U_CART_BLOCK_PICKUP_L;
$modversion['blocks'][4]['description'] = 'block picup lock';
$modversion['blocks'][4]['show_func'] = 'b_uu_cart_pickup_lock';
$modversion['blocks'][4]['template'] = 'uu_cart_block.pickup_r.html';

$modversion['blocks'][5]['file'] = 'block.php';
$modversion['blocks'][5]['name'] = _MI_U_CART_BLOCK_WELCOME;
$modversion['blocks'][5]['description'] = 'welcome block';
$modversion['blocks'][5]['show_func'] = 'b_uu_cart_welcome';
$modversion['blocks'][5]['template'] = 'module.welcome_block.html';

// Search
/*
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = "include/search.inc.php";
$modversion['search']['func'] = "u_multibbs_search";
*/

/*
$modversion['config'][1]['name'] = 'shop_name';
$modversion['config'][1]['title'] = '_MI_U_CART_SHOP_NAME';
$modversion['config'][1]['description'] = '';
$modversion['config'][1]['formtype'] = 'textbox';
$modversion['config'][1]['valuetype'] = 'text';
$modversion['config'][1]['default'] = $xoopsConfig['sitename'].''.$xoopsConfig['slogan'];
*/