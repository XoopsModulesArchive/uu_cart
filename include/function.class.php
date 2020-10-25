<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.php';

class uu_cart_function
{
    public $db;

    public $myts;

    public $cart;

    public $im;

    public $url;

    public $Tpl;

    public $module;

    //class object constructor

    public function __construct()
    {
        global $xoopsModule, $xoopsTpl;

        if (empty($xoopsModule) || 'uu_cart' != $xoopsModule->getVar('dirname')) {
            require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';

            $moduleHandler = xoops_getHandler('module');

            $this->module = $moduleHandler->getByDirname('uu_cart');
        } else {
            $this->module = &$xoopsModule;
        }

        if (empty($xoopsTpl) || !is_object($xoopsTpl)) {
            require_once XOOPS_ROOT_PATH . '/class/template.php';

            $this->Tpl = new XoopsTpl();
        } else {
            $this->Tpl = &$xoopsTpl;
        }

        require_once XOOPS_ROOT_PATH . '/modules/' . $this->module->getVar('dirname') . '/include/user_function.class.php';

        require_once XOOPS_ROOT_PATH . '/modules/' . $this->module->getVar('dirname') . '/include/user_image.class.php';

        $this->cart = &uu_cart_user::getInstance();

        $this->im = &uu_cart_image::getInstance();

        $this->url = XOOPS_URL . '/modules/' . $this->module->dirname() . '/';
    }

    public function get_Tpl_instance()
    {
        return $this->Tpl;
    }

    public function get_goods_view($gid)
    {
        return $this->cart->get_goods_view($gid);
    }

    public function get_goods_detail($gid)
    {
        return $this->cart->get_goods_detail($gid);
    }

    public function get_use_ssl()
    {
        return $this->cart->get_use_ssl();
    }

    public function get_notation_view()
    {
        $this->Tpl->assign('title', _UCART_SITE_STATUS);

        $src = $this->cart->get_notation_view();

        if (is_array($src)) {
            $dat = array_values($src);

            $this->Tpl->assign('data', $dat);
        }

        $this->Tpl->assign('column_title', $this->get_notation_title());
    }

    public function get_welcome()
    {
        global $xoopsConfig;

        $src = $this->cart->get_welcome();

        if (!$src) {
            $src = ['top' => $xoopsConfig['sitename'], 'main' => _UUCART_WARMING_UP];
        }

        $this->Tpl->assign('welcome', $src);
    }

    public function get_policy_view()
    {
        $src = $this->cart->get_notation_view();

        $this->Tpl->assign('policy', $src['policy']);
    }

    public function get_category_goods_detail($gcid, $page)
    {
        $this->Tpl->assign('category', $this->make_category_detail($gcid)); //, $page

        $item = $this->_category_goods_detail($gcid, $page);

        if (!$item) {
            $this->Tpl->assign('goods', 'none');
        } else {
            $this->Tpl->assign('goods', $item);
        }
    }

    public function get_main_goods_detail($gid)
    {
        $this->Tpl->assign('detail', $this->make_goods_detail($gid));
    }

    public function get_allcategory()
    {
        $this->Tpl->assign('category', $this->make_allcategory());
    }

    public function image($im, $type)
    {
        return $this->im->image($im, $type);
    }

    public function _goods_stock($gid)
    {
        return $this->cart->_goods_stock($gid);
    }

    public function get_default_main($gcid)
    {
        $this->Tpl->assign('title', $this->module->name() . ' ' . _UCART_SUGGESTION);

        $this->Tpl->assign('selecter', $this->make_selector($gcid));

        if ($gcid) {
            $this->Tpl->assign('goods', $this->_category_goods_detail($gcid, 0));
        } else {
            $this->Tpl->assign('goods', $this->make_center_main());
        }
    }

    public function get_ranking_main($arr)
    {
        $this->Tpl->assign('title', 'RANKING TOP10');

        $this->Tpl->assign('goods', $arr);
    }

    public function make_selector($gcid)
    {
        require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

        $formgoods = false;

        $categorys = $this->cart->get_categorys_array();

        $categorys = str_replace(_UCART_NEW, _UUCART_CATEGORY_S, $categorys);

        if ($gcid) {
            if (true === $this->cart->get_goods_check($gcid)) {
                $set_gid = $gid ?? '--';

                $formgoods = new XoopsFormSelect(_UUCART_SELECT_GOODS, 'gid', $set_gid);

                $goods_arr = $this->cart->get_goods_array($gcid);

                $goods_arr = str_replace(UUCART_NEW_CATEGORY, _UUCART_SELECT_GOODS, $goods_arr);

                $formgoods->addOptionArray($goods_arr);

                $formgoods->setExtra('onChange="goods_change(this)"');
            }
        }

        $form = new XoopsThemeForm(_UCART_SECECT, 'selecter', $this->url, 'POST');

        $set_category = $gcid ?? '--';

        $formcategory = new XoopsFormSelect(_UUCART_CATEGORY_S, 'gcid', $set_category);

        $formcategory->addOptionArray($categorys);

        $formcategory->setExtra('onChange="category_change(this)"');

        $form->addElement($formcategory);

        if ($formgoods) {
            $form->addElement($formgoods);
        }

        return $form->render();
    }

    public function make_center_main()
    {
        $row = $this->cart->get_pickup_detail('ON');

        return $this->_center($row);
    }

    public function make_center_ranking()
    {
        $row = $this->cart->get_ranking_detail();

        $dat = $this->_center($row);

        $ranking = [];

        foreach ($dat as $val) {
            $val['rankim'] = $this->ranking($val['rank']);

            $ranking[] = $val;
        }

        $this->get_ranking_main($ranking);
    }

    public function _center($row)
    {
        $main = [];

        $i = 1;

        if (is_array($row)) {
            foreach ($row as $val) {
                if (!$val['gid']) {
                    break;
                }

                $src = $this->make_goods_detail($val['gid']);

                $src['formname'] = str_replace('shopping', 'shopping' . $i, $src['formname']);

                $src['purchase'] = str_replace('send(document.shopping)', 'send(document.shopping' . $i . ')', $src['purchase']);

                $main[] = $src;

                $i++;
            }

            return $main;
        }

        return false;
    }

    /* Merchandise listing in terms of category
    * param gcid return array
    */

    public function _category_goods_detail($gcid, $page)
    {
        $start = $page * 10;

        $items = false;

        $items = $this->cart->get_goods_list($gcid, $item = 'ON', 10, $start);

        if ($items) {
            $i = 1;

            foreach ($items as $val) {
                $src = $this->_goods_detail($val);

                $src['formname'] = str_replace('shopping', 'shopping' . $i, $src['formname']);

                $src['purchase'] = str_replace('send(document.shopping)', 'send(document.shopping' . $i . ')', $src['purchase']);

                $category[] = $src; //$val+$this->_goods_detail($val);

                $i++;
            }

            return $category;
        }

        return false;
    }

    /* All the category listing
    * return array Category details
    */

    public function make_allcategory()
    {
        $category = [];

        $row = $this->cart->get_category_view($on_view = 'ON');

        foreach ($row as $val) {
            $category[] = $val + $this->_category_detail($val['gcid'], $val);
        }

        return $category;
    }

    /* Details of a category
    * param gcid return array Category details
    */

    public function make_category_detail($gcid)//, $page
    {
        $category = [];

        //$start = $page*10;
        $row = $this->cart->get_category($gcid); //, $page
        $category = $this->_category_detail($gcid, $row);

        return array_merge($row, $category);
    }

    public function _category_detail($gcid, $row)
    {
        $src = $this->cart->_goods_count($gcid);

        $category['linktop'] = '<a href="' . $this->url . '?ucart=category&amp;categoryid=' . $gcid . '">' . $row['top'] . '</a>';

        $category['action'] = $this->url;

        $category['page'] = $this->u_page_count($src['c'], $src['gcid']);

        $category['img'] = $this->im->image($row['images'], 'main');

        $category['img'] = str_replace('"item"', '"CATEGORY IMAGE"', $category['img']);

        $category['bg'] = _UCART_BG1_IMG;

        $category['item_title'] = _UCART_ITEM_CATEGORY;

        return $category;
    }

    /* Details of merchandise
    * param gid return array Merchandise details
    */

    public function make_goods_detail($gid)
    {
        $detail = [];

        $this->cart->hits_count($gid);

        $dat = $this->get_goods_detail($gid, $on_view = 'ON');

        $detail['categoryimg'] = $this->im->image($dat['categoryimage'], 'main') . _UCART_CATEGORY . _UCART_ARROWR_IMG;

        $detail['categorytop'] = '<a href="' . $this->url . '?ucart=category&amp;categoryid=' . $dat['categoryid'] . '">' . $dat['categorytop'] . '</a>';

        $detail['b_rank'] = ($dat['rank']) ? $this->ranking($dat['rank']) : $this->ranking(0);

        $detail['buy_rankimg'] = sprintf(_UCART_ITEM_POPULARITY, $detail['b_rank']);

        $detail['action'] = $this->url;

        $detail['image'] = $this->im->image($dat['images'], 'pickup');

        $detail['im'] = $this->im->other_image($dat['images'], 'pickup');

        $detail['image'] = str_replace('2px;', '15px', $detail['image']);

        $detail['revimg'] = ($dat['rev']) ? '<a href="' . $this->url . '?ucart=main&amp;item=' . $dat['rev'] . '">' . _UCART_REVITEM_IMG . '</a>' : _UCART_REVITEM_IMG;

        $detail['nextimg'] = ($dat['next']) ? '<a href="' . $this->url . '?ucart=main&amp;item=' . $dat['next'] . '">' . _UCART_NEXTITEM_IMG . '</a>' : _UCART_NEXTITEM_IMG;

        $detail['review'] = _UCART_ARROWR1_IMG . ' <a href="' . $this->url . 'review.php?ucart=review&amp;item=' . $dat['gid'] . '">' . _UCART_ITEM_REVIEW . '</a>';

        $detail['postreview'] = _UCART_ARROWR1_IMG . ' <a href="' . $this->url . 'review.php?ucart=postreview&amp;item=' . $dat['gid'] . '">' . _UCART_ITEM_POSTREVIEW . '</a>';

        $detail['postmail'] = _UCART_TRIANGLE_IMG . ' <a href="' . $this->url . '?ucart=postmail&amp;item=' . $dat['gid'] . '">' . _UCART_ITEM_PMAIL . '</a>';

        $detail['exp_title'] = _UCART_ITEM_EXPLANATION;

        $detail = array_merge($detail, $this->_goods_detail($dat));

        return array_merge($dat, $detail);
    }

    public function _goods_detail($dat)
    {
        $detail = [];

        $detail['formname'] = 'shopping';

        $detail['mainlink'] = _UCART_TRIANGLE_IMG . '<a href="' . $this->url . '?ucart=main&amp;item=' . $dat['gid'] . '">' . $dat['top'] . '</a>';

        $detail['itemimg'] = $this->im->image($dat['images'], 'main');

        $detail['no'] = sprintf(_UCART_ITEM_NO, sprintf('%04d', $dat['gcid']), sprintf('%06d', $dat['gid']));

        $detail['zoom'] = $this->im->pic_view($dat['images'], _UCART_ZOOMIN_IMG);

        if (isset($dat['addmag'])) {
            $detail['addim'] = $this->im->image($dat['addmag'], 'pickup');

            $detail['addmain'] = $this->im->image($dat['addmag'], 'main');

            $detail['otherim'] = $this->im->pic_view('pic_' . $dat['addmag'], _UCART_OTHER_IMG);

            $detail['othermain'] = $this->im->pic_view('main_' . $dat['addmag'], _UCART_OTHER_IMG);
        }

        $detail['nontaxprice'] = sprintf(_UCART_ITEM_NOTAX, _UCART_ITEM_YEN . (number_format(ceil($dat['price'] / (1 + $dat['tax'] / 100)))));

        $detail['mprice'] = sprintf(_UCART_PRICE, '<span style="font-size : 14px;font-weight : bold;color : #ff8000;">' . _UCART_ITEM_YEN . number_format($dat['price']) . ' </span>') . ' (' . $detail['nontaxprice'] . ')<br>' . _UCART_ITEM_TAX;

        $detail['mstock'] = ($dat['stock'] > 0) ? sprintf(_UCART_ITEM_STOCK, $dat['stock']) : _UCART_ITEM_0STOCK;

        if (1 == $dat['on_sale']) {
            $detail['purchase'] = ($dat['stock'] > 0) ? sprintf(_UCART_ITEM_PURCHASE, '<input size="5" type="text" maxlength="3" name="purchase" value="1">&nbsp;<a href="#" onclick="return send(document.shopping)">' . _UCART_CART_IMG . '</a>') : sprintf(
                _UCART_ITEM_PURCHASE,
                '<input size="5" type="text" maxlength="3" name="purchase" value=""  disabled>'
            ) . '&nbsp;' . _UCART_OUT_IMG;
        } else {
            $detail['purchase'] = _UCART_ITEM_STOP;
        }

        $detail['coolim'] = ('f' == $dat['cool']) ? _UCART_NORMAL_IMG : _UCART_COOL_IMG;

        $detail['reference'] = sprintf(_UCART_ITEM_REFERENCE, $dat['hit']);

        $detail['hidden'] = '<input type="hidden" name="gid" value="' . $dat['gid'] . '">';

        $detail['hidden2'] = '<input type="hidden" name="stock" value="' . $dat['stock'] . '">';

        return $detail;
    }

    public function u_rev_insert($arr)
    {
        return $this->cart->u_rev_insert($arr);
    }

    public function u_rev_pages($gid)
    {
        $page = $this->cart->u_rev_count($gid);

        if ($page) {
            return 0;
        }

        $pages = '(';

        if ($page > 5) {
            for ($i = 0; ; $i++) {
                $f_val = 5 * $i;

                $page -= $f_val;

                if ($page < 0) {
                    break;
                }

                $pages .= '&nbsp;[&nbsp;<a href="review.php?ucart=review&amp;item=' . $gid . '&amp;page=' . $i . '">' . ($i + 1) . '</a>&nbsp;]';
            }
        }

        $pages .= ' &nbsp;)';

        return $pages;
    }

    public function u_rev_select($gid, $page)
    {
        global $xoopsUser;

        $admindel = '<a href="review.php?ucart=reviewdel&amp;item=%s">' . _UCART_DEL_IMG . '</s>';

        $admin = false;

        if (isset($xoopsUser) || $xoopsUser->isAdmin()) {
            $admin = true;
        }

        $dat = $this->cart->u_rev_select($gid, $page);

        if ($dat) {
            $src = [];

            foreach ($dat as $val) {
                $val['rank'] = $this->review_ranking($val['rank']);

                if (true === $admin) {
                    $val['admin'] = sprintf($admindel, $val['rid']);
                }

                $src[] = $val;
            }

            return $src;
        }

        return false;
    }

    public function u_page_count($item_count, $gcid)
    {
        $pages = '(';

        if ($item_count > 10) {
            for ($i = 0; ; $i++) {
                $f_val = 10 * $i;

                $page = $item_count - $f_val;

                if ($page < 0) {
                    break;
                }

                $pages .= '&nbsp;[&nbsp;<a href="' . $this->url . '?ucart=category&amp;categoryid=' . $gcid . '&amp;page=' . $i . '">' . ($i + 1) . '</a>&nbsp;]';
            }
        }

        $pages .= ' &nbsp;)';

        return $pages;
    }

    public function ranking($hit)
    {
        switch (true) {
            case ($hit < 50):
                return _UCART_RANK_0_IMG;
                break;
            case ($hit < 100):
                return _UCART_RANK_1_IMG;
                break;
            case ($hit < 200):
                return _UCART_RANK_2_IMG;
                break;
            case ($hit < 300):
                return _UCART_RANK_3_IMG;
                break;
            case ($hit < 400):
                return _UCART_RANK_4_IMG;
                break;
            case ($hit > 500):
                return _UCART_RANK_5_IMG;
                break;
            default:
                break;
        }
    }

    public function review_ranking($rev)
    {
        switch (true) {
            case (1 == $rev):
                return _UCART_RANK_1_IMG;
                break;
            case (2 == $rev):
                return _UCART_RANK_2_IMG;
                break;
            case (3 == $rev):
                return _UCART_RANK_3_IMG;
                break;
            case (4 == $rev):
                return _UCART_RANK_4_IMG;
                break;
            case (5 == $rev):
                return _UCART_RANK_5_IMG;
                break;
            default:
                break;
        }
    }

    public function get_notation_title()
    {
        return [
            0 => _UCART_INFO_01,
            1 => _UCART_INFO_02,
            2 => _UCART_INFO_03,
            3 => _UCART_INFO_04,
            4 => _UCART_INFO_05,
            5 => _UCART_INFO_06,
            6 => _UCART_INFO_07,
            7 => _UCART_INFO_08,
            8 => _UCART_INFO_09,
            9 => _UCART_INFO_10,
            10 => _UCART_INFO_11,
            11 => _UCART_INFO_12,
            12 => _UCART_INFO_13,
        ];
    }
}
