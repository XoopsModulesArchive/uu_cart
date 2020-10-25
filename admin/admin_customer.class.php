<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Bad!!Access error none mainfile:');
    exit();
}

if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
    trigger_error('Access Denied');
    exit('Access Denied');
}

require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';
require_once dirname(__DIR__) . '/include/Myformdate.php';
require_once dirname(__DIR__) . '/include/user_function.php';

class admin_customer extends uu_cart_user
{
    public $err   = '';
    public $ulist = [
        'id'        => 'ID',
        'name'      => _UUCART_USER_NAME,
        'name2'     => _UUCART_USER_NAME,
        'read_it'   => _UUCART_USER_READ,
        'read2_it'  => _UUCART_USER_READ,
        'uid'       => 'XOOPS UID',
        'sex'       => _UUCART_USER_SEX,
        'pass'      => 'PASSWORD MD5',
        'entering'  => _AD_UUCART_ORDER01,
        'birthday'  => _UUCART_USER_BIRTHDAY,
        'email'     => 'E-MAIL',
        'zip'       => _UUCART_USER_ZIP,
        'pref'      => _UUCART_USER_PREF,
        'address'   => _UUCART_USER_ADDRESS,
        'tel'       => 'TEL',
        'fax'       => 'FAX',
        'mobile'    => _UUCART_USER_MOBILE,
        'sess_id'   => 'SESSION',
        'point'     => _AD_UUCART_ORDER02,
        'magazine'  => _UUCART_USER_MAGAZINE,
        'l_time'    => 'Last Access',
        'falseness' => _AD_UUCART_ORDER07,
    ];
    public $month;

    public function __construct()
    {
        parent::__construct();
    }

    public function _post_sanitiz($post)
    {
        $n_post = array_map(
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
			}'
            ),
            $post
        );
        unset($n_post['editid']);
        if (isset($n_post['zip'])) {
            $n_post['zip'] = $this->_convert_alnum($n_post['zip']);
        }
        if ($n_post) {
            return $n_post;
        }
        return false;
    }

    public function _personal_history($arr, $id)
    {
        $user = $this->_customer($id);
        echo '
		<h4>'
             . _AD_UUCART_HISTORY
             . '</h4>
		<h4>'
             . _AD_UUCART_GOMENU
             . '</h4>
		<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>
		<table border="0" cellpadding="2" cellspacing="1" width="100%">
			<tbody>
				<tr>
					<td class="head" style="text-align:center;" colspan="6"><a href="admin_customer.php?uu_cust=custmod&amp;id='
             . $id
             . '" style="padding: 3px 0.5em; margin-left:3px;border: 1px solid #778000;background: #FFFFFF;text-decoration: none;">'
             . _AD_UUCART_PROFILE_MOD
             . '</a> <a href="admin_customer.php?uu_cust=mail&amp;id='
             . $id
             . '" style="padding: 3px 0.5em; margin-left:3px;border: 1px solid #778000;background: #FFFFFF;text-decoration: none;">'
             . _AD_UUCART_MAIL
             . '</a></td>
				</tr>
				<tr>
					<td class="head" style="text-align:center;">'
             . $user['name']
             . '</td>
					<td class="head" style="text-align:center;">'
             . $user['email']
             . '</td>
					<td class="head" style="text-align:center;">'
             . $this->prefecture[$user['pref']]
             . '</td>
					<td class="head" style="text-align:center;" colspan="2">'
             . $user['address']
             . '</td>
					<td class="head" style="text-align:center;">'
             . $user['tel']
             . '</td>
				</tr>
				<tr>
					<td colspan="6" style="text-align:center;"><hr></td>
				</tr>
			';
        $this->_layout($arr);
    }

    public function _layout($src, $user = null)
    {
        $class = 'even';
        foreach ($src as $key => $val) {
            $all = ($val['price'] * $val['num']) + $val['shipping'] + $val['coolval'];
            if ($val['receipt'] == 0) {
                $deposit  = '<span style="color:tomato;">' . _AD_UUCART_NORECEIPT . '</span>';
                $_deposit = _AD_UUCART_NORECEIPT;
            } else {
                $deposit  = _UCART_ITEM_YEN . number_format($val['receipt']);
                $_deposit = _AD_UUCART_RECEIPT_DAY . $val['r_day'];
            }
            $delivery = ($val['p_day']) ?: _AD_UUCART_DELIVERY_N;
            echo '
			<tr>
				<td colspan="6" style="text-align:left;">' . _AD_UUCART_ORDER_TIME . $val['f_day'] . ' / ' . _AD_UUCART_DELIVERY . $delivery . ' / ' . $_deposit . '</td>
			</tr>';
            if ($user) {
                echo '
			<tr>
				<td class="' . $class . '" style="text-align:right;">' . $val['name'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['email'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . _AD_UUCART_ZIPMARK . zip_format_check($val['zip']) . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $this->prefecture[$val['pref']] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['address'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . tel_format_check($val['tel']) . '</td>
			</tr>';
            }
            echo '
			<tr>
				<td class="' . $class . '" style="text-align:right;">' . $val['top'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['price'] . '</td>
				<td class="' . $class . '" style="text-align:right;"><span style="color:red;">' . $val['package'] . '</span></td>
				<td class="' . $class . '" style="text-align:right;">' . $val['price'] . '¡ß<span style="font-weight:bold;">' . $val['num'] . '</span>=' . ($val['price'] * $val['num']) . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['postage'] . '¡ß<span style="color:red;">' . (ceil($val['num'] / $val['package'])) . '</span> = ' . (ceil($val['num'] / $val['package'])) * $val['postage'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['coolval'] . '</td>
			</tr>
			<tr>
				<td class="' . $class . '" style="text-align:right;">' . $val['name1'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $val['address1'] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . _AD_UUCART_ZIPMARK . zip_format_check($val['zip1']) . '</td>
				<td class="' . $class . '" style="text-align:right;">' . $this->prefecture[$val['pref1']] . '</td>
				<td class="' . $class . '" style="text-align:right;">' . tel_format_check($val['tel1']) . '</td>
				<td class="' . $class . '" style="text-align:right;">' . _AD_UUCART_TOTAL . number_format($all) . '</td>
			</tr>
			<tr class="foot">
				<td colspan="1" style="text-align:right;">' . $deposit . '</td>
				<td colspan="5" style="text-align:center;"><a href="admin_customer.php?uu_cust=msend&amp;buy=' . $val['buy'] . '" style="padding: 3px 0.5em; margin-left:3px;border: 1px solid #778000;background: #FFFFFF;text-decoration: none;">' . _AD_UUCART_SENT . '</a></td>
			</tr>
			<tr>
				<td colspan="6" style="text-align:center;"><hr></td>
			</tr>
			';
            $class = ($class == 'even') ? 'odd' : 'even';
        }
        echo '
				</tbody>
			</table>';
    }

    public function customer_modify($_id = '--', $page = 0, $sort = 'id', $order = 'ASC')
    {
        $row = false;
        echo '
		<script type="text/javascript">
		<!--
		function customer_change()
		{
			var p = document.customer.page.value;
			var s = document.customer.sort.value;
			var o = document.customer.order.value;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cust=view&amp;page="+p+"&amp;sort="+s+"&amp;order="+o;
		}
		function customer_id()
		{
			var n = document.customer.id.value;
			location.href = "' . $_SERVER['SCRIPT_NAME'] . '?uu_cust=view&amp;id="+n;
		}
		function customer_pref(cust)
		{
			var p = cust.selectedIndex;
			document.customer.valpref.value = cust.options[p].text;
		}
		//-->
		</script>
		<h4>' . _AD_UUCART_CUSTOMER . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>';
        $form = new XoopsThemeForm(_AD_UUCART_CUSTOMER, 'customer', $_SERVER['SCRIPT_NAME'], 'POST');
        if ($_id) {
            $dat = $this->_customer($_id);
            foreach ($dat as $key => $val) {
                if ($key == 'sess_id' || $key == 'name2' || $key == 'read2_it') {
                    continue;
                }
                if ($key == 'zip') {
                    $val = zip_format_check($val);
                }
                if ($key == 'tel' || $key == 'fax' || $key == 'mobile') {
                    $val = tel_format_check($val);
                }
                if ($key == 'pref') {
                    $valpref  = $this->prefecture[$val];
                    $preftray = new XoopsFormElementTray($this->ulist[$key], '&nbsp;');
                    $preftray->addElement(new XoopsFormText('', 'valpref', 40, 250, $valpref));
                    $formpref = new XoopsFormSelect('', 'pref', $val);
                    $formpref->addOptionArray($this->prefecture);
                    $formpref->setExtra('onChange="customer_pref(this)"');
                    $preftray->addElement($formpref);
                    $form->addElement($preftray);
                } elseif ($key == 'sex') {
                    $valsex  = ($val == 0) ? _UUCART_USER_MAN : _UUCART_USER_WOMAN;
                    $sextray = new XoopsFormElementTray(_UUCART_USER_SEX, '&nbsp;');
                    $sex     = new XoopsFormSelect('', $key);
                    $sex->addOptionArray([0 => _UUCART_USER_MAN, 1 => _UUCART_USER_WOMAN], $val);
                    $valsex = new XoopsFormText('', 'valsex', 10, 10, $valsex);
                    $valsex->setExtra('readonly');
                    $sextray->addElement($valsex);
                    $sextray->addElement($sex);
                    $form->addElement($sextray);
                } elseif ($key == 'id' || $key == 'uid') {
                    ${$key} = new XoopsFormText($this->ulist[$key], $key, 10, 250, $val);
                    ${$key}->setExtra('disabled');
                    $form->addElement(${$key});
                } else {
                    $form->addElement(new XoopsFormText($this->ulist[$key], $key, 40, 250, $val));
                }
            }
            $form->addElement(new XoopsFormHidden('id', $_id));
            $submit = new XoopsFormElementTray('', '&nbsp;');
            $form->addElement(new XoopsFormButton(_AD_UUCART_EDIT, 'editid', _AD_UUCART_EDIT, 'submit'));
        } else {
            $custtray = new XoopsFormElementTray(_AD_UUCART_SELECT, '&nbsp;');
            $custpage = new XoopsFormSelect('', 'page', $page);
            $custpage->addOptionArray($this->_customer_pages());
            $custsort = new XoopsFormSelect('', 'sort', $sort);
            $custsort->addOptionArray(
                [
                    'id'       => _AD_UUCART_ORDER03,
                    'entering' => _AD_UUCART_ORDER01,
                    'pref'     => _AD_UUCART_ORDER04,
                    'point'    => _AD_UUCART_ORDER02,
                ]
            );
            $custorder = new XoopsFormSelect('', 'order', $order);
            $custorder->addOptionArray(
                [
                    'ASC'  => _ASCENDING,
                    'DESC' => _DESCENDING,
                ]
            );
            $button = new XoopsFormLabel('', '<a href="javascript:customer_change();" style="padding: 3px 0.5em; margin-left:3px;border: 1px solid #778000;background: #FFFFFF;text-decoration: none;">' . _AD_UUCART_CHANGE . '</a>');
            $custtray->addElement($custpage);
            $custtray->addElement($custsort);
            $custtray->addElement($custorder);
            $custtray->addElement($button);
            $form->addElement($custtray);
            $custid = new XoopsFormSelect(_AD_UUCART_ORDER06, 'id', $_id);
            $custid->addOptionArray($this->_customers_select($sort, $order, $page));
            $custid->setExtra('onChange="customer_id()"');
            $form->addElement($custid);//$sort;
        }
        $form->display();
    }

    public function _mail($id)
    {
        $user = $this->_customer($id);
        echo '
		<h4>' . _AD_UUCART_MAIL . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>';
        $form = new XoopsThemeForm(_AD_UUCART_MAIL, 'to_nail', $_SERVER['SCRIPT_NAME'], 'POST');
        $form->addElement(new XoopsFormLabel(_UUCART_USER_NAME, $user['name'] . ' ' . $user['email']));
        $form->addElement(new XoopsFormText('Subject', 'subject', 50, 250), true);
        $form->addElement(new XoopsFormTextArea(_AD_UUCART_MAI_BODY, 'body', '', 10), true);
        $form->addElement(new XoopsFormButton(_AD_UUCART_MAIL, 'mail_sent', _AD_UUCART_MAI_TO_USER, 'submit'));
        $form->addElement(new XoopsFormHidden('id', $id));
        $form->display();
    }

    public function _mail_send($arr)
    {
        $user   = $this->_customer((int)$arr['id']);
        $arr    = $this->_array_hSC_walk($arr);
        $assign = ['BODY' => $arr['body']];
        return u_cart_Mailer('normal.tpl', $assign, $user['email'], $arr['subject']);
    }

    public function _make_sent($msend = 1)
    {
        $res = $this->_union(null, $msend);
        if (!$res) {
            return false;
        }
        $this->make_buy_table($res);
        return true;
    }

    public function make_buy_table($res)
    {
        echo '
	<h4>' . _AD_UUCART_ORDERSEND . '</h4>
	<h4>' . _AD_UUCART_GOMENU . '</h4>
	<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tbody>
			<tr>
				<td class="head" style="text-align:center;">' . _UUCART_USER_NAME . '</td>
				<td class="head" style="text-align:center;">MAIL</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_ZIP . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_PREF . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_ADDRESS . '</td>
				<td class="head" style="text-align:center;width:15%;">' . _UUCART_USER_TEL . '</td>
			</tr>
			<tr>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_NAME . '</td>
				<td class="head" style="text-align:center;">' . _UCART_PRICE_INDEX . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_PACKAGES . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_PURCH . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_POSTAGE . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_COOL . '</td>
			</tr>
			<tr>
				<td class="head" colspan="6" style="text-align:center;">' . _UUCART_USER_DESTINATION . '</td>
			</tr>
			<tr>
				<td colspan="6" style="text-align:center;"><hr></td>
			</tr>
			';
        $this->_layout($res, 'ON');
        echo '
				</tbody>
			</table>';
    }

    public function _msent($buy)
    {
        $res       = $this->_union($buy, 1);
        $all       = ($res[0]['price'] * $res[0]['num']) + $res[0]['shipping'] + $res[0]['coolval'];
        $deposit   = ($res[0]['receipt'] == 0) ? _AD_UUCART_NORECEIPT . ' :: ' . number_format($all) : number_format($res[0]['receipt']);
        $_delivery = ($res[0]['p_day']) ? true : false;
        $all       = ($res[0]['price'] * $res[0]['num']) + $res[0]['shipping'] + $res[0]['coolval'];
        echo '
	<h4>' . _AD_UUCART_ORDERSEND . '</h4>
	<h4>' . _AD_UUCART_GOMENU . '</h4>
	<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>
	<form name="ship" id="ship" action="admin_customer.php" method="POST" onsubmit="return xoopsFormValidate_general();">
	<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tbody>
			<tr>
				<td class="head" style="text-align:center;">' . _UUCART_USER_NAME . '</td>
				<td class="head" style="text-align:center;">MAIL</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_ZIP . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_PREF . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_ADDRESS . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_USER_TEL . '</td>
			</tr>
			<tr>
				<td class="odd" style="text-align:right;">' . $res[0]['name'] . '</td>
				<td class="odd" style="text-align:right;">' . $res[0]['email'] . '</td>
				<td class="odd" style="text-align:right;">' . _AD_UUCART_ZIPMARK . zip_format_check($res[0]['zip']) . '</td>
				<td class="odd" style="text-align:right;">' . $this->prefecture[$res[0]['pref']] . '</td>
				<td class="odd" style="text-align:right;">' . $res[0]['address'] . '</td>
				<td class="odd" style="text-align:right;">' . tel_format_check($res[0]['tel']) . '</td>
			</tr>
			<tr>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_NAME . '</td>
				<td class="head" style="text-align:center;">' . _UCART_PRICE_INDEX . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_PACKAGES . '</td>
				<td class="head" style="text-align:center;">' . _UUCART_PURCH . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_POSTAGE . '</td>
				<td class="head" style="text-align:center;">' . _UCART_ITEM_COOL . '</td>
			</tr>
			<tr>
				<td class="even" style="text-align:right;">' . $res[0]['top'] . '</td>
				<td class="even" style="text-align:right;">' . $res[0]['price'] . '</td>
				<td class="even" style="text-align:right;">' . $res[0]['package'] . '</td>
				<td class="even" style="text-align:right;">' . $res[0]['num'] . '</td>
				<td class="even" style="text-align:right;">' . $res[0]['postage'] . '¡ß<span style="color:red;">' . (ceil($res[0]['num'] / $res[0]['package'])) . '</span> = ' . (ceil($res[0]['num'] / $res[0]['package'])) * $res[0]['postage'] . '</td>
				<td class="even" style="text-align:right;">' . $res[0]['coolval'] . '</td>
			</tr>
			<tr>
				<td class="head" colspan="5" style="text-align:center;">' . _UUCART_USER_DESTINATION . '</td>
				<td class="head" colspan="1" style="text-align:center;">' . _AD_UUCART_SLIPNUM . '</td>
			</tr>
			<tr>
				<td class="odd" style="text-align:right;">' . $res[0]['name1'] . '</td>
				<td class="odd" style="text-align:right;">' . $res[0]['address1'] . '</td>
				<td class="odd" style="text-align:right;">' . _AD_UUCART_ZIPMARK . zip_format_check($res[0]['zip1']) . '</td>
				<td class="odd" style="text-align:right;">' . $this->prefecture[$res[0]['pref1']] . '</td>
				<td class="odd" style="text-align:right;">' . tel_format_check($res[0]['tel1']) . '</td>
				<td class="odd" style="text-align:right;"><input type="text" name="slipnum" id="slipnum" size="20" maxlength="50" value=""></td>
			</tr>';
        if ($res[0]['receipt'] == 0) {
            echo '
				<tr>
					<td class="even" colspan="1" style="text-align:right;">' . $deposit . '</td>
					<td class="even" colspan="5" style="text-align:center;">' . _AD_UUCART_CANCEL_MES . '<input type="text" name="receipt" id="receipt" size="20" maxlength="50" value=""> <input type="submit" name="deposit" id="deposit" value="' . _AD_UUCART_RECEIPT . '"></a></td>
				</tr>';
        } else {
            echo '
				<tr>
					<td class="even" colspan="6" style="text-align:center;"><span style="font-weight:bold;">' . _UCART_ITEM_YEN . $deposit . '</span></td>
				</tr>';
        }
        if (false === $_delivery) {
            echo '
				<tr>
					<td class="head" colspan="4" style="text-align:center;"><input type="submit" name="sent_mail" id="sent_mail" value="' . _AD_UUCART_SENT_MAIL . '"></td>
					<td class="head" colspan="2" style="text-align:center;"><input type="submit" name="sent_n" id="sent_n" value="' . _AD_UUCART_SENT_NOMAIL . '"></td>
				</tr>
				<tr>
					<td class="head" colspan="6" style="text-align:center;">' . _AD_UUCART_CANCEL_MES . '<br><input type="submit" name="cancel" id="cancel" value="' . _AD_UUCART_CANCEL_DB . '"></td>
				</tr>';
        } else {
            echo '
				<tr>
					<td class="head" colspan="6" style="text-align:center;">' . _AD_UUCART_DELIVERY . $res[0]['p_day'] . '</td>
				</tr>';
        }
        echo '
		</tbody>
	</table><input type="hidden" name="buy" id="buy" value="' . $res[0]['buy'] . '"></form>
			<script type="text/javascript">
			<!--//
			function xoopsFormValidate_general() {
				myform = window.document.ship;
			if ( myform.slipnum.value == "" ) { window.alert("' . _AD_UUCART_NONSLIP . '"); myform.slipnum.focus(); return false; }
			}
			//--></script>
			';
        return $res;
    }

    public function _customer($id)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('buy_user') . " WHERE id = '" . (int)$id . "'";
        return $this->_result($sql);
    }

    public function _customer_pages()
    {
        $sql    = 'SELECT count(*) as c FROM ' . $this->db->prefix('buy_user');
        $result =& $this->db->query($sql);
        $row    = $this->db->fetchArray($result);
        $n      = ceil($row['c'] / 30);
        if ($n > 1) {
            return range(0, $n);
        }
        return ['--' => '--', '0' => 0];
    }

    public function _customers_select($sort = 'id', $order = 'ASC', $page = 0)
    {
        $start    = $page * 50;
        $sql_puls = ' ORDER BY ' . $sort . ' ' . $order;
        $sql      = 'SELECT * FROM ' . $this->db->prefix('buy_user') . $sql_puls;
        $result   =& $this->db->query($sql, 50, $start);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            $cust       = [];
            $cust['--'] = '--';
            while (false !== ($row = $this->db->fetchArray($result))) {
                $cust[$row['id']] = $this->myts->htmlSpecialChars($row['name']);
            }
            return $cust;
        }
        return false;
    }

    public function receipt($receiptnum, $buy)
    {
        $val    = $this->_convert_alnum($receiptnum);
        $sql    = 'UPDATE ' . $this->db->prefix('buy_table') . " SET receipt = '" . $val . "',r_day = NOW() WHERE buy = '" . $buy . "'";
        $result = $this->result_sql($sql);
        return $result;
    }

    public function delivery($slipnum, $buy, $mail = true)
    {
        global $xoopsConfig;
        $res      = $this->_union($buy, 1);
        $sql      = 'UPDATE ' . $this->db->prefix('buy_table') . " SET msend = 2, slipnum = '" . $slipnum . "', p_day = NOW() WHERE buy = '" . $buy . "'";
        $result   = $this->result_sql($sql);
        $notation = $this->get_notation();
        if (true === $mail) {
            $assign = [
                'UU_USER_NAME' => $res[0]['name'],
                'SLIPNUM'      => sprintf(_AD_UUCART_SLIPMAIL, $slipnum),
                'TRANSPORTER'  => $this->get_transporter_name($notation['transport']),
                'TRANSPORTURL' => $this->get_transporter_url($notation['transport']),
                'GOODSNAME'    => _UUCART_MAIL00 . $res[0]['top'],
                'GOODSNUM'     => _AD_UUCART_ITEM_NUM . $res[0]['num'],
                'ADDRESS'      => _AD_UUCART_ADDRESS . "\n" . zip_format_check($res[0]['zip1']) . ' ' . $res[0]['address1'],
                'CONSIGNEE'    => $res[0]['name1'],
            ];
            return u_cart_Mailer('sent_notify.tpl', $assign, $res[0]['email'], sprintf(_UUCART_SUBJECT01, $xoopsConfig['sitename']));
        }
        return $result;
    }

    public function cancel($buy)
    {
        $sql   = [];
        $src   = $this->_union($buy, 1);
        $sql[] = 'UPDATE ' . $this->db->prefix('goods') . ' SET rank = rank-' . $src[0]['num'] . ', stock = stock+' . $src[0]['num'] . " WHERE gid = '" . $src[0]['gid'] . "'";
        $sql[] = 'DELETE FROM ' . $this->db->prefix('buy_table') . " WHERE buy = '" . $buy . "'";
        $sql[] = 'DELETE FROM ' . $this->db->prefix('buy_addressee') . " WHERE buy_addressee = '" . $src[0]['buy_addressee'] . "'";
        $res   = true;
        for ($i = 0, $iMax = count($sql); $i < $iMax; $i++) {
            $res = $this->result_sql($sql[$i]);
        }
        return $res;
    }

    public function _union($buy = null, $msend = 1)
    {
        $arr = $this->_buy_table($msend, $buy);
        return $this->_buy_addressee($arr);
    }

    public function _buy_user($id, $sday = null, $eday = null, $order = 'ASC')
    {
        //if (! $sday) $sday = $this->month['sday'];
        //if (! $eday) $eday = $this->month['eday'];
        $sql    = "SELECT *, (t.packages*t.postage) as shipping, t.cool as coolval, DATE_FORMAT(t.times, '%Y-%m-%d %H:%i') as f_day FROM " . $this->db->prefix('buy_table') . ' as t ';
        $sql    .= 'LEFT OUTER JOIN ' . $this->db->prefix('goods') . ' as tt USING(gid) ';
        $sql    .= "WHERE t.id = '" . $id . "' AND t.falseness = 't' AND t.msend > '0' ORDER BY t.times " . $order;
        $result = $this->_result_rows($sql);
        return $this->_buy_addressee($result);
    }

    public function _buy_table($msend, $buy = null, $op = null)
    {
        $buy_table = [];
        $sql       = "SELECT *, (t.packages*t.postage) as shipping, t.cool as coolval, DATE_FORMAT(t.times, '%Y-%m-%d %H:%i') as f_day FROM " . $this->db->prefix('buy_table') . ' as t ';
        $sql       .= 'LEFT OUTER JOIN ' . $this->db->prefix('buy_user') . ' as tt USING(id) ';

        if ($op) {
            $sql .= $op;
        } elseif ($buy) {
            $sql .= "WHERE t.falseness = 't' AND t.buy = '" . $buy . "'";
        } else {
            $sql .= "WHERE t.falseness = 't' AND ( receipt = 0 OR t.msend = '" . $msend . "' ) ORDER BY t.times ASC";
        }
        $result =& $this->db->query($sql);
        return $this->_add_goods($result);
    }

    public function _buy_addressee($arr)
    {
        if (!$arr) {
            return false;
        }
        $address = [];
        $sql     = 'SELECT buy_addressee, name as name1, readit as readit1, zip as zip1, pref as pref1, address as address1, tel as tel1 FROM ' . $this->db->prefix('buy_addressee') . " WHERE uniq = '%s'";
        foreach ($arr as $val) {
            $result = $this->_result(sprintf($sql, $val['uniq']));
            if ($result) {
                $addressee = $this->_array_hSC_walk($result);
                $address[] = array_merge($val, $addressee);
            }
        }
        return $address;
    }

    public function _add_goods(&$result)
    {
        $sqlgoods = 'SELECT * FROM ' . $this->db->prefix('goods') . " WHERE gid ='%s'";
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            $user = [];
            $arr  = [];
            while (false !== ($row = $this->db->fetchArray($result))) {
                $goods = $this->_result(sprintf($sqlgoods, (int)$row['gid']));
                $row   = array_merge($row, $goods);
                $arr[] = $this->_array_hSC_walk($row);
            }
            return $arr;
        }
        return false;
    }

    public function _convert_alnum($str)
    {
        return preg_replace('/[^0-9]/i', '', mb_convert_kana($str, 'a'));
    }

    public function get_error()
    {
        return $this->err;
    }

    public function _cart_sessions_gc()
    {
        $sql    = 'SELECT gid,buy FROM ' . $this->db->prefix('cart_sessions') . ' WHERE times < DATE_SUB(CURRENT_DATE(), INTERVAL 1 HOUR)';
        $sqldel = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . ' WHERE times < DATE_SUB(CURRENT_DATE(), INTERVAL 1 HOUR)';
        $result = $this->_result($sql);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $sql = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock+' . (int)$row['buy'] . ' WHERE gid = ' . (int)$row['gid'];
                $this->result_sql($sql);
            }
        }
        return $this->result_sql($sqldel);
    }

    public function _user_profile($arr)
    {
        $id = (int)$arr['id'];
        //$arr['pref'] = array_search($arr['valpref'], $this->prefecture);
        unset($arr['valsex']);
        unset($arr['entering']);
        unset($arr['valpref']);
        unset($arr['id']);
        $arr    = array_filter(
            $arr,
            create_function(
                '$b',
                'if ($b == "") {
				return false;
			}
			return true;'
            )
        );
        $column = $this->sql_parse_updare($arr);
        $sql    = 'UPDATE ' . $this->db->prefix('buy_user') . ' SET ' . $column . ", l_time = NOW() WHERE id = '" . $id . "'";
        return $this->result_sql($sql);
    }
}
