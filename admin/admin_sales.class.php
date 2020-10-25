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

class admin_sales extends uu_cart_user
{
    public $err = '';

    public $month;

    public function __construct()
    {
        parent::__construct();

        //今月月初－月末

        $monthsql = 'SELECT DATE_ADD(DATE_SUB(CURRENT_DATE(), INTERVAL EXTRACT(DAY FROM CURRENT_DATE()) DAY), INTERVAL 1 DAY) as sday,DATE_ADD(DATE_SUB(CURRENT_DATE(), INTERVAL EXTRACT(DAY FROM CURRENT_DATE()) DAY), INTERVAL 31 DAY) as eday';

        $this->month = $this->_result($monthsql);
    }

    public function _sales($order = 'ASC')
    {
        echo '
		<h4>' . _AD_UUCART_ORDERSTATUS . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>';

        $form = new XoopsThemeForm(_AD_UUCART_CUSTOMER, 'customer', $_SERVER['SCRIPT_NAME'], 'GET');

        $form->addElement(new MyFormDateTime(_AD_UUCART_START, 'sday', 15, $this->month['sday'], 'ON'));

        $form->addElement(new MyFormDateTime(_AD_UUCART_END, 'eday', 15, $this->month['eday'], 'ON'));

        /*
        $salessort = new XoopsFormSelect('', 'sort', $sort);
        $salessort->addOptionArray(array(	'id'		=> _AD_UUCART_ORDER03,
                                            'entering'	=> _AD_UUCART_ORDER01,
                                            'pref'		=> _AD_UUCART_ORDER04,
                                            'point'		=> _AD_UUCART_ORDER02));
        */

        $salesorder = new XoopsFormSelect('', 'order', $order);

        $salesorder->addOptionArray(
            [
                'ASC' => _ASCENDING,
                'DESC' => _DESCENDING,
            ]
        );

        $form->addElement($salesorder);

        $form->addElement(new XoopsFormButton('', 'saleslist', _AD_UUCART_MAI_TO_USER, 'submit'));

        $form->addElement(new XoopsFormHidden('uu_cust', 'sales_view'));

        $form->display();
    }

    public function _format($src, $sday, $eday)
    {
        echo '
		<h4>' . _AD_UUCART_ORDERSTATUS . '</h4>
		<h4>' . _AD_UUCART_GOMENU . '</h4>
		<h4><a href="javaScript:history.go(-1)">[ BACK ]</a></h4>
		<form name="receipt" id="receipt" action="admin_sales.php" method="POST">
		<table border="0" cellpadding="2" cellspacing="1" width="100%">
			<tbody>
				<tr>
					<td class="head" style="text-align:center;" colspan="5">' . $sday . ' - ' . $eday . '</td>
				</tr>';

        $all_receipt = 0;

        $all_sales = 0;

        $cl = 'odd';

        foreach ($src as $key => $val) {
            $all_receipt += $val['receipt'];

            $all_sales += $val['price'] * $val['num'];

            $user = $this->_customer($val['id']);

            $r_day = ($val['r_day']) ? _AD_UUCART_RECEIPT_DAY . $val['r_day'] : '<span style="color:red;font-weight:bold;">' . _AD_UUCART_NORECEIPT . '</span>';

            echo '
				<tr>
					<td class="' . $cl . '" style="text-align:center;">' . $val['f_day'] . '</td>
					<td class="' . $cl . '" style="text-align:center;">' . _AD_UUCART_GOODS_NAME . ' ' . $val['top'] . '</td>
					<td class="' . $cl . '" style="text-align:center;">' . _UCART_PRICE_INDEX . ' ' . $val['price'] . '</td>
					<td class="' . $cl . '" style="text-align:right;">' . _UCART_ITEM_NUM . ' ' . $val['num'] . '</td>
					<td class="' . $cl . '" style="text-align:right;">' . _UCART_ITEM_YEN . number_format($val['num'] * $val['price']) . '</td>
				</tr>
				<tr>
					<td class="' . $cl . '" style="text-align:right;">' . $user['name'] . '</td>
					<td class="' . $cl . '" style="text-align:right;">' . $r_day . '</td>
					<td class="' . $cl . '" style="text-align:center;" colspan="2"><input type="text" name="receipt_' . $val['buy'] . '" size="10" maxlength="50" value="' . $val['receipt'] . '"> <input type="submit" name="change_' . $val['buy'] . '" value="' . _AD_UUCART_MOD_RECEIPT . '"></td>
					<td class="' . $cl . '" style="text-align:right;">' . _AD_UUCART_DELIVERY . $val['p_day'] . '</td>
				</tr>
				';

            $cl = ('even' == $cl) ? 'odd' : 'even';
        }

        echo '
				<tr>
					<td class="head" style="text-align:center;" colspan="5">' . _AD_UUCART__SALES . ' ' . number_format($all_receipt) . ' - ' . _AD_UUCART_ALL_SALES . ' ' . number_format($all_sales) . '</td>
				</tr>
				</tbody>
			</table>';
    }

    public function _buy($sday = null, $eday = null, $order = 'ASC')
    {
        if (!$sday) {
            $sday = $this->month['sday'];
        }

        if (!$eday) {
            $eday = $this->month['eday'];
        }

        $sql = "SELECT *, (t.packages*t.postage) as shipping, t.cool as coolval, DATE_FORMAT(t.times, '%Y-%m-%d %H:%i') as f_day FROM " . $this->db->prefix('buy_table') . ' as t ';

        $sql .= 'LEFT OUTER JOIN ' . $this->db->prefix('goods') . ' as tt USING(gid) ';

        $sql .= "WHERE times BETWEEN '" . $sday . "' AND '" . $eday . "' AND t.falseness = 't' AND t.msend = '2' ORDER BY t.times " . $order;

        return $this->_result_rows($sql);
    }

    public function _customer($id)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('buy_user') . " WHERE id = '" . (int)$id . "'";

        return $this->_result($sql);
    }

    public function receipt($receiptnum, $buy)
    {
        $val = $this->_convert_alnum($receiptnum);

        $sql = 'UPDATE ' . $this->db->prefix('buy_table') . " SET receipt = '" . $val . "',r_day = NOW() WHERE buy = '" . $buy . "'";

        $result = $this->result_sql($sql);

        return $result;
    }
}
