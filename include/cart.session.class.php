<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/function.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/transporter.class.php';

class u_cart_session extends uu_cart_function
{
    public $db;

    public $myts;

    public $cart;

    public $gid;

    public $stock;

    public $sessions;

    public $sess_ip;

    public $notation = [];

    public $ssl_mode = false;

    public $citys = [];

    public $towns = [];

    public $s_towns;

    public $s_citys;

    public $zips;

    public $error;

    public $_user;

    public $id;

    public $name;

    public $name2;

    public $read_it;

    public $read2_it;

    public $sex = 0;

    public $pass;

    public $entering;

    public $birthday;

    public $email;

    public $zip;

    public $_zip;

    public $pref;

    public $_pref;

    public $address;

    public $_address;

    public $tel;

    public $fax;

    public $mobile;

    public $point;

    public $magazine;

    //var $st_zoon;

    //class object constructor

    public function __construct($sessions, $sess_ip)
    {
        global $_SERVER, $xoopsModule, $xoopsUser, $xoopsTpl, $xoopsConfig;

        parent::__construct();

        $this->prefecture = &$this->cart->get_prefecture_list();

        $this->sessions = $sessions;

        $this->sess_ip = $sess_ip;

        if (empty($this->db) || !is_object($this->db)) {
            $this->db = XoopsDatabaseFactory::getDatabaseConnection();
        }

        if (empty($this->myts) || !is_object($this->myts)) {
            $this->myts = MyTextSanitizer::getInstance();
        }

        $this->uid = ($xoopsUser && is_object($xoopsUser)) ? $xoopsUser->uid() : 0;

        mt_srand((float)microtime() * 100000);

        if (mt_rand(0, 1000) <= 10) {
            $this->u_cart_sessions_gc();
        }

        $this->notation = $this->cart->get_notation();

        $_bank = str_replace([' ', '-'], "\n", $this->notation['bank']);

        $bank = explode(',', $_bank);

        $this->notation['bank'] = array_filter(
            $bank,
            create_function(
                '$b',
                'if ($b == "") { return false;}	return true;'
            )
        );

        $this->notation['bank'] = implode("\n", $this->notation['bank']);

        $sql = 'SELECT count(zid) FROM ' . $this->db->prefix('zipcode');

        $result = $this->db->fetchRow($this->db->query($sql));

        if (!$result || 0 == $result[0]) {
            $this->zipcode = false;
        } elseif ($result[0] > 0) {
            $this->zipcode = true;
        }

        if (isset($_SERVER['SSL_PROTOCOL'])) {
            $this->ssl_mode = true;
        }

        $this->cargo = new transporter();

        $this->transporter = $this->cargo->_transporter();

        $this->transporter_name = $this->cargo->_transporter_name();
    }

    public function u_cart_buyuser_sanitiz($arr)
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
            $arr
        );

        $n_post = array_filter(
            $n_post,
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
			}'
            )
        );

        if (isset($n_post['birthday_y']) && '--' != $n_post['birthday_y']) {
            $n_post['birthday'] = implode('-', [$n_post['birthday_y'], $n_post['birthday_m'], $n_post['birthday_d']]);

            $this->birthday = $n_post['birthday'];
        }

        unset($n_post['birthday_y']);

        unset($n_post['birthday_m']);

        unset($n_post['birthday_d']);

        unset($n_post['citytown']);

        unset($n_post['submit']);

        unset($n_post['zipaddress']);

        unset($n_post['addresscity']);

        if (isset($n_post['zip'])) {
            $n_post['zip'] = $this->u_cart_convert_alnum($n_post['zip']);
        }

        if ($n_post) {
            return $n_post;
        }

        return false;
    }

    public function u_cart_buyuser_DB_passwd($n_post)
    {
        $res = false;

        $result = false;

        $tel = '';

        $email = '';

        if (isset($n_post['tel'])) {
            $tel = $this->u_cart_convert_alnum($n_post['tel']);
        }

        if (isset($n_post['email'])) {
            $email = mb_convert_kana($n_post['email'], 'a');
        }

        if ($tel && $email) {
            $result = $this->u_cart_uid2buyuser(7, $tel, $email);

            if ($result && $result['pass'] == md5($n_post['pass'])) {
                return $this->u_cart_input_form();
            } elseif ($result && $result['pass'] != md5($n_post['pass'])) {
                $this->error = _UUCART_USER_EX . '<br>' . _UUCART_MAIL;

                return $this->u_cart_input_form($n_post);
            }  

            $this->error = _UUCART_USER_EX1;

            return $this->u_cart_input_form($n_post);
        } elseif ($tel) {
            $res = $this->u_cart_uid2buyuser(2, md5($n_post['pass']), $tel);
        } elseif ($email) {
            $res = $this->u_cart_uid2buyuser(3, md5($n_post['pass']), $email);
        }

        if ($res) {
            if ($res) {
                return $this->u_cart_input_form();
            }
        } else {
            $this->error = _UUCART_USER_EX1;

            return $this->u_cart_input_form($n_post);
        }
    }

    public function u_cart_convert_alnum($str)
    {
        return preg_replace('/[^0-9]/i', '', mb_convert_kana($str, 'a'));
    }

    public function u_cart_buyuser_DB_FalsenessSet($n_post, $mod = false)
    {
        $id = '';

        if (isset($n_post['id'])) {
            $id = $n_post['id'];

            unset($n_post['id']);
        } else {
            $result = $this->u_cart_uid2buyuser(6);

            if ($result) {
                $id = $this->id;
            }
        }

        if (isset($n_post['s_citys'])) {
            unset($n_post['s_citys']);
        }

        if (isset($n_post['s_towns'])) {
            unset($n_post['s_towns']);
        }

        $n_post['name'] = mb_convert_kana($n_post['name'], 'KHVS');

        if (isset($n_post['read_it'])) {
            $n_post['read_it'] = mb_convert_kana($n_post['read_it'], 'KHVS');
        }

        $n_post['email'] = mb_convert_kana($n_post['email'], 'a');

        $n_post['tel'] = $this->u_cart_convert_alnum($n_post['tel']);

        $n_post['zip'] = $this->u_cart_convert_alnum($n_post['zip']);

        if (!$id && isset($n_post['pass'])) {
            $result = $this->u_cart_uid2buyuser(7, $n_post['tel'], $n_post['email']);

            if ($result && $result['pass'] == md5($n_post['pass'])) {
                $id = $this->id;
            }
        }

        if (isset($n_post['pass'])) {
            $n_post['pass'] = md5($n_post['pass']);
        }

        if (isset($n_post['fax'])) {
            $n_post['fax'] = $this->u_cart_convert_alnum($n_post['fax']);
        }

        if (isset($n_post['mobile'])) {
            $n_post['mobile'] = $this->u_cart_convert_alnum($n_post['mobile']);
        }

        $n_post['uid'] = $this->uid;

        $n_post['sess_id'] = $this->sessions;

        if ($id) {
            $column = $this->cart->sql_parse_updare($n_post);

            $sql = 'UPDATE ' . $this->db->prefix('buy_user') . ' SET ' . $column . ', l_time = NOW() WHERE id = ' . $id;
        } else {
            $column = $this->cart->sql_parse_insert($n_post);

            $sql = 'INSERT INTO ' . $this->db->prefix('buy_user') . ' (' . implode(',', $column['key']) . ', entering, l_time) VALUES(' . implode(',', $column['val']) . ', NOW(), NOW())';
        }

        return $this->cart->result_sql($sql);
    }

    public function u_cart_buyuser_DB_FalsenessRead($n_post)
    {
        global $_SERVER;

        if ((isset($n_post['tel']) && $this->u_cart_uid2buyuser(4, 'dummy', $n_post['tel']))
            || (isset($n_post['email']) && $this->u_cart_uid2buyuser(5, 'dummy', $n_post['email']))) {
            return $this->u_cart_input_form();
        }

        if ((isset($n_post['email']) && isset($n_post['pass']) && $this->u_cart_uid2buyuser(3, $n_post['pass'], $n_post['email']))
            || (isset($n_post['tel']) && isset($n_post['pass']) && $this->u_cart_uid2buyuser(2, $n_post['pass'], $n_post['tel']))) {
            return $this->u_cart_input_form();
        }

        if ($this->sess_ip == $_SERVER['REMOTE_ADDR'] && $this->u_cart_uid2buyuser(6)) {
            return $this->u_cart_input_form();
        }

        return false;
    }

    public function u_cart_uid2buyuser($type, $pass = null, $str = null)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('buy_user') . ' as t ';

        if (2 == $type && isset($str)) {
            $sql1 = "WHERE pass = '" . $pass . "' AND tel = '" . $str . "'";
        } elseif (1 == $type) {
            $sql1 = 'WHERE uid = ' . $this->uid;
        } elseif (3 == $type && isset($str)) {
            $sql1 = "WHERE pass = '" . $pass . "' AND email = '" . $str . "'";
        } elseif (4 == $type && isset($str)) {
            $sql1 = "WHERE tel = '" . $str . "'";
        } elseif (5 == $type && isset($str)) {
            $sql1 = "WHERE email = '" . $str . "'";
        } elseif (6 == $type) {
            $sql1 = "WHERE sess_id = '" . $this->sessions . "'";
        } elseif (7 == $type && isset($str)) {
            $sql1 = "WHERE tel = '" . $pass . "' OR email = '" . $str . "'";
        } elseif (8 == $type) {
            $sql1 = "WHERE sess_id = '" . $this->sessions . "'";
        } elseif (9 == $type) {
            $sql1 = 'LEFT OUTER JOIN ' . $this->db->prefix('buy_table') . " as tt USING(id) WHERE tt.sessions = '" . $this->sessions . "' AND tt.falseness = 'f'";
        } elseif (10 == $type) {
            $sql1 = 'WHERE id = ' . $pass;
        } elseif ('check' == $type) {
            $sql1 = 'LEFT OUTER JOIN ' . $this->db->prefix('buy_table') . " as tt USING(id) WHERE tt.falseness = 't' AND tt.uniq = '" . $pass . "'";
        }

        $sql .= $sql1;

        $result = $this->cart->_result($sql, 1, 0);

        if ($result && $type < 9) {
            foreach ($result as $key => $val) {
                $this->{$key} = $this->myts->htmlSpecialChars($val);
            }

            return true;
        } elseif ($result && $type > 8) {
            $this->_user = $result;

            return true;
        }

        return false;
    }

    public function u_cart_addressee_form($src)
    {
    }

    public function u_cart_numbers_format()
    {
        if ($this->zip) {
            $this->zip = zip_format_check($this->u_cart_convert_alnum($this->zip));
        }

        if ($this->tel) {
            $this->tel = tel_format_check($this->u_cart_convert_alnum($this->tel));
        }

        if ($this->fax) {
            $this->fax = tel_format_check($this->u_cart_convert_alnum($this->fax));
        }

        if ($this->mobile) {
            $this->mobile = tel_format_check($this->u_cart_convert_alnum($this->mobile));
        }
    }

    public function u_cart_input_form($n_post = false, $zip = false)
    {
        $res = false;

        if ($this->uid > 0) {
            $res = $this->u_cart_uid2buyuser(1);
        }

        if (true === $zip) {
            $this->zip = zip_format_check($this->_zip);

            $this->address = $this->_address;

            //$this->pref = $this->prefecture[$this->_pref];
        }

        if (!$res && $n_post) {
            $this->u_cart_post2this($n_post);
        } elseif (!$res) {
            $this->u_cart_uid2buyuser(6);
        }

        $this->u_cart_numbers_format();

        $form = new XoopsThemeForm(_UUCART_USER, 'buyuser', 'purchase.php', 'POST');

        $form->addElement(new XoopsFormLabel(_UUCART_MESSAGE_PLEASE, _UUCART_COLOR_MESSAGE));

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_NAME, 'name', 30, 40, $this->name));

        $form->addElement(new XoopsFormText(_UUCART_USER_READ, 'read_it', 30, 40, $this->read_it));

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_MAIL, 'email', 50, 100, $this->email));

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_TEL, 'tel', 30, 30, $this->tel));

        $passtray = new XoopsFormElementTray(_UUCART_USER_PASS_INPUT, '&nbsp;');

        $passbox = new XoopsFormPassword('', 'pass', 10, 15);

        $passtray->addElement($passbox);

        $passsend = new XoopsFormButton('', 'passsend', _UUCART_USER_PASS_SUBMIT, 'submit');

        $passtray->addElement($passsend);

        $form->addElement($passtray);

        $form->addElement(new XoopsFormText(_UUCART_USER_FAX, 'fax', 30, 30, $this->fax));

        $form->addElement(new XoopsFormText(_UUCART_USER_MOBILE, 'mobile', 30, 30, $this->mobile));

        if (true === $this->zipcode) {
            $ziptray = new XoopsFormElementTray(_UUCART_MARK . _UUCART_USER_ZIP, '&nbsp;');

            $ziptray->addElement(new XoopsFormText('', 'zip', 15, 20, $this->zip));

            if ($this->zips) {
                $formzips = new XoopsFormSelect('', 's_zips', '--');

                $formzips->addOptionArray($this->zips);

                $formzips->setExtra('onchange="add_zips()"');

                $ziptray->addElement($formzips);
            } else {
                $ziptray->addElement(new XoopsFormButton('', 'zipaddress', _UUCART_USER_ZIP2ADDRESS, 'submit'));
            }

            $form->addElement($ziptray);

            $addtray = new XoopsFormElementTray(_UUCART_MARK . _UUCART_USER_PREF, '&nbsp;');

            $formpref = new XoopsFormSelect('', 'pref', $this->pref);

            $formpref->addOptionArray($this->prefecture);

            $addtray->addElement($formpref);

            if ($this->citys) {
                $formcity = new XoopsFormSelect('', 's_citys', $this->s_citys);

                $formcity->addOptionArray($this->citys);

                $formcity->setExtra('onchange="add_city()"');

                $addtray->addElement($formcity);

                if ($this->towns) {
                    $formtown = new XoopsFormSelect('', 's_towns', $this->s_towns);

                    $formtown->addOptionArray($this->towns);

                    $formtown->setExtra('onchange="add_town()"');

                    $addtray->addElement($formtown);

                    $addtray->addElement(new XoopsFormButton('', 'addtozip', _UUCART_USER_ADD2ZIP, 'submit'));
                } else {
                    $addtray->addElement(new XoopsFormButton('', 'citytown', _UUCART_USER_CITY2ZIP, 'submit'));
                }
            } else {
                $addtray->addElement(new XoopsFormButton('', 'addresscity', _UUCART_USER_ADDRESS2ZIP, 'submit'));
            }
        } else {
            $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_ZIP, 'zip', 15, 20, $this->zip));

            $addtray = new XoopsFormText(_UUCART_MARK . _UUCART_USER_CITY, 'address', 60, 200, $this->address);

            $addtray = new XoopsFormSelect(_UUCART_MARK . _UUCART_USER_PREF, 'pref', $this->pref);

            $addtray->addOptionArray($this->prefecture);
        }

        $form->addElement($addtray);

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_CITY, 'address', 70, 300, $this->address));

        $sex = new XoopsFormRadio(_UUCART_USER_SEX, 'sex', $this->sex);

        $sex->addOptionArray([0 => _UUCART_USER_MAN, 1 => _UUCART_USER_WOMAN]);

        $form->addElement($sex);

        if ($this->birthday) {
            $form->addElement(new XoopsFormHidden('birthday', $this->birthday));
        }

        $form->addElement(new MyFormDateTime(_UUCART_USER_BIRTHDAY, 'birthday', 15, $this->birthday));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_PASS, _UUCART_USER_PMESS));

        if ($res && isset($this->id)) {
            $form->addElement(new XoopsFormHidden('id', $this->id));
        }

        $form->addElement(new XoopsFormButton(_UUCART_USER_POST_NEXT, 'submit', _UUCART_USER_NEXT, 'submit'));

        return $form->render();
    }

    public function u_cart_session2buy($_gid = null, $_bid = null)
    {
        $src = [];

        //Enough functions that confirm the falsification of the GET

        if ($_bid) {
            $arr1 = unserialize(base64_decode($_bid, true));

            if (!$arr1) {
                return _UUCART_USER_ERROR;
            }

            foreach ($arr1 as $val) {
                foreach ($val as $k => $v) {
                    ${$k}[] = $v;
                }
            }

            if (!$bid) {
                return _UUCART_USER_ERROR;
            }
        }

        $src = $this->u_cart_disp_Basket($next = 1);

        $src['map_url'] = $this->url . 'index.php?ucart=view_basket';

        if (1 == $this->notation['usessl']) {
            $src = array_map(
                create_function('$val', 'return preg_replace("/http/i", "https", $val);'),
                $src
            );
        }

        return $src;
    }

    public function u_cart_buy_sessions($gid, $stock)
    {
        $item_sum = [];

        $existence = $this->u_cart_sessions_existence_check($gid);

        if ($existence) {
            $sql = 'UPDATE ' . $this->db->prefix('cart_sessions') . ' SET buy = buy+' . $stock . ' WHERE bid = ' . $existence['bid'];
        } else {
            $sql = 'INSERT INTO ' . $this->db->prefix('cart_sessions') . " (sessions, id, buy, gid, sess_ip, times) VALUES('$this->sessions', $this->uid, $stock, $gid, '$this->sess_ip', NOW())";
        }

        $result = $this->cart->result_sql($sql);

        $sql = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock-' . $stock . ' WHERE gid = ' . $gid;

        $result = $this->cart->result_sql($sql);

        return $result;
    }

    public function u_cart_alldelete_sessions($_gid, $_bid)
    {
        $arr1 = unserialize(base64_decode($_bid, true));

        foreach ($arr1 as $val) {
            foreach ($val as $k => $v) {
                ${$k}[] = $v;
            }
        }

        $loop = count($bid);

        for ($i = 0; $i < $loop; $i++) {
            $sql = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . ' WHERE bid = ' . $bid[$i];

            $result = $this->cart->result_sql($sql);
        }

        for ($i = 0; $i < $loop; $i++) {
            $sql = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock+' . $buy[$i] . ' WHERE gid = ' . $gid[$i];

            $result = $this->cart->result_sql($sql);
        }
    }

    public function u_cart_order_decision($n_post)
    {
        global $xoopsConfig;

        $result = [];

        $sql = [];

        $msent = [];

        $id = (int)$n_post['id'];

        $op = [];

        unset($n_post['id']);

        for ($i = 0, $iMax = count($n_post['gid']); $i < $iMax; $i++) {
            $sql[] = 'UPDATE ' . $this->db->prefix('goods') . ' SET rank = rank+' . (int)$n_post['num'][$i] . ' WHERE gid = ' . (int)$n_post['gid'][$i];

            $sql[] = 'UPDATE ' . $this->db->prefix('buy_table') . " SET id = '" . $id . "', falseness = 't' WHERE buy = " . (int)$n_post['buy'][$i];

            $op[] = "t.buy = '" . $n_post['buy'][$i] . "'";

            $msent[] = 'UPDATE ' . $this->db->prefix('buy_table') . " SET msend = '1' WHERE buy = " . (int)$n_post['buy'][$i];
        }

        $sql[] = 'UPDATE ' . $this->db->prefix('buy_user') . " SET point = point+1, falseness = 't',sess_id = '" . $this->sessions . "' WHERE id = " . (int)$id;

        $sql[] = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . " WHERE sessions = '" . $this->sessions . "' AND sess_ip = '" . $this->sess_ip . "'";

        for ($i = 0, $iMax = count($sql); $i < $iMax; $i++) {
            $result[] = $this->cart->result_sql($sql[$i]);
        }

        $c = 0;

        $option = '(' . implode(' OR ', $op) . ') AND t.msend = 0 ORDER BY tt.times DESC';

        $buy_table = $this->u_cart_id2addressee($id, 'uniq', $option);

        $useruniq = $buy_table[0]['uniq'];

        $new_buy = [];

        $shipping = 0;

        foreach ($buy_table as $val) {
            if ($c == $val['buy']) {
                continue;
            }

            $shipping += $val['postage'] * $val['packages'];

            $UU_S['UU_S_USER_NAME'] = $val['name'];

            $UU_S['UU_S_ZIP'] = $val['zip'];

            $new_buy['gid'][] = $val['gid'];

            $new_buy['num'][$val['gid']] = $val['num'];

            $new_buy['postage'][$val['gid']] = $val['postage'];

            $new_buy['packages'][$val['gid']] = $val['packages'];

            $c = $val['buy'];
        }

        $goods = $this->cart->get_goods_plural($new_buy['gid']);

        $goodsname = [];

        $sub_total = 0;

        $_cool = 0;

        //$is_cool	= 0;

        foreach ($goods as $val) {
            $is_cool = 0;

            if ('t' == $val['cool']) {
                $is_cool = transporter::_cool($val['length']);
            }

            $goodsname[] = _UUCART_MAIL00
                           . $val['top']
                           . "\n"
                           . sprintf(_UCART_PRICE, $val['price'])
                           . ' - '
                           . _UCART_ITEM_NUM
                           . ' '
                           . $new_buy['num'][$val['gid']]
                           . ' - '
                           . _UUCART_CARRIAGECOST
                           . ' '
                           . $new_buy['postage'][$val['gid']]
                           . _UCART_ITEM_YEN2
                           . ' - '
                           . sprintf(_UUCART_ISCOOL, $is_cool)
                           . "\n"
                           . _UUCART_MAIL01
                           . $val['sub'];

            $sub_total += $val['price'] * $new_buy['num'][$val['gid']];

            $_cool += $is_cool * $new_buy['packages'][$val['gid']];
        }

        $cool = ($_cool > 0) ? sprintf(_UUCART_MAIL02, $_cool) : sprintf(_UUCART_MAIL02, '----');

        $gname = implode("\n", $goodsname);

        $user = [];

        $buy_user = $this->cart->_buy_user($id);

        array_walk($buy_user, 'myts_hSC_call_back', $user);

        $assign = [
            'UU_USER_NAME' => $buy_user['name'],
            'UU_TODAY' => date('Y-m-d H:i', time()),
            'UU_USER_EMAIL' => $buy_user['email'],
            'UU_S_USER_NAME' => $UU_S['UU_S_USER_NAME'],
            'UU_GOODS' => $gname,
            'UU_BUYING_VALUE' => number_format($sub_total),
            'UU_SIP_COOL' => $cool,
            'UU_S_USER_NAME' => $UU_S['UU_S_USER_NAME'],
            'UU_S_ZIP' => $UU_S['UU_S_ZIP'],
            'UU_S_ADD' => _UUCART_OMISSION,
            'UU_S_TEL' => _UUCART_OMISSION,
            'UU_PAY_TYPE' => _UUCART_PAY_TYPE,
            'CHECKURL' => $this->url . 'purchase2.php?user=check&buy=' . $useruniq,
            'UU_BANK' => $this->notation['bank'],
            'UU_SIP_VAL' => number_format($shipping),
            'UU_SHIPPING' => number_format($shipping + $sub_total),
            'UU_ALL_PAY' => number_format($shipping + $sub_total + $_cool),
        ];

        u_cart_Mailer('sales_notify.tpl', $assign, $buy_user['email'], sprintf(_UUCART_SUBJECT00, $xoopsConfig['sitename']));

        for ($i = 0, $iMax = count($msent); $i < $iMax; $i++) {
            $result[] = $this->cart->result_sql($msent[$i]);
        }

        return $buy_user;
    }

    public function u_cart_delete_sessions($gid, $str)
    {
        $sesdat = $this->u_cart_sessions_existence();

        $stock = 0;

        foreach ($sesdat as $val) {
            if ($val['gid'] != $gid) {
                continue;
            }

            $sql = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . ' WHERE bid = ' . $val['bid'];

            $result = $this->cart->result_sql($sql);

            $stock += $val['buy'];
        }

        $sql2 = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock+' . $stock . ' WHERE gid = ' . $gid;

        $result = $this->cart->result_sql($sql2);
    }

    public function u_cart_edit_sessions($gid, $str, $diff, $flg)
    {
        $sesdat = $this->u_cart_sessions_existence();

        $dummy = 0;

        $sql2 = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock+' . $diff . ' WHERE gid = ' . $gid;

        foreach ($sesdat as $val) {
            if ($val['gid'] != $gid) {
                continue;
            }

            if (true === $flg) {
                $sql1 = 'UPDATE ' . $this->db->prefix('cart_sessions') . ' SET buy = buy+' . $diff . ' WHERE bid = ' . $val['bid'];

                $sql2 = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock-' . $diff . ' WHERE gid = ' . $gid;

                break;
            }

            $dummy = $val['buy'] - $diff;

            if ($dummy > 0) {
                $sql1 = 'UPDATE ' . $this->db->prefix('cart_sessions') . ' SET buy = buy-' . $diff . ' WHERE bid = ' . $val['bid'];

                break;
            } elseif ($dummy <= 0) {
                $sql = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . ' WHERE bid = ' . $val['bid'];

                $result = $this->cart->result_sql($sql);
            }
        }

        $result = $this->cart->result_sql($sql1);

        $result = $this->cart->result_sql($sql2);
    }

    public function u_cart_disp_Basket($next = 0, $map = '')
    {
        $dat = $this->u_cart_sessions_existence();

        $hidden = '';

        $hidden1 = '';

        $sub_total = 0;

        if ($dat) {
            foreach ($dat as $val) {
                if (!isset($item_sum[$val['gid']])) {
                    $item_sum[$val['gid']] = 0;
                }

                $item_sum[$val['gid']] += $val['buy'];

                $hidden_dat[]['bid'] = $val['bid'];

                $hidden_dat[]['gid'] = $val['gid'];

                $hidden_dat[]['buy'] = $val['buy'];
            }

            $hidden = base64_encode(serialize($hidden_dat));

            $hidden1 = base64_encode(serialize($item_sum));

            $dis = [];

            $src = [];

            $i = 0;

            foreach ($item_sum as $key => $val) {
                $src = $this->make_goods_detail($key);

                $hidd = base64_encode(serialize([$key, $val]));

                $src['no'] = sprintf('%04d', $src['gcid']) . '-' . sprintf('%06d', $src['gid']);

                $src['linetotal'] = number_format($val * $src['price']);

                $sub_total += $val * $src['price'];

                $src['price'] = number_format($src['price']) . '<br>' . _UCART_ITEM_TAX;

                $src['formname'] = str_replace('shopping', 'shopping' . $i, $src['formname']);

                $src['purchase'] = '<a href="#" onclick="return sendedit(document.shopping' . $i . ')">' . _UCART_MOD_IMG . '</a>';

                $src['deletekine'] = '<a href="#" onclick="return senddelete(document.shopping' . $i . ')">' . _UCART_DEL_IMG . '</a>';

                $src['hidden1'] = '<input type="hidden" name="bid" value="' . $hidd . '">';

                $src['hidden3'] = '<input type="hidden" name="buy_user" value="' . $val . '">';

                $src['buy_user'] = $val;

                $dis[] = $src;

                $i++;
            }
        } else {
            $dis = ['mes' => _UUCART_BK_NULL, 'flg' => 'null'];
        }

        $sub_total = number_format($sub_total);

        $delete_basket = '<form method="POST" style="text-align : center;" name="alldel">'
                         . _UCART_CLEAR_BASKET
                         . '<a href="#" onclick="return send(document.alldel)">'
                         . _UCART_CLEAR_IMG
                         . '</a><input type="hidden" name="bid" value="'
                         . $hidden
                         . '"><input type="hidden" name="gid" value="'
                         . $hidden1
                         . '"></form>';

        if (1 == $this->notation['usessl']) {
            $url = str_replace('http', 'https', $this->url);
        }//a href="javascript:

        $continue_uu = '<form method="GET" name="basket" action="'
                       . $url
                       . 'purchase.php"><input type="image" src="'
                       . _UCART_OKSUBMIT_IMG
                       . '" alt="'
                       . _UUCART_PURCH
                       . '" onClick="sendok(document.basket)" /"><input type="hidden" name="gid" value="'
                       . $hidden1
                       . '"><input type="hidden" name="bid" value="'
                       . $hidden
                       . '"><input type="hidden" name="ucart" value="basketto"></form>';

        if (!$map) {
            $map = '<map name="course">
	<area shape="default" nohref>
</map>';
        }

        if (0 == $next) {
            $this->u_cart_buy_detail2Tpl(
                $dis,
                [
                    'delete_basket' => $delete_basket,
                    'continue_uu' => $continue_uu,
                    'sub_total' => $sub_total,
                    'course' => _UCART_COURSE1_IMG,
                    'map' => $map,
                ]
            );
        } else {
            $dis['sub_total'] = $sub_total;

            return $dis;
        }
    }

    public function u_cart_sessions_order_status($uniq = null)
    {
        $dat = [];

        if ($uniq) {
            $res = $this->cart->_order_inquiry($uniq);
        } else {
            $res = $this->cart->_order_status($this->sessions);
        }

        if (!$res) {
            return false;
        }

        $t1_total = 0;

        $t2_total = 0;

        foreach ($res as $val) {
            $val['_cool'] = (1 == $val['cool']) ? transporter::_cool($val['length']) : '----';

            $src = $this->make_goods_detail($val['gid']);

            $t1_total += $val['num'] * $val['price'];

            $t2_total += $val['postage'] * $val['packages'];

            $val['t2'] = (is_int($val['_cool'])) ? number_format($val['postage'] * $val['packages'] + $val['_cool']) : number_format($val['postage'] * $val['packages']);

            $val['t1'] = number_format($val['num'] * $val['price']);

            $val['price'] = number_format($val['price']);

            $dat[] = array_merge($val, $src);
        }

        $dat['t1_total'] = $t1_total;

        $dat['t2_total'] = $t2_total;

        return $dat;
    }

    public function u_cart_buy_detail2Tpl($src, $arr = '')
    {
        $this->Tpl->assign('goods', $src);

        if ($arr) {
            $this->Tpl->assign('other', $arr);
        }
    }

    public function u_cart_buy_addressee($uniq = false)
    {
        if (!$uniq) {
            $op = " WHERE sessions = '" . $this->sessions . "'";
        } else {
            $op = " WHERE uniq = '" . $uniq . "'";
        }

        $sql = 'SELECT * FROM ' . $this->db->prefix('buy_addressee') . $op;

        return $this->cart->_result($sql);
    }

    public function u_cart_id2addressee($id, $using = 'sessions', $op = '')
    {
        if ('' == $op) {
            $op = 't.id = ' . $id;
        }

        $sql = 'SELECT *,(t.packages*t.postage) as shipping FROM ' . $this->db->prefix('buy_table') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('buy_addressee') . ' as tt USING(' . $using . ') WHERE ' . $op;

        return $this->cart->_result_rows($sql);
    }

    public function u_cart_sessions_existence()
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('cart_sessions') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('goods') . " as tt USING(gid) WHERE t.sessions = '" . $this->sessions . "' AND t.sess_ip = '" . $this->sess_ip . "'";

        return $this->cart->_result_rows($sql);
    }

    public function u_cart_sessions_existence_check($gid)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('cart_sessions') . " WHERE sessions = '" . $this->sessions . "' AND sess_ip = '" . $this->sess_ip . "' AND gid = " . $gid;

        return $this->cart->_result($sql);
    }

    public function u_cart_sessions_gc()
    {
        //$today = date('Y-m-d H:i:s', time()-86400);

        $sql = 'SELECT gid,buy FROM ' . $this->db->prefix('cart_sessions') . ' WHERE times < DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)';

        $sqldel = 'DELETE FROM ' . $this->db->prefix('cart_sessions') . ' WHERE times < DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)';

        $result = $this->cart->_result($sql);

        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $sql = 'UPDATE ' . $this->db->prefix('goods') . ' SET stock = stock+' . (int)$row['buy'] . ' WHERE gid = ' . (int)$row['gid'];

                $this->cart->result_sql($sql);
            }

            //return $this->cart->result_sql($sqldel);
            //return $this->pickup_row;
        }

        return $this->cart->result_sql($sqldel);
    }

    public function u_cart_zip2address($n_post)
    {
        $this->u_cart_post2this($n_post);

        if (isset($n_post['zip'])) {
            $this->_zip = $this->u_cart_convert_alnum($n_post['zip']);

            $sql = 'SELECT zip, pref, city, town FROM ' . $this->db->prefix('zipcode') . " WHERE zip = '" . $this->_zip . "'";

            $result = $this->db->fetchRow($this->db->query($sql));

            if ($result) {
                [, $this->_pref, $city, $town] = $result;

                $this->_address = $this->_pref . $city . $town;
            }

            return $this->u_cart_input_form($n_post = false, $zip = true);
        }

        return $this->u_cart_input_form();
    }

    public function u_cart_pref2city($n_post)
    {
        $this->u_cart_post2this($n_post);

        $this->_zip = $this->u_cart_convert_alnum($n_post['zip']);

        if (isset($this->pref)) {
            $this->_pref = $this->prefecture[$this->pref];

            $this->_address = $this->_pref;

            $this->u_cart_make_city($this->_pref);
        }

        return $this->u_cart_input_form($n_post = false, $zip = true);
    }

    public function u_cart_city2town($n_post)
    {
        $this->u_cart_post2this($n_post);

        $this->_zip = $this->u_cart_convert_alnum($n_post['zip']);

        if (isset($this->s_citys)) {
            $this->_pref = $this->prefecture[$this->pref];

            $this->u_cart_make_city($this->_pref);

            if ('--' == $this->s_citys) {
                $this->s_citys = '';
            } else {
                $this->u_cart_make_town($this->s_citys);
            }

            $this->_address = $this->_pref . $this->s_citys;
        }

        return $this->u_cart_input_form($n_post = false, $zip = true);
    }

    public function u_cart_add2zip($n_post)
    {
        $this->u_cart_post2this($n_post);

        $this->_zip = $this->u_cart_convert_alnum($n_post['zip']);

        if (isset($this->s_towns)) {
            $this->_pref = $this->prefecture[$this->pref];

            $this->u_cart_search_zips($this->_pref);

            $this->_address = $this->_pref . $this->s_citys . $this->s_towns;
        }

        return $this->u_cart_input_form($n_post = false, $zip = true);
    }

    public function u_cart_search_zips($pref)
    {
        $sql = 'SELECT zip FROM ' . $this->db->prefix('zipcode') . " WHERE pref = '" . $pref . "' AND city = '" . $this->s_citys . "' AND town = '" . $this->s_towns . "'";

        $result = $this->cart->_result_rows($sql);

        if ($result && 1 == count($result)) {
            $this->_zip = $result[0]['zip'];
        } else {
            $this->zips['--'] = '--';

            foreach ($result as $val) {
                $this->zips[$val['zip']] = $val['zip'];
            }
        }

        $this->_address = $pref . $this->s_citys . $this->s_towns;

        $this->u_cart_make_city($pref);

        $this->u_cart_make_town($this->s_citys);
    }

    public function u_cart_make_city($pref)
    {
        $sql = 'SELECT city FROM ' . $this->db->prefix('zipcode') . " WHERE pref = '" . $pref . "' GROUP BY city";

        $result = $this->cart->_result_rows($sql);

        if ($result) {
            $this->citys['--'] = '--';

            foreach ($result as $val) {
                $this->citys[$val['city']] = $val['city'];
            }
        }
    }

    public function u_cart_make_town($city)
    {
        $sql = 'SELECT town FROM ' . $this->db->prefix('zipcode') . " WHERE city = '" . $this->s_citys . "' GROUP BY town";

        $result = $this->cart->_result_rows($sql);

        if ($result) {
            $this->towns['--'] = '--';

            foreach ($result as $val) {
                $this->towns[$val['town']] = $val['town'];
            }
        }
    }

    public function u_cart_post2this($n_post)
    {
        $n_post = $this->u_cart_buyuser_sanitiz($n_post);

        if (is_array($n_post)) {
            foreach ($n_post as $key => $val) {
                $this->{$key} = $this->cart->myts->htmlSpecialChars($val);
            }
        }
    }

    public function u_cart_get_err()
    {
        return $this->error;
    }

    public function _get_user()
    {
        return $this->_user;
    }
}

//Particularly I am not entering the completion tag
