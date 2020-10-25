<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Bad!!Access error none mainfile:');
    exit();
}

require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

class uu_cart_admin
{
    public $db;
    public $myts;
    public $cart;
    public $gid       = '';
    public $top       = '';        //varchar(255) NOT NULL
    public $sub       = '';        //varchar(255)
    public $exp       = '';        //text NOT NULL
    public $package   = 1;        //mediumint(8)
    public $stock     = 10;        //mediumint(8)
    public $price     = 1000;        //mediumint(8) unsigned NOT NULL
    public $tax       = 5;        //tinyint(1) default '0'
    public $images    = '';        //varchar(255)
    public $on_view   = 1;        //tinyint(1) default '1'
    public $on_sale   = 1;        //tinyint(1) default '1'
    public $categorys = [];
    public $weight    = 2;
    public $length    = 60;
    public $n_post    = [];
    public $sql       = [];
    //category
    public $gcid         = '';
    public $c_top        = '';        //varchar(255) NOT NULL
    public $c_sub        = '';        //varchar(255)
    public $c_exp        = '';        //text NOT NULL
    public $categorydata = false;
    public $c_on_view    = 1;
    public $c_on_sale    = 1;
    //files
    public $cutbackname = '';
    public $upload      = '';
    public $orgname     = '';
    public $tmp_name    = '';
    public $type        = '';
    public $size        = 0;
    //main setting
    public $sid;
    public $shop_name;
    public $manager_name;
    public $zip;
    public $send_out;
    public $low_address;
    public $low_tel;
    public $low_email;
    public $order_method;
    public $necessary_charge;
    public $charge_delivery;
    public $term_validity;
    public $delivery_time;
    public $cart_cancel;
    public $cancel_time;
    public $cart_support;
    public $cool         = 'f';
    public $transport    = 0;
    public $islock       = 0;
    public $usefax       = 0;
    public $faxno;
    public $usessl       = 0;
    public $policy;
    public $bank;
    public $carriagefree = 0;
    public $freeval      = 10000;
    public $welcome;
    public $welcometop;
    public $welcometype  = 0;
    public $thum         = 90;
    public $main         = 150;
    public $pic          = 240;
    public $magicpath    = '';
    public $carriage     = 1000;
    public $prefecture   = [];
    public $file_err     = '';

    //class object constructor
    public function __construct()
    {
        global $xoopsModule;
        $this->cart       =& uu_cart_user::getInstance();
        $this->db         = $this->cart->get_db_instance();
        $this->myts       = $this->cart->get_ts_instance();
        $this->prefecture = $this->cart->get_prefecture_list();
        $this->upload     = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads/';
    }

    public function get_type($type, $mod = null)
    {
        switch ($type) {
            case 'category':
                $this->uu_admin_category($mod);
                break;
            case 'goods':
                $this->uu_admin_goods($mod);
                break;
            case 'mod_goods':
                $this->uu_admin_goods_set($mod);
                $this->uu_admin_goods($this->gcid);
                break;
            case 'addimage':
                $this->uu_admin_goods($mod, $add = true);
                break;
            case 'mod_addimg':
                $this->uu_admin_goods_set($mod, $add = true);
                $this->uu_admin_goods($this->gcid, $add = true);
                break;
            default:
                break;
        }
    }

    //post data sanitize
    public function uu_cart_post4data()
    {
        global $_POST;
        $ww = '';
        if (isset($_POST['submit'])) {
            unset($_POST['submit']);
        }
        if (isset($_POST['edit'])) {
            unset($_POST['edit']);
        }
        if (isset($_POST['delete'])) {
            unset($_POST['delete']);
        }
        if (isset($_POST['goods'])) {
            unset($_POST['goods']);
        }
        if (isset($_POST['welcome'])) {
            $ww = $_POST['welcome'];
        }

        $this->n_post = array_map(
            create_function(
                '$a',
                'if (is_string($a)) {
				return trim(htmlspecialchars(strip_tags($a), ENT_QUOTES));
			} else if (is_int($a)){
				return intval($a);
			} else if ($a == 0){
				return strval($a);
			} else if (is_array($a)) {
				return $a;
			}
			'
            ),
            $_POST
        );

        $this->n_post = array_filter(
            $this->n_post,
            create_function(
                '$b',
                'if (is_array($b)) {
				return true;
			} else if ($b == "") {
				return false;
			} else if ($b == "--") {
				return false;
			} else {
				return true;
			}
			'
            )
        );
        if (isset($this->n_post['welcome'])) {
            $this->n_post['welcome'] = $ww;
        }
        if (isset($this->n_post['shop_name'])) {
            return $this->post4data_notation2sql();
        } elseif (isset($this->n_post['use_im'])) {
            return $this->post4data_use_im2sql();
        } elseif (isset($this->n_post['c_top'])) {
            return $this->post4data_category2sql();
        } elseif (isset($this->n_post['top'])) {
            return $this->post4data_goods2sql();
        } elseif (isset($this->n_post['addim'])) {
            return $this->post4data_addim2sql();
        }
    }

    public function post4data_addim2sql()
    {
        global $_FILES;
        unset($this->n_post['addim']);
        $result            = true;
        $gid               = false;
        $sql_array         = [];
        $this->sql['gcid'] = (isset($this->n_post['gcid']) && $this->n_post['gcid'] != '--') ? (int)$this->n_post['gcid'] : 1;
        unset($this->n_post['gcid']);
        if (isset($this->n_post['gid']) && $this->n_post['gid'] != '--') {
            $gid = (int)$this->n_post['gid'];
            unset($this->n_post['gid']);
        } else {
            redirect_header($_SERVER['SCRIPT_NAME'], 3, _AD_UUCART_ERR07);
            exit;
        }
        if (isset($_FILES['images'])) {
            $result = $this->uploda_files_construct();
        }
        if (isset($_FILES['addmag'])) {
            $result = $this->uploda_files_construct();
        }
        if ($result) {
            $result = $this->imagefilecheck();
        }
        if ($result) {
            $result = $this->originalimagesave();
        }
        if (true === $result && is_bool($result)) {
            $this->sql['addmag'] = $this->cutbackname;
            $this->sql['p_type'] = $this->type;
        } elseif (is_string($result)) {
            $this->file_err = $result;
        }
        $this->sql = array_merge($this->sql);
        $column    = $this->cart->sql_parse_updare($this->sql);
        $sql       = 'UPDATE ' . $this->db->prefix('goods') . ' SET ' . $column . ', days = NOW() WHERE gid = ' . $gid;
        return $this->cart->result_sql($sql);
    }

    public function post4data_goods2sql()
    {
        global $_FILES;
        $result           = true;
        $gid              = false;
        $sql_array        = [];
        $this->sql['top'] = $this->n_post['top'];
        unset($this->n_post['top']);
        $this->sql['sub'] = $this->n_post['sub'];
        unset($this->n_post['sub']);
        $this->sql['exp'] = $this->n_post['exp'];
        unset($this->n_post['exp']);
        if (isset($_FILES['images'])) {
            $result = $this->uploda_files_construct();
        }
        if ($result) {
            $result = $this->imagefilecheck();
        }
        if ($result) {
            $result = $this->originalimagesave();
        }
        if (true === $result && is_bool($result)) {
            $this->sql['images'] = $this->cutbackname;
            $this->sql['p_type'] = $this->type;
        } elseif (is_string($result)) {
            $this->file_err = $result;
        }
        $this->sql['gcid'] = (isset($this->n_post['gcid']) && $this->n_post['gcid'] != '--') ? (int)$this->n_post['gcid'] : 1;
        unset($this->n_post['gcid']);
        if (isset($this->n_post['gid']) && $this->n_post['gid'] != '--') {
            $gid = (int)$this->n_post['gid'];
            unset($this->n_post['gid']);
        }
        $this->sql = array_merge($this->sql, $this->n_post);
        if ($gid) {
            $column = $this->cart->sql_parse_updare($this->sql);
            $sql    = 'UPDATE ' . $this->db->prefix('goods') . ' SET ' . $column . ', days = NOW() WHERE gid = ' . $gid;
        } else {
            $this->existence_confirmation($this->db->prefix('goods'), htmlspecialchars($this->sql['top'], ENT_QUOTES | ENT_HTML5));
            $column = $this->cart->sql_parse_insert($this->sql);
            $sql    = 'INSERT INTO ' . $this->db->prefix('goods') . ' (' . implode(',', $column['key']) . ', days) VALUES(' . implode(',', $column['val']) . ', NOW())';
        }
        return $this->cart->result_sql($sql);
    }

    public function post4data_category2sql()
    {
        global $_FILES;
        $result               = true;
        $this->sql['top']     = $this->n_post['c_top'];
        $this->sql['sub']     = $this->n_post['c_sub'];
        $this->sql['exp']     = $this->n_post['c_exp'];
        $this->sql['on_view'] = (int)$this->n_post['c_on_view'];
        $this->sql['on_sale'] = (int)$this->n_post['c_on_sale'];
        if (isset($_FILES['images'])) {
            $result = $this->uploda_files_construct();
        }
        if ($result) {
            $result = $this->imagefilecheck();
        }
        if ($result) {
            $result = $this->originalimagesave();
        }
        if (true === $result && is_bool($result)) {
            $this->sql['images'] = $this->cutbackname;
            $this->sql['p_type'] = $this->type;
        } elseif (is_string($result)) {
            $this->file_err = $result;
        }
        if (isset($this->n_post['c_gcid'])) {
            if ($this->sql['top'] == _AD_UUCART_CATEGORY_TOP) {
                unset($this->sql['top']);
            }
            $gcid = (int)$this->n_post['c_gcid'];
            unset($this->n_post['c_gcid']);
            $column = $this->cart->sql_parse_updare($this->sql);
            $sql    = 'UPDATE ' . $this->db->prefix('goods_category') . ' SET ' . $column . ', days = NOW() WHERE gcid = ' . $gcid;
        } else {
            $this->existence_confirmation($this->db->prefix('goods_category'), htmlspecialchars($this->sql['top'], ENT_QUOTES | ENT_HTML5));
            $column = $this->cart->sql_parse_insert($this->sql);
            $sql    = 'INSERT INTO ' . $this->db->prefix('goods_category') . ' (' . implode(',', $column['key']) . ', days) VALUES(' . implode(',', $column['val']) . ', NOW())';
        }
        return $this->cart->result_sql($sql);
    }

    public function category_delete($gcid)
    {
        if ($gcid == 1) {
            return false;
        }
        $sql    = 'UPDATE ' . $this->db->prefix('goods') . ' SET gcid = 1 WHERE gcid = ' . $gcid;
        $result =& $this->cart->result_sql($sql);
        if ($result) {
            $sql = 'DELETE FROM ' . $this->db->prefix('goods_category') . ' WHERE gcid = ' . $gcid;
            return $this->cart->result_sql($sql);
        }
        return false;
    }

    public function goods_delte($gid)
    {
        $row       =& $this->cart->_goods($gid);
        $thumbnail = '';
        $pickup    = '';
        if ($row['images']) {
            $thumbnail = 'thum_' . $row['images'];
            $pickup    = 'pic_' . $row['images'];
            if (file_exists($this->upload . $row['images'])) {
                @unlink($this->upload . $row['images']);
            }
            if (file_exists($this->upload . $thumbnail)) {
                @unlink($this->upload . $thumbnail);
            }
            if (file_exists($this->upload . $pickup)) {
                @unlink($this->upload . $pickup);
            }
        }
        $sql = 'DELETE FROM ' . $this->db->prefix('goods') . ' WHERE gid = ' . $gid;
        return $this->cart->result_sql($sql);
    }

    public function existence_confirmation($tb, $top)
    {
        $sql    = 'SELECT * FROM ' . $tb . " WHERE top = '" . $top . "'";
        $result =& $this->db->query($sql);
        if ($result && $this->db->getRowsNum($result) > 0) {
            $redirect = (eregi('category', $tb)) ? 'new_category' : 'new_goods';
            redirect_header($_SERVER['SCRIPT_NAME'] . '?uu_cart=' . $redirect, 5, sprintf(_AD_UUCART_ERR03, $this->sql['top']));
            exit;
        }
    }

    public function post4data_use_im2sql()
    {
        $sid = '';
        unset($this->n_post['generalset']);
        if (isset($this->n_post['sid'])) {
            $sid = (int)$this->n_post['sid'];
            unset($this->n_post['sid']);
            $column = $this->cart->sql_parse_updare($this->n_post);
            $sql    = 'UPDATE ' . $this->db->prefix('u_main_setting') . ' SET ' . $column . ' WHERE sid = ' . $sid;
        } else {
            $column = $this->cart->sql_parse_insert($this->n_post);
            $sql    = 'INSERT INTO ' . $this->db->prefix('u_main_setting') . ' (' . implode(',', $column['key']) . ') VALUES(' . implode(',', $column['val']) . ')';
        }
        return $this->cart->result_sql($sql);
    }

    public function post4data_notation2sql()
    {
        $this->n_post['zip']   = zip_format_check($this->n_post['zip']);
        $this->sql['send_out'] = $this->n_post['send_out'];
        unset($this->n_post['send_out']);
        $this->sql['setting'] = base64_encode(serialize($this->n_post));
        if ($this->cart->uu_admin_notation_chack()) {
            $sid = $this->cart->get_sid();
            if (isset($this->n_post['sid'])) {
                $sid = (int)$this->n_post['sid'];
                unset($this->n_post['sid']);
            }
            $column = $this->cart->sql_parse_updare($this->sql);
            $sql    = 'UPDATE ' . $this->db->prefix('u_main_setting') . ' SET ' . $column . ' WHERE sid = ' . $sid;
        } else {
            $column = $this->cart->sql_parse_insert($this->sql);
            $sql    = 'INSERT INTO ' . $this->db->prefix('u_main_setting') . ' (' . implode(',', $column['key']) . ') VALUES(' . implode(',', $column['val']) . ')';
        }
        return $this->cart->result_sql($sql);
    }

    public function uploda_files_construct()
    {
        global $_FILES, $xoopsModule;
        if (isset($_FILES['images']) && $_FILES['images']['error'] != 0) {
            return false;
        } elseif (isset($_FILES['addmag']) && $_FILES['addmag']['error'] != 0) {
            return false;
        }
        if (isset($_FILES['images'])) {
            $fname          = pathinfo($_FILES['images']['name']);
            $this->orgname  = strtolower($_FILES['images']['name']);
            $this->type     = strtolower($_FILES['images']['type']);
            $this->tmp_name = $_FILES['images']['tmp_name'];
            $this->size     = $_FILES['images']['size'];
        } elseif (isset($_FILES['addmag'])) {
            $fname          = pathinfo($_FILES['addmag']['name']);
            $this->orgname  = strtolower($_FILES['addmag']['name']);
            $this->type     = strtolower($_FILES['addmag']['type']);
            $this->tmp_name = $_FILES['addmag']['tmp_name'];
            $this->size     = $_FILES['addmag']['size'];
        }
        $prefix            = time();
        $this->cutbackname = $prefix . '.' . strtolower($fname['extension']);
        return true;
    }

    public function make_categorys()
    {
        $this->categorys = $this->cart->get_categorys_array();
    }

    public function uu_admin_category($gcid = null)
    {
        $row = false;
        $this->make_categorys();
        if ($gcid) {
            $row             = $this->cart->_category($gcid);
            $this->gcid      = $gcid;
            $this->c_top     = $this->myts->htmlSpecialChars($row['top']);
            $this->c_sub     = $this->myts->htmlSpecialChars($row['sub']);
            $this->c_exp     = $this->myts->htmlSpecialChars($row['exp']);
            $this->c_on_view = (int)$row['on_view'];
            $this->c_on_sale = (int)$row['on_sale'];
        }
        echo '
		<script type="text/javascript">
		<!--
		function category_change(category)
		{
			var n = category.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_category&amp;mod=" + category.options[n].value;
		}
		//-->
		</script>
		<h4>' . _AD_UUCART_NEW_CATEGORY . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>';
        $form = new XoopsThemeForm(_AD_UUCART_NEW_CATEGORY, 'newgoods', $_SERVER['SCRIPT_NAME'], 'POST');
        $form->setExtra('enctype="multipart/form-data"');
        $formcategory = new XoopsFormSelect(_AD_UUCART_CATEGORY, 'gcid', $this->gcid);
        $formcategory->addOptionArray($this->categorys);
        $formcategory->setExtra('onChange="category_change(this)"');
        $submit_button = new XoopsFormButton('', 'submit', _AD_UUCART_SUBMIT, 'submit');
        $form->addElement($formcategory);
        $form->addElement(new XoopsFormText(_AD_UUCART_CATEGORY_NAME, 'c_top', 50, 150, $this->c_top), true);
        $form->addElement(new XoopsFormText(_AD_UUCART_CATEGORY_SUB, 'c_sub', 50, 150, $this->c_sub), true);
        $form->addElement(new XoopsFormDhtmlTextArea(_AD_UUCART_CATEGORY_EXP, 'c_exp', $this->c_exp, 7, 50), true);
        $form->addElement(new XoopsFormLabel(_AD_UUCART_IMAGES, '<input type="file" name="images" id="images">'));
        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_GOODS_DISPLAY, 'c_on_view', $this->c_on_view));
        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_GOODS_ONSALE, 'c_on_sale', $this->c_on_sale));
        if ($gcid) {
            $form->addElement(new XoopsFormHidden('c_gcid', $gcid));
            if ($row['top'] != _AD_UUCART_CATEGORY_TOP) {
                $form->addElement(new XoopsFormButton(_AD_UUCART_DELETE_DESC, 'delete', _AD_UUCART_DELETE, 'submit'));
            }
            $form->addElement(new XoopsFormButton('', 'edit', _AD_UUCART_EDIT, 'submit'));
        } else {
            $form->addElement(new XoopsFormButton('', 'submit', _AD_UUCART_SUBMIT, 'submit'));
        }
        $form->display();
    }

    public function uu_admin_goods_set($gid = null)
    {
        $row = $this->cart->_goods($gid);
        foreach ($row as $key => $val) {
            $this->{$key} = $this->myts->htmlSpecialChars($val);
        }
    }

    public function uu_admin_goods($gcid = null, $add = false)
    {
        global $_SERVER;
        $formgoods     = false;
        $delete_button = false;
        $this->make_categorys();
        $this->categorys = str_replace(_AD_UUCART_NEW, _AD_UUCART_CATEGORY_S, $this->categorys);
        if ($gcid || $this->gcid) {
            if ($gcid) {
                $this->gcid = (int)$gcid;
            }//im_change
            if (true === $this->cart->get_goods_check($this->gcid)) {
                $formgoods = new XoopsFormSelect(_AD_UUCART_SELECT_GOODS, 'gid', $this->gid);
                $formgoods->addOptionArray($this->cart->get_goods_array($this->gcid));
                if (false === $add) {
                    $formgoods->setExtra('onChange="goods_change(this)"');
                } else {
                    $formgoods->setExtra('onChange="im_change(this)"');
                }
            }
        }
        echo '
		<script type="text/javascript">
		<!--
		function category_change(category)
		{
			var n = category.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_goods&amp;mod=" + category.options[n].value;
		}
		function category_imchange(category)
		{
			var n = category.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_addimage&amp;mod=" + category.options[n].value;
		}

		function im_change(goods)//mod_goods&item=7
		{
			var n = goods.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=mod_addimg&amp;item=" + goods.options[n].value;
		}

		function goods_change(goods)
		{
			var n = goods.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=mod_goods&amp;item=" + goods.options[n].value;
		}
		function dispConfirm() {
			var str = "' . _AD_UUCART_CONFIRM_DELETE . '";
			if (confirm(str)) {
				return true;
			} else {
				document.newgoods.top.focus();
				return false;
			}
		}
		//-->
		</script>
		<h4>' . _AD_UUCART_NEW_GOODS . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<p style="text-align:left;">' . _AD_UUCART_IM_CHANGE . '</p>';
        $form = new XoopsThemeForm(_AD_UUCART_NEW_GOODS, 'newgoods', $_SERVER['SCRIPT_NAME'], 'POST');
        $form->setExtra('enctype="multipart/form-data"');
        $set_category = ($this->gcid) ?: '--';
        $formcategory = new XoopsFormSelect(_AD_UUCART_CATEGORY, 'gcid', $set_category);
        $formcategory->addOptionArray($this->categorys);
        $formbutton = new XoopsFormButton(_AD_UUCART_SELECT_CATEGORY, 'catchange', _AD_UUCART_SELECT_CATEGORY, 'button');
        if (false === $add) {
            $formbutton->setExtra('onClick="category_change(document.newgoods.gcid)"');
        } else {
            $formbutton->setExtra('onClick="category_imchange(document.newgoods.gcid)"');
        }
        $form->addElement($formcategory);
        $form->addElement($formbutton);
        if (false === $add) {
            $form->addElement(new XoopsFormLabel(_AD_UUCART_NEWCATEGORY, '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=new_category" style="padding: 3px 0.5em; margin-left:3px;border: 1px solid #778000;background: #FFFFFF;text-decoration: none;">' . _AD_UUCART_SUBMIT . '</a>'));
        }

        if ($formgoods) {
            $form->addElement($formgoods);
        }

        if (false === $add) {
            if (!empty($this->top)) {
                $submit_button = new XoopsFormButton('', 'goods', _AD_UUCART_EDIT, 'submit');
                $delete_button = new XoopsFormButton('', 'goodsdelete', _AD_UUCART_DELETE, 'submit');
            } else {
                $submit_button = new XoopsFormButton('', 'goods', _AD_UUCART_SUBMIT, 'submit');
            }
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_NAME, 'top', 50, 150, $this->top), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_SUB, 'sub', 50, 150, $this->sub), true);
            $form->addElement(new XoopsFormDhtmlTextArea(_AD_UUCART_GOODS_EXP, 'exp', $this->exp, 7, 50), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_STOCK, 'stock', 10, 50, $this->stock), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_PRICE, 'price', 10, 50, $this->price), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_TAX, 'tax', 10, 50, $this->tax), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_PACKAGE, 'package', 10, 50, $this->package), true);
            $form->addElement(new XoopsFormLabel(_AD_UUCART_GOODS_PACKAGE, _AD_UUCART_GOODS_PACK_LABEL));
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_WEIGHT, 'weight', 10, 50, $this->weight), true);
            $form->addElement(new XoopsFormText(_AD_UUCART_GOODS_LENGTH, 'length', 10, 50, $this->length), true);
            $coll = new XoopsFormRadio(_AD_UUCART_COOL, 'cool', $this->cool);
            $coll->addOptionArray(['t' => _YES, 'f' => _NO, 'c' => _AD_UUCART_TRANSPORT]);
            $form->addElement($coll);
            $form->addElement(new XoopsFormText(_AD_UUCART_CARRIAGE, 'length', 10, 50, $this->carriage));
            $form->addElement(new XoopsFormRadioYN(_AD_UUCART_GOODS_DISPLAY, 'on_view', $this->on_view));
            $form->addElement(new XoopsFormRadioYN(_AD_UUCART_GOODS_ONSALE, 'on_sale', $this->on_sale));
            $form->addElement(new XoopsFormLabel(_AD_UUCART_IMAGES, '<input type="file" name="images" id="images">'));
            $form->addElement($submit_button);
            if ($delete_button) {
                $delete_button->setExtra('onClick="return dispConfirm()"');
                $form->addElement($delete_button);
            }
        } else {
            $form->addElement(new XoopsFormLabel(_AD_UUCART_IMAGES, '<input type="file" name="addmag" id="addmag">'));
            $form->addElement(new XoopsFormButton('', 'addim', _AD_UUCART_SUBMIT, 'submit'));
        }

        $form->display();
    }

    public function uu_admin_notationbasedonlaw()
    {
        $this->uu_admin_notationinitialize();
        echo '		<h4>' . _AD_UUCART_NOTATION_BASED . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>';
        $form         = new XoopsThemeForm(_AD_UUCART_NEW_GOODS, 'newgoods', $_SERVER['SCRIPT_NAME'], 'POST');
        $shop_name    = new XoopsFormText(_AD_UUCART_SHOP_NAME, 'shop_name', 50, 150, $this->shop_name);
        $manager_name = new XoopsFormText(_AD_UUCART_MANAGER_NAME, 'manager_name', 50, 150, $this->manager_name);
        $zip          = new XoopsFormText(_AD_UUCART_LOW_ADDRESS_ZIP . '<br>' . _AD_UUCART_LOW_ADDRESS_DESC, 'zip', 50, 150, $this->zip);
        $send_out     = new XoopsFormSelect(_AD_UUCART_LOW_SEND_PREF, 'send_out', $this->send_out);
        $send_out->addOptionArray($this->prefecture);
        $low_address      = new XoopsFormText(_AD_UUCART_LOW_ADDRESS, 'low_address', 50, 150, $this->low_address);
        $low_tel          = new XoopsFormText(_AD_UUCART_LOW_TELFAX . '<br>' . _AD_UUCART_LOW_TELFAX_DESC, 'low_tel', 50, 150, $this->low_tel);
        $low_email        = new XoopsFormText(_AD_UUCART_LOW_EMAIL, 'low_email', 50, 150, $this->low_email);
        $order_method     = new XoopsFormTextArea(_AD_UUCART_ORDER_METHOD, 'order_method', $this->order_method);
        $necessary_charge = new XoopsFormTextArea(_AD_UUCART_NECESSARY_CHARGE, 'necessary_charge', $this->necessary_charge);
        $charge_delivery  = new XoopsFormTextArea(_AD_UUCART_CHARGE_DELIVERY, 'charge_delivery', $this->charge_delivery);
        $term_validity    = new XoopsFormTextArea(_AD_UUCART_TERM_VALIDITY, 'term_validity', $this->term_validity);
        $delivery_time    = new XoopsFormTextArea(_AD_UUCART_DELIVERY_TIME, 'delivery_time', $this->delivery_time);
        $cart_cancel      = new XoopsFormTextArea(_AD_UUCART_CANCEL, 'cart_cancel', $this->cart_cancel);
        $cancel_time      = new XoopsFormTextArea(_AD_UUCART_CANCEL_TIME, 'cancel_time', $this->cancel_time);
        $cart_support     = new XoopsFormTextArea(_AD_UUCART_SUPPORT, 'cart_support', $this->cart_support);
        $submit_button    = new XoopsFormButton('', 'submit', _AD_UUCART_SUBMIT, 'submit');
        $form->addElement($shop_name, true);
        $form->addElement($manager_name, true);
        $form->addElement($zip, true);
        $form->addElement($send_out);
        $form->addElement($low_address, true);
        $form->addElement($low_tel, true);
        $form->addElement($low_email, true);
        $form->addElement($order_method, true);
        $form->addElement($necessary_charge, true);
        $form->addElement($charge_delivery, true);
        $form->addElement($term_validity, true);
        $form->addElement($delivery_time, true);
        $form->addElement($cart_cancel, true);
        $form->addElement($cancel_time, true);
        $form->addElement($cart_support, true);
        $form->addElement($submit_button, true);
        $form->display();
    }

    public function uu_admin_notationinitialize()
    {
        global $xoopsUser, $xoopsConfig;
        $src = $this->cart->get_notation();
        if (is_array($src)) {
            foreach ($src as $key => $val) {
                $this->{$key} = $val;
            }
            $this->zip = zip_format_check($this->zip);
        } else {
            $this->shop_name        = $xoopsConfig['sitename'] . ' ' . $xoopsConfig['slogan'];
            $this->manager_name     = $xoopsUser->getVar('uname');
            $this->zip              = '';
            $this->send_out         = '';
            $this->low_address      = '';
            $this->low_tel          = '';
            $this->low_email        = $xoopsUser->getVar('email');
            $this->order_method     = _AD_UUCART_ORDER_METHOD_DESC;
            $this->necessary_charge = _AD_UUCART_NECESSARY_CHARGE_DESC;
            $this->charge_delivery  = _AD_UUCART_ORDER_METHOD_DESC;
            $this->term_validity    = _AD_UUCART_TERM_VALIDITY_DESC;
            $this->delivery_time    = _AD_UUCART_DELIVERY_TIME_DESC;
            $this->cart_cancel      = _AD_UUCART_CANCEL_DESC;
            $this->cancel_time      = _AD_UUCART_CANCEL_TIME_DESC;
            $this->cart_support     = _AD_UUCART_SUPPORT_DESC;
            $this->bank             = _AD_UUCART_BANK00;
            require dirname(__DIR__) . '/include/policy.php';
            $this->policy = $policy;
        }
    }

    public function uu_admin_general_setting()
    {
        $form_gd   = false;
        $imgc_path = false;
        $form_imgc = false;
        $im_path   = false;
        $row       = [];
        echo '		<h4>' . _AD_UUCART_ADMIN . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>';
        $form      = new XoopsThemeForm(_AD_UUCART_NEW_GOODS, 'general', $_SERVER['SCRIPT_NAME'], 'POST');
        $form_imgc = '';
        $im_gd     = $this->getsupportedimagetypes();
        if ($im_gd) {
            $im_type = 1;
        }
        $_gd     = ($im_gd) ? sprintf(_AD_UUCART_GD_SUPPORT, implode(':', $im_gd)) : _U_U_GD_NON_SUPPORT;
        $form_gd = new XoopsFormLabel(_AD_UUCART_IM_SUPPORT, $_gd);
        $command = 'which convert';
        exec($command, $rtn, $rc);
        if ($rc == 0) {
            $im_path   = $rtn[0];
            $im_type   = 2;
            $form_imgc = new XoopsFormLabel(_AD_UUCART_IM_SUPPORT, $im_path . _AD_ADMIN_MENU_32);
        }
        if ($this->cart->uu_admin_notation_chack()) {
            $row = $this->cart->get_notation();
            foreach ($row as $key => $val) {
                $this->{$key} = $val;
            }
            if ($this->use_im == 2) {
                $im_path = ($this->magicpath) ?: $im_path;
            }
            if ($this->use_im < 3) {
                $im_type = $this->use_im;
            }
        }
        //if (! $this->bank) $this->bank = _AD_UUCART_BANK00;
        if ($this->policy == '') {
            require dirname(__DIR__) . '/include/policy.php';
            $this->policy = $policy;
        }
        if ($im_path) {
            $imgc_path = new XoopsFormText(_AD_ADMIN_MENU_33, 'magicpath', 50, 150, $im_path);
        }
        $use_im = new XoopsFormRadio(_AD_UUCART_IM_SUPPORT_TYPE, 'use_im', $im_type);
        $use_im->addOptionArray([0 => '::NONE', 1 => '::GD', 2 => '::ImageMagic']);
        $form->addElement(new XoopsFormText(_AD_ADMIN_MENU_34, 'thum', 10, 20, $this->thum), true);
        $form->addElement(new XoopsFormText(_AD_ADMIN_MENU_35, 'main', 10, 20, $this->main), true);
        $form->addElement(new XoopsFormText(_AD_ADMIN_MENU_36, 'pic', 10, 20, $this->pic), true);
        $form->addElement($use_im);
        if ($form_gd) {
            $form->addElement($form_gd);
        }
        if ($form_imgc) {
            $form->addElement($form_imgc);
        }
        if ($imgc_path) {
            $form->addElement($imgc_path);
        }
        $transport = new XoopsFormRadio(_AD_UUCART_TRANSPORTER, 'transport', $this->transport);
        $transport->addOptionArray(
            [
                0 => _AD_UUCART_SAGAWA,
                1 => _AD_UUCART_KURONEKO,
                2 => _AD_UUCART_PELICAN,
            ]
        );
        //3 => _AD_UUCART_UPACK
        $form->addElement($transport);
        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_TRANSPORT, 'islock', $this->islock));
        $form->addElement(new XoopsFormText(_AD_UUCART_CARRIAGE, 'carriage', 10, 20, $this->carriage));
        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_FREE, 'carriagefree', $this->carriagefree));
        $form->addElement(new XoopsFormText(_AD_UUCART_FREEVAL, 'freeval', 10, 20, $this->freeval));

        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_USE_FAX, 'usefax', $this->usefax));
        $form->addElement(new XoopsFormText(_AD_UUCART_FAX_NO, 'faxno', 20, 20, $this->faxno));
        $form->addElement(new XoopsFormRadioYN(_AD_ADMIN_MENU_37, 'usessl', $this->usessl));

        $form->addElement(new XoopsFormTextArea(_UCART_SITE_POLICY, 'policy', $this->policy));

        $form->addElement(new XoopsFormLabel(_UUCART_BANKS, _AD_UUCART_BANK));
        $form->addElement(new XoopsFormText(_UUCART_BANKS, 'bank', 50, 500, $this->bank));
        if ($this->sid) {
            $form->addElement(new XoopsFormHidden('sid', $this->sid));
        }

        $form->addElement(new XoopsFormText(_AD_UUCART_WELCOME_TOP, 'welcometop', 50, 255, $this->welcometop), true);
        $form->addElement(new XoopsFormDhtmlTextArea(_AD_UUCART_WELCOME, 'welcome', $this->welcome, 7, 50), true);
        $welcometype = new XoopsFormRadio(_AD_UUCART_WELCOME_TYPE, 'welcometype', $this->welcometype);
        $welcometype->addOptionArray(
            [
                0 => _AD_UUCART_WELCOME_TYPE0,
                1 => _AD_UUCART_WELCOME_TYPE1,
            ]
        );
        $form->addElement($welcometype);
        $form->addElement(new XoopsFormButton('', 'generalset', _AD_UUCART_SUBMIT, 'submit'));
        $form->display();
    }

    public function getsupportedimagetypes()
    {
        if (!function_exists('imagetypes')) {
            return false;
        }
        $asupportedtypes        = [];
        $apossibleimagetypebits = [
            IMG_GIF  => 'GIF',
            IMG_JPG  => 'JPG',
            IMG_PNG  => 'PNG',
            IMG_WBMP => 'WBMP',
        ];
        foreach ($apossibleimagetypebits as $iimagetypebits => $simagetypestring) {
            if (imagetypes() && $iimagetypebits) {
                $asupportedtypes[] = $simagetypestring;
            }
        }
        return $asupportedtypes;
    }

    public function imagefilecheck()
    {
        $this->type = strtolower($this->type);
        if (!preg_match('/(png|gif|jpe?g)$/i', $this->orgname)) {
            return _AD_UUCART_ERR27;
        }
        if (!preg_match("/(image\/)(x-png|png|gif|p?jpe?g)/", $this->type)) {
            return _AD_UUCART_ERR26;
        }
        if (!@getimagesize($this->tmp_name)) {
            return _AD_UUCART_ERR29;
        }
        return true;
    }

    public function originalimagesave()
    {
        $tmp  = true;
        $ctmp = true;
        if (function_exists('move_uploaded_file')) {
            $tmp = @move_uploaded_file($this->tmp_name, $this->upload . $this->cutbackname);
            if (!$tmp) {
                return _AD_UUCART_ERR34;
            }
        } elseif (is_uploaded_file($this->tmp_name)) {
            $ctemp = copy($this->tmp_name, $this->upload . $this->cutbackname);
            @unlink($this->tmp_name);
            if (!$ctmp) {
                return _AD_UUCART_ERR34;
            }
        } elseif (!is_uploaded_file($this->tmp_name)) {
            return _AD_UUCART_ERR31;
        }
        return true;
    }

    public function get_notation()
    {
        return $this->cart->get_notation();
    }

    public function get_notation_chack()
    {
        return $this->cart->uu_admin_notation_chack();
    }

    public function get_categorys()
    {
        $this->cart->make_categorys();
        return $this->cart->get_category_check();
    }

    public function get_file_err()
    {
        return $this->file_err;
    }

    public function get_use_im()
    {
        return $this->cart->get_use_im();
    }
    //$sql = 'OPTIMIZE TABLE `unashop_zipcode` ';//テーブルを最適化します。
    //$sql = 'REPAIR TABLE `unashop_zipcode` ';//テーブルを復旧します。
    //$sql = 'FLUSH TABLE `unashop_zipcode` ';//テーブルのキャッシュを空にする
    //$sql = 'CHECK TABLE `unashop_zipcode` ';//テーブルをチェックします。

}

/*

文字列型
POSTデータのDB保存（改行無し）：addSlashes($text)
POSTデータのDB保存（改行有り）：addSlashes($text)
POSTデータの通常表示（改行無し）：stripSlashesGPC(htmlSpecialChars($text))
POSTデータの通常表示（改行有り）：previewTarea($text, $html=0, $smiley=1, $xcode=1, $image=1, $br=1)
POSTデータのテキストボックス内表示：stripSlashesGPC(htmlSpecialChars($text))
POSTデータの<textarea>内表示：stripSlashesGPC(htmlSpecialChars($text))
DB取得データの通常表示（改行無し）：htmlSpecialChars($text)
DB取得データの通常表示（改行有り）：displayTarea($text, $html=0, $smiley=1, $xcode=1, $image=1, $br=1)
DB取得データのテキストボックス内表示：htmlSpecialChars($text)
DB取得データの<textarea>内表示：htmlSpecialChars($text)
DB取得データのDB保存（改行無し）：addSlashes($text)
DB取得データのDB保存（改行有り）：addSlashes($text)

*/

