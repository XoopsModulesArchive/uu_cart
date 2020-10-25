<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/function.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/cart.session.class.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/transporter.class.php';

class u_cart_transporter extends u_cart_session
{
    public $transporter;

    public $transporter_name;

    public $citys = [];

    public $towns = [];

    public $s_citys;

    public $zips;

    public $error;

    public $s_towns;

    public $s_city;

    public $sessions;

    public $sess_ip;

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

    public $pref;

    public $address;

    public $tel;

    public $fax;

    public $mobile;

    public $point;

    public $magazine;

    public $st_zoon;

    //class object constructor

    public function __construct($sessions, $sess_ip)
    {
        global $_SERVER, $xoopsModule, $xoopsUser, $xoopsTpl, $xoopsConfig;

        u_cart_session::__construct($sessions, $sess_ip);
    }

    public function u_cart_session2DB($n_post)
    {
        if (!isset($n_post['gid'])) {
            redirect_header('index.php?ucart=view_basket', 3, _UCART_NO_GID);

            exit;
        }

        $buy_tb = [];

        $buy_add = [];

        //$carriage	= array();

        $nums = [];

        $goods = [];

        $packages = 0;

        $allnums = 0;

        $sub_total = 0;

        $carriageall = 0;

        $uniq = md5(uniqid('buy'));

        $readit = $n_post['readit'] ?? '';

        for ($i = 0, $iMax = count($n_post['gid']); $i < $iMax; $i++) {
            $_goods = $this->u_cart_shipping($n_post['gid'][$i], $n_post['pref'], $n_post['weight'][$i], $n_post['length'][$i], $n_post['buy'][$i]);

            $packages += $_goods['packages'];

            $allnums += $n_post['buy'][$i];

            $sub_total += $_goods['linetotal_1'];

            $carriageall += $_goods['linetotal_2'];

            $goods[] = $_goods;

            $nums[$n_post['gid'][$i]] = $n_post['buy'][$i];

            $buy_tb[] = [
                'id' => $n_post['id'],
                'sessions' => $this->sessions,
                'uniq' => $uniq,
                'gid' => $n_post['gid'][$i],
                'num' => $n_post['buy'][$i],
                'transportid' => $this->transporter,
                'carriage' => $_goods['carri'],
                'cool' => $_goods['cools'],
                'packages' => $_goods['packages'],
                'postage' => $_goods['postage'],
            ];
        }

        $goods['transporter_name'] = $this->transporter_name;

        $goods['mypref'] = $this->prefecture[$this->notation['send_out']];

        $goods['customerpref'] = $this->prefecture[$n_post['pref']];

        $buy_add = [
            'sessions' => $this->sessions,
            'uniq' => $uniq,
            'name' => $n_post['name'],
            'readit' => $readit,
            'zip' => zip_format_check($n_post['zip']),
            'pref' => $n_post['pref'],
            'address' => $n_post['address'],
            'tel' => $n_post['tel'],
        ];

        if (isset($n_post['buy_id']) && isset($n_post['buy_addressee'])) {
            for ($i = 0, $iMax = count($n_post['gid']); $i < $iMax; $i++) {
                $_buy_table = $this->cart->sql_parse_updare($buy_tb[$i]);

                $sql[] = 'UPDATE ' . $this->db->prefix('buy_table') . ' SET ' . $_buy_table . ', times = NOW() WHERE buy = ' . (int)$n_post['buy_id'];
            }

            $_buy_add = $this->cart->sql_parse_updare($buy_add);

            $sql[] = 'UPDATE ' . $this->db->prefix('buy_addressee') . ' SET ' . $_buy_add . ', times = NOW() WHERE buy_addressee = ' . (int)$n_post['buy_addressee'];
        } else {
            for ($i = 0, $iMax = count($n_post['gid']); $i < $iMax; $i++) {
                $_buy_table = $this->cart->sql_parse_insert($buy_tb[$i]);

                $sql[] = 'INSERT INTO ' . $this->db->prefix('buy_table') . ' (' . implode(',', $_buy_table['key']) . ',times) VALUES(' . implode(',', $_buy_table['val']) . ',NOW())';
            }

            $_buy_add = $this->cart->sql_parse_insert($buy_add);

            $sql[] = 'INSERT INTO ' . $this->db->prefix('buy_addressee') . ' (' . implode(',', $_buy_add['key']) . ',times) VALUES(' . implode(',', $_buy_add['val']) . ',NOW())';
        }

        $goods['total_1'] = $sub_total;

        //Lite Delete

        $goods['total_2'] = (1 == $this->notation['carriagefree'] && $sub_total > $this->notation['freeval']) ? _UUCART_CARRIAGEFREE : $carriageall;

        $goods['packages'] = $packages;

        $goods['allnums'] = $allnums;

        //$goods['fax'] = '<a href="javascript:openFaxWindow(\'index.php?view=open_fax\',\'sup_open\',780,800);"><span style="font-size:13px;">'._UCART_FAX_IMG.'</span></a>';

        for ($i = 0, $iMax = count($sql); $i < $iMax; $i++) {
            $result = $this->cart->result_sql($sql[$i]);
        }

        return $goods;
    }

    public function u_cart_customer_form($type = false, $uniq = null)
    {
        if ('check' == $type) {
            $result = $this->u_cart_uid2buyuser('check', $uniq);

            if (!$result) {
                return false;
            }

            $sql = 'SELECT * FROM ' . $this->db->prefix('buy_table') . " WHERE uniq = '" . $uniq . "'";

            $check = $this->cart->_result_rows($sql);
        } else {
            $result = $this->u_cart_uid2buyuser(8);

            $sql = 'SELECT * FROM ' . $this->db->prefix('buy_table') . " WHERE sessions = '" . $this->sessions . "'";

            $check = $this->cart->_result_rows($sql);
        }

        $this->u_cart_numbers_format();

        $sex = ($this->sex = 1) ? _UUCART_USER_MAN : _UUCART_USER_WOMAN;

        $pref = $this->prefecture[$this->pref];

        $form = new XoopsThemeForm(_UUCART_USER_INFORMATION, 'customer', 'purchase.php', 'POST');

        $form->addElement(new XoopsFormLabel(_UUCART_USER_NAME, $this->name));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_READ, $this->read_it));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_MAIL, $this->email));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_TEL, $this->tel));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_FAX, $this->fax));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_MOBILE, $this->mobile));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_ZIP, $this->zip));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_PREF, $pref));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_CITY, $this->address));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_SEX, $sex));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_BIRTHDAY, $this->birthday));

        $form->addElement(new XoopsFormHidden('id', $this->id));

        $i = 0;

        foreach ($check as $val) {
            $form->addElement(new XoopsFormHidden('gid[' . $i . ']', $val['gid']));

            $form->addElement(new XoopsFormHidden('buy[' . $i . ']', $val['buy']));

            $form->addElement(new XoopsFormHidden('num[' . $i . ']', $val['num']));

            $i++;
        }

        if (!$type) {
            $form->addElement(new XoopsFormButton('', 'mod', _UUCART_MODIFY, 'submit'));

            $form->addElement(new XoopsFormButton('', 'decision', _UUCART_DECISION, 'submit'));
        }

        return $form->render();
    }

    public function u_cart_customer_addressee_form($buy = null)
    {
        $src = $this->u_cart_buy_addressee();

        if ($src['zip']) {
            $src['zip'] = zip_format_check($this->u_cart_convert_alnum($src['zip']));
        }

        if ($src['tel']) {
            $src['tel'] = tel_format_check($this->u_cart_convert_alnum($src['tel']));
        }

        $read_it = $src['read_it'] ?? '&nbsp;';

        $pref = $this->prefecture[$src['pref']];

        $form = new XoopsThemeForm(_UUCART_TO, 'custaddressee', 'purchase2.php', 'POST');

        $form->addElement(new XoopsFormLabel(_UUCART_USER_DESTINATION . _UUCART_USER_NAME, $src['name']));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_READ, $read_it));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_DESTINATION . _UUCART_USER_ZIP, $src['zip']));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_DESTINATION . _UUCART_USER_PREF, $pref));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_DESTINATION . _UUCART_USER_ADDRESS, $src['address']));

        $form->addElement(new XoopsFormLabel(_UUCART_USER_DESTINATION . _UUCART_USER_TEL, $src['tel']));

        $form->addElement(new XoopsFormHidden('buy_addressee', $src['buy_addressee']));

        if (!$buy) {
            $form->addElement(new XoopsFormButton('', 'addcheck', _UUCART_MODIFY, 'submit'));
        }

        return $form->render();
    }

    public function u_cart_addressee_form()
    {
        $result = $this->u_cart_uid2buyuser(6);

        $check = $this->u_cart_uid2buyuser(9);

        $addresse = $this->u_cart_buy_addressee();

        $src = $this->u_cart_sessions_existence();

        if (isset($this->_address)) {
            $this->address = $this->_address;
        }

        $this->u_cart_numbers_format();

        $form = new XoopsThemeForm(_UUCART_USER2, 'buyaddressee', 'purchase2.php', 'POST');

        $form->addElement(new XoopsFormLabel(_UUCART_MESSAGE_PLEASE, _UUCART_COLOR_MESSAGE));

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_DESTINATION . _UUCART_USER_NAME, 'name', 30, 40, $this->name), true);

        $form->addElement(new XoopsFormText(_UUCART_USER_READ, 'read_it', 30, 40, $this->read_it));

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_TEL, 'tel', 30, 30, $this->tel), true);

        if (true === $this->zipcode) {
            $ziptray = new XoopsFormElementTray(_UUCART_MARK . _UUCART_USER_ZIP, '&nbsp;');

            $ziptray->addElement(new XoopsFormText('', 'zip', 15, 20, $this->zip), true);

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
            $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_ZIP, 'zip', 15, 20, $this->zip), true);

            $addtray = new XoopsFormText(_UUCART_MARK . _UUCART_USER_CITY, 'address', 60, 200, $this->address);

            $addtray = new XoopsFormSelect(_UUCART_MARK . _UUCART_USER_PREF, 'pref', $this->pref);

            $addtray->addOptionArray($this->prefecture);
        }

        $form->addElement($addtray);

        $form->addElement(new XoopsFormText(_UUCART_MARK . _UUCART_USER_CITY, 'address', 70, 300, $this->address), true);

        if ($result && isset($this->id)) {
            $form->addElement(new XoopsFormHidden('id', $this->id));
        }

        $i = 0;

        foreach ($src as $val) {
            $form->addElement(new XoopsFormHidden('gid[' . $i . ']', $val['gid']));

            $form->addElement(new XoopsFormHidden('buy[' . $i . ']', $val['buy']));

            $form->addElement(new XoopsFormHidden('weight[' . $i . ']', $val['weight']));

            $form->addElement(new XoopsFormHidden('length[' . $i . ']', $val['length']));

            $form->addElement(new XoopsFormHidden('package[' . $i . ']', $val['package']));

            $i++;
        }

        $form->addElement(new XoopsFormHidden('buy_addressee', $addresse['buy_addressee']));

        $form->addElement(new XoopsFormHidden('buy_id', $this->_user['buy']));

        $form->addElement(new XoopsFormButton(_UUCART_USER_CHECK, 'pasubmit', _UCART_ITEM_SUBMIT, 'submit'));

        return $form->render();
    }

    public function u_cart_zip2address($n_post)
    {
        $this->u_cart_post2this($n_post);

        if (isset($n_post['zip'])) {
            $sql = 'SELECT zip, pref, city, town FROM ' . $this->db->prefix('zipcode') . " WHERE zip = '" . $n_post['zip'] . "'";

            $result = $this->db->fetchRow($this->db->query($sql));

            if ($result) {
                [$this->zip, $this->pref, $city, $town] = $result;

                $this->_address = $this->pref . $city . $town;
            }
        }

        return $this->u_cart_addressee_form();
    }

    public function u_cart_pref2city($n_post)
    {
        $this->u_cart_post2this($n_post);

        if (isset($this->pref)) {
            $pref = $this->prefecture[$this->pref];

            $this->_address = $pref;

            $this->u_cart_make_city($pref);
        }

        return $this->u_cart_addressee_form();
    }

    public function u_cart_city2town($n_post)
    {
        $this->u_cart_post2this($n_post);

        if (isset($this->s_citys)) {
            $pref = $this->prefecture[$this->pref];

            $this->u_cart_make_city($pref);

            if ('--' == $this->s_citys) {
                $this->s_citys = '';
            } else {
                $this->u_cart_make_town($this->s_citys);
            }

            $this->_address = $pref . $this->s_citys;
        }

        return $this->u_cart_addressee_form();
    }

    public function u_cart_add2zip($n_post)
    {
        $this->u_cart_post2this($n_post);

        if (isset($this->s_towns)) {
            $pref = $this->prefecture[$this->pref];

            $this->u_cart_search_zips($pref);

            $this->_address = $pref . $this->s_citys . $this->s_towns;
        }

        return $this->u_cart_addressee_form();
    }

    public function u_cart_search_zips($pref)
    {
        $sql = 'SELECT zip FROM ' . $this->db->prefix('zipcode') . " WHERE pref = '" . $pref . "' AND city = '" . $this->s_citys . "' AND town = '" . $this->s_towns . "'";

        $result = $this->cart->_result_rows($sql);

        if ($result && 1 == count($result)) {
            $this->zip = $result[0]['zip'];
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

    public function u_cart_shipping($gid, $pref, $weight, $length, $num)
    {
        $_goods = $this->cart->get_goods_view($gid, $on_view = 1);

        $_goods['cools'] = '';

        $_goods['reduce'] = 0;

        //送料単価

        if ('c' != $_goods['cool']) {
            $_goods['postage'] = $this->cargo->_field($pref, $weight, $length);

            $_goods['coolim'] = _UCART_NORMAL_IMG;
        } else {
            $_goods['postage'] = $_goods['carriage'];

            $_goods['coolim'] = _UCART_NORMAL_IMG;
        }

        //購入数

        $_goods['buy_user'] = $num;

        //クール便

        if ('t' == $_goods['cool']) {
            $_goods['cools'] = $this->cargo->_cool($length);

            $_goods['coolim'] = _UCART_COOL_IMG;
        }

        //個口

        if ('c' != $_goods['cool']) {
            $_goods['packages'] = ($num > $_goods['package']) ? (int)ceil($num / $_goods['package']) : $_goods['package'];

            $_goods['carri'] = 0;
        } else {
            $_goods['packages'] = 1;

            $_goods['carri'] = $_goods['carriage'];
        }

        //クロネコ値引き

        if (1 == $this->transporter && $_goods['packages'] > 1) {
            $_goods['reduce'] = $this->cargo->_kuroneko_reduce($_goods['packages']);
        }

        //送料

        $_goods['carriageall'] = $_goods['packages'] * $_goods['postage'];

        //行合計

        $_goods['linetotal_1'] = $num * $_goods['price'];

        $_goods['linetotal_2'] = $_goods['carriageall'] + $_goods['cools'] + $_goods['reduce'];

        return $_goods;
    }
}

//Particularly I am not entering the completion tag
