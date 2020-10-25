<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Bad!!Access error none mainfile:');

    exit();
}
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.class.php';

class uu_item_admin
{
    public $db;

    public $myts;

    public $cart;

    public $gid = '';

    public $gcid = '';

    public $err = '';

    public $n_post = [];

    public function __construct()
    {
        global $xoopsModule;

        $this->cart = &uu_cart_user::getInstance();

        $this->db = $this->cart->get_db_instance();

        //$this->myts =& $this->cart->get_ts_instance();
    }

    public function uu_item_post4data()
    {
        global $_POST;

        if (isset($_POST['pickup'])) {
            unset($_POST['pickup']);
        }

        $this->n_post = array_map(
            create_function(
                '$a',
                'return trim(htmlspecialchars(strip_tags($a), ENT_QUOTES));'
            ),
            $_POST
        );
    }

    public function uu_admin_pickup_check($gid = null)
    {
        if ($gid) {
            $this->gid = $gid;
        }

        return $this->cart->_result('SELECT * FROM ' . $this->db->prefix('u_pickup') . ' WHERE gid = ' . $this->gid);
    }

    public function uu_admin_pickup_delete()
    {
        $today = date('Y-m-d', userTimeToServerTime(time()));

        $sql = 'DELETE FROM ' . $this->db->prefix('u_pickup') . " WHERE gid = '0' OR offdays < '" . $today . "'";

        $result = $this->cart->result_sql($sql);
    }

    public function uu_admin_pickup_off($item)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix('u_pickup') . " WHERE gid = '" . $item . "'";

        return $this->cart->result_sql($sql);
    }

    public function uu_admin_time_compare()
    {
        if (strtotime($this->n_post['ondays']) > strtotime($this->n_post['offdays'])) {
            $_on = $this->n_post['offdays'];

            $_off = $this->n_post['ondays'];

            $this->n_post['ondays'] = $_on;

            $this->n_post['offdays'] = $_off;
        }
    }

    public function uu_admin_pickup_set()
    {
        $this->uu_item_post4data();

        if (!isset($this->n_post['gid']) || '--' == $this->n_post['gid']) {
            $this->err = _AD_UUCART_ERR07;

            return false;
        }

        $check = $this->uu_admin_pickup_check($this->n_post['gid']);

        $this->uu_admin_time_compare();

        unset($this->n_post['gcid']);

        $this->n_post['gid'] = (int)$this->n_post['gid'];

        $this->n_post['type'] = (isset($this->n_post['type'])) ? (int)$this->n_post['type'] : 0;

        if (1 == $this->n_post['type']) {
            $sqlset = 'UPDATE ' . $this->db->prefix('u_pickup') . " SET type = '0'";

            $result = $this->cart->result_sql($sqlset);
        }

        if (false === $check) {
            $column = $this->cart->sql_parse_insert($this->n_post);

            $sql = 'INSERT INTO ' . $this->db->prefix('u_pickup') . ' (' . implode(',', $column['key']) . ') VALUES(' . implode(',', $column['val']) . ')';
        } else {
            $gid = $check['gid'];

            unset($this->n_post['gid']);

            $column = $this->cart->sql_parse_updare($this->n_post);

            $sql = 'UPDATE ' . $this->db->prefix('u_pickup') . ' SET ' . $column . ' WHERE gid = ' . $gid;
        }

        return $this->cart->result_sql($sql);
    }

    public function uu_admin_pickup($gcid)
    {
        global $_SERVER;

        $formgoods = false;

        $pic = false;

        $this->uu_admin_pickup_delete();

        echo '
		<script type="text/javascript">
		<!--
		function category_change(category)
		{
			var n = category.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup&amp;item=" + category.options[n].value;
		}
		//-->
		</script>
		<h4>' . _AD_UUCART_PICKUP . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<p style="text-align:left;">' . _AD_UUCART_PICKUP_DESC . '</p>';

        $categorys = $this->cart->get_categorys_array();

        $categorys = str_replace(_AD_UUCART_NEW, _AD_UUCART_CATEGORY_S, $categorys);

        $form = new XoopsThemeForm(_AD_UUCART_NEW_GOODS, 'pickup_select', $_SERVER['SCRIPT_NAME'], 'POST');

        if ($gcid) {
            $this->gcid = (int)$gcid;

            if (true === $this->cart->get_goods_check($this->gcid)) {
                $formgoods = new XoopsFormSelect(_AD_UUCART_SELECT_GOODS, 'gid', $this->gid);

                $formgoods->addOptionArray(str_replace(_AD_UUCART_NEW, _AD_UUCART_SELECT_GOODS, $this->cart->get_goods_array($this->gcid)));
            }
        }

        $pic = $this->cart->_pickup();

        if ($pic) {
            $str = '';

            foreach ($pic as $val) {
                $str .= '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pickupoff&amp;item=' . (int)$val['gid'] . '">' . $val['top'] . '</a>&nbsp;&gt;&gt;&nbsp;' . htmlspecialchars($val['offdays'], ENT_QUOTES) . "<br>\n";
            }

            $form->addElement(new XoopsFormLabel(_AD_UUCART_PICKUP_VIEW, $str, $pic));
        }

        $set_category = ($this->gcid) ?: '--';

        $formcategory = new XoopsFormSelect(_AD_UUCART_CATEGORY, 'gcid', $set_category);

        $formcategory->addOptionArray($categorys);

        $formcategory->setExtra('onchange="category_change(document.pickup_select.gcid)"');

        $form->addElement($formcategory);

        $form->addElement($formbutton);

        if ($formgoods) {
            $form->addElement($formgoods);
        }

        $form->addElement(new XoopsFormTextDateSelect(_AD_UUCART_PICKUP_ON, 'ondays', 15, time()));

        $form->addElement(new XoopsFormTextDateSelect(_AD_UUCART_PICKUP_OFF, 'offdays', 15, time()));

        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_PICKUP_LOCK, 'type', 0));

        $form->addElement(new XoopsFormButton('', 'pickup', _AD_UUCART_SUBMIT, 'submit'));

        $form->display();
    }

    public function uu_admin_monthly($mid)
    {
        global $_SERVER;

        $formgoods = false;

        $pic = false;

        $this->uu_admin_pickup_delete();

        echo '
		<script type="text/javascript">
		<!--
		function category_change(category)
		{
			var n = category.selectedIndex;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pickup&amp;item=" + category.options[n].value;
		}
		//-->
		</script>
		<h4>' . _AD_UUCART_PICKUP . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<p style="text-align:left;">' . _AD_UUCART_PICKUP_DESC . '</p>';

        $categorys = $this->cart->get_categorys_array();

        $categorys = str_replace(_AD_UUCART_NEW, _AD_UUCART_CATEGORY_S, $categorys);

        $form = new XoopsThemeForm(_AD_UUCART_NEW_GOODS, 'pickup_select', $_SERVER['SCRIPT_NAME'], 'POST');

        if ($gcid) {
            $this->gcid = (int)$gcid;

            if (true === $this->cart->get_goods_check($this->gcid)) {
                $goods = $this->cart->get_goods_array($this->gcid);

                $goods = str_replace(_AD_UUCART_NEW, _AD_UUCART_SELECT_GOODS, $goods);

                $formgoods = new XoopsFormSelect(_AD_UUCART_SELECT_GOODS, 'gid', $this->gid);

                $formgoods->addOptionArray($goods);
            }
        }

        $pic = $this->cart->_pickup();

        if ($pic) {
            $str = '';

            $src = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pickupdel&amp;gid=%d">%s</a>';

            foreach ($pic as $val) {
                $str .= sprintf($src, $val['gid'], $val['top'] . '&nbsp;&gt;&gt;&nbsp;' . $val['offdays']) . "<br>\n";
            }

            $form->addElement(new XoopsFormLabel(_AD_UUCART_PICKUP_VIEW, $str, $pic));
        }

        $set_category = ($this->gcid) ?: '--';

        $formcategory = new XoopsFormSelect(_AD_UUCART_CATEGORY, 'gcid', $set_category);

        $formcategory->addOptionArray($categorys);

        $formcategory->setExtra('onchange="category_change(document.pickup_select.gcid)"');

        $form->addElement($formcategory);

        $form->addElement($formbutton);

        if ($formgoods) {
            $form->addElement($formgoods);
        }

        $form->addElement(new XoopsFormTextDateSelect(_AD_UUCART_PICKUP_ON, 'ondays', 15, time()));

        $form->addElement(new XoopsFormTextDateSelect(_AD_UUCART_PICKUP_OFF, 'offdays', 15, time()));

        $form->addElement(new XoopsFormRadioYN(_AD_UUCART_PICKUP_LOCK, 'type', 0));

        $form->addElement(new XoopsFormButton('', 'pickup', _AD_UUCART_SUBMIT, 'submit'));

        $form->display();
    }

    public function imagedelete_db($gid, $im)
    {
        $sql = 'UPDATE ' . $this->db->prefix('goods') . " SET images = '', ptype = '' WHERE gid = " . $gid;

        $result = $this->cart->result_sql($sql);

        if ($result) {
            $this->imagedelete($im);

            return true;
        }  

        $this->err = _AD_UUCART_IM_DEL_FALSE;

        return false;
    }

    public function imagedelete($im)
    {
        global $xoopsModule;

        $upload = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads/';

        $main = 'main_' . $im;

        $thumbnail = 'thum_' . $im;

        $pickup = 'pic_' . $im;

        if (file_exists($upload . $im)) {
            @unlink($upload . $im);
        }

        if (file_exists($upload . $main)) {
            @unlink($upload . $main);
        }

        if (file_exists($upload . $thumbnail)) {
            @unlink($upload . $thumbnail);
        }

        if (file_exists($upload . $pickup)) {
            @unlink($upload . $pickup);
        }
    }

    public function imageadminister()
    {
        global $xoopsModule;

        $sql = 'SELECT gid,top,images FROM ' . $this->db->prefix('goods') . " WHERE images is not null OR images != ''";

        $db_image = [];

        $diff_image = [];

        $all_size = 0;

        $url = XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/images/uploads/';

        $dir = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/images/uploads/';

        $im = "<a href=\"javascript:openWithSelfMain('" . $url . "%s','popup',450,380);\">%s</a>";

        $a = dir($dir);

        $images = [];

        while ($fname = $a->read()) {
            if (preg_match('/(png|gif|jpe?g)$/i', $fname)) {
                if (preg_match('/(thum_|main_|pic_)/i', $fname)) {
                    continue;
                }

                $images[] = $fname;

                $all_size += filesize($dir . $fname);
            }
        }

        $a->close();

        $size = number_format(($all_size / 1024), 2) . ' kb /' . (int)($all_size / 1024 / 1024) . ' MB';

        echo '
				<h4>' . _AD_UUCART_IM_MANAGER . '&nbsp;' . $size . '</h4>
				<h4>' . _AD_UUCART_GOMENU . '</h4>
				<h4>' . _AD_UUCART_IM_DB . '</h4>
				<table border="0" cellpadding="2" cellspacing="1" width="100%">
					<tbody>
						<tr>
							<td class="head" align="center">' . _AD_UUCART_GOODS_NAME . '</td>
							<td class="head" align="center">' . _AD_UUCART_IM . '</td>
							<td class="head" align="center">' . _AD_UUCART_DELETE . '</td>
						</tr>';

        $result = &$this->db->query($sql);

        if (!$this->db->error($result) && $result) {
            $css = 'odd';

            $picd = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pic&amp;im=%s&amp;n=%s">' . _AD_UUCART_IM_DELETE . '</a>';

            while (false !== ($row = $this->db->fetchArray($result))) {
                if (empty($row['images'])) {
                    continue;
                }

                $db_image[] = $row['images'];

                echo '
						<tr>
							<td class="' . $css . '" align="center">' . $row['top'] . '</td>
							<td class="' . $css . '" align="center">' . sprintf($im, $row['images'], $row['images']) . '</td>
							<td class="' . $css . '" align="center">' . sprintf($picd, $row['images'], $row['gid']) . '</td>
						</tr>';

                $css = ('odd' == $css) ? 'even' : 'odd';
            }

            $this->db->freeRecordSet($result);
        }

        echo '
				</tbody>
			</table>';

        $sql = 'SELECT gid,top,addmag FROM ' . $this->db->prefix('goods') . " WHERE addmag is not null OR addmag != ''";

        $result = &$this->db->query($sql);

        echo '
				<h4>' . _AD_UUCART_ADDIMG . '</h4>
				<table border="0" cellpadding="2" cellspacing="1" width="100%">
					<tbody>
						<tr>
							<td class="head" align="center">' . _AD_UUCART_ADDIMG . '</td>
							<td class="head" align="center">' . _AD_UUCART_IM . '</td>
							<td class="head" align="center">' . _AD_UUCART_DELETE . '</td>
						</tr>';

        if (!$this->db->error($result) && $result) {
            $css = 'odd';

            $picd = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=pic&amp;im=%s&amp;n=%s">' . _AD_UUCART_IM_DELETE . '</a>';

            while (false !== ($row = $this->db->fetchArray($result))) {
                if (empty($row['addmag'])) {
                    continue;
                }

                $db_image[] = $row['addmag'];

                echo '
						<tr>
							<td class="' . $css . '" align="center">' . $row['top'] . '</td>
							<td class="' . $css . '" align="center">' . sprintf($im, $row['addmag'], $row['addmag']) . '</td>
							<td class="' . $css . '" align="center">' . sprintf($picd, $row['addmag'], $row['gid']) . '</td>
						</tr>';

                $css = ('odd' == $css) ? 'even' : 'odd';
            }

            $this->db->freeRecordSet($result);
        }

        echo '
				</tbody>
			</table>';

        $sql = 'SELECT gcid,top,images FROM ' . $this->db->prefix('goods_category') . " WHERE images is not null OR images != ''";

        $result = &$this->db->query($sql);

        echo '
				<h4>' . _AD_UUCART_CATEGORY . '</h4>
				<table border="0" cellpadding="2" cellspacing="1" width="100%">
					<tbody>
						<tr>
							<td class="head" align="center">' . _AD_UUCART_CATEGORY . '</td>
							<td class="head" align="center">' . _AD_UUCART_IM . '</td>
							<td class="head" align="center">' . _AD_UUCART_DELETE . '</td>
						</tr>';

        if (!$this->db->error($result) && $result) {
            $css = 'odd';

            $picd = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=piccategory&amp;im=%s&amp;n=%s">' . _AD_UUCART_IM_DELETE . '</a>';

            while (false !== ($row = $this->db->fetchArray($result))) {
                if (empty($row['images'])) {
                    continue;
                }

                $db_image[] = $row['images'];

                echo '
						<tr>
							<td class="' . $css . '" align="center">' . $row['top'] . '</td>
							<td class="' . $css . '" align="center">' . sprintf($im, $row['images'], $row['images']) . '</td>
							<td class="' . $css . '" align="center">' . sprintf($picd, $row['images'], $row['gcid']) . '</td>
						</tr>';

                $css = ('odd' == $css) ? 'even' : 'odd';
            }

            $this->db->freeRecordSet($result);
        }

        echo '
				</tbody>
			</table>';

        $diff_image = array_diff($images, $db_image);

        if ($diff_image) {
            $imgoff = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?uu_cart=image&amp;im=%s">' . _AD_UUCART_DELETE . '</a>';

            echo '
			<h4>' . _AD_UUCART_IM_NON_DB . '</h4>
			<table border="0" cellpadding="2" cellspacing="1" width="100%">
				<tbody>
					<tr>
						<td class="head" align="center">' . _AD_UUCART_IM . '</td>
						<td class="head" align="center">' . _AD_UUCART_IM_SIZE . '</td>
						<td class="head" align="center">' . _AD_UUCART_DELETE . '</td>
					</tr>';

            foreach ($diff_image as $val) {
                $imsize = filesize($dir . $val);

                $psize = number_format(($imsize / 1024), 2) . ' kb';

                echo '
						<tr>
							<td class="' . $css . '" align="center">' . sprintf($im, $val, $val) . '</td>
							<td class="' . $css . '" align="center">' . $psize . '</td>
							<td class="' . $css . '" align="center">' . sprintf($imgoff, $val) . '</td>
						</tr>';
            }

            echo '
				</tbody>
			</table>';
        }
    }

    public function get_error()
    {
        return $this->err;
    }
}
