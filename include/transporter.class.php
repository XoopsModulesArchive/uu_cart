<?php

if (!defined('XOOPS_MAINFILE_INCLUDED') || !defined('XOOPS_ROOT_PATH') || !defined('XOOPS_URL')) {
    trigger_error('Access error!! none mainfile:');

    exit();
}

class transporter
{
    public $send_out = '';

    public $sagawa_zoon = [
        '01' => 'A',  // ËÌ³¤Æ»
        '02' => 'B',  // ÀÄ¿¹¸©
        '03' => 'B',  // ´ä¼ê¸©
        '04' => 'C',  // µÜ¾ë¸©
        '05' => 'B',  // ½©ÅÄ¸©
        '06' => 'C',  // »³·Á¸©
        '07' => 'C',  // Ê¡Åç¸©
        '08' => 'D',  // °ñ¾ë¸©
        '09' => 'D',  // ÆÊÌÚ¸©
        '10' => 'D',  // ·²ÇÏ¸©
        '11' => 'D',  // ºë¶Ì¸©
        '12' => 'D',  // ÀéÍÕ¸©
        '13' => 'D',  // ÅìµþÅÔ
        '14' => 'D',  // ¿ÀÆàÀî¸©
        '15' => 'E',  // ¿·³ã¸©
        '16' => 'G',  // ÉÙ»³¸©
        '17' => 'G',  // ÀÐÀî¸©
        '18' => 'G',  // Ê¡°æ¸©
        '19' => 'D',  // »³Íü¸©
        '20' => 'E',  // Ä¹Ìî¸©
        '21' => 'F',  // ´ôÉì¸©
        '22' => 'F',  // ÀÅ²¬¸©
        '23' => 'F',  // °¦ÃÎ¸©
        '24' => 'F',  // »°½Å¸©
        '25' => 'H',  // ¼¢²ì¸©
        '26' => 'H',  // µþÅÔÉÜ
        '27' => 'H',  // ÂçºåÉÜ
        '28' => 'H',  // Ê¼¸Ë¸©
        '29' => 'H',  // ÆàÎÉ¸©
        '30' => 'H',  // ÏÂ²Î»³¸©
        '31' => 'I',  // Ä»¼è¸©
        '32' => 'I',  // Åçº¬¸©
        '33' => 'I',  // ²¬»³¸©
        '34' => 'I',  // ¹­Åç¸©
        '35' => 'I',  // »³¸ý¸©
        '36' => 'J',  // ÆÁÅç¸©
        '37' => 'J',  // ¹áÀî¸©
        '38' => 'J',  // °¦É²¸©
        '39' => 'J',  // ¹âÃÎ¸©
        '40' => 'K',  // Ê¡²¬¸©
        '41' => 'K',  // º´²ì¸©
        '42' => 'K',  // Ä¹ºê¸©
        '43' => 'L',  // ·§ËÜ¸©
        '44' => 'K',  // ÂçÊ¬¸©
        '45' => 'L',  // µÜºê¸©
        '46' => 'L',  // ¼¯»ùÅç¸©
        '47' => 'M', // ²­Æì¸©
    ];

    //10kgËè +270

    public $kuroneko_zoon = [
        '01' => 'A',  // ËÌ³¤Æ»
        '02' => 'B',  // ÀÄ¿¹¸©
        '03' => 'B',  // ´ä¼ê¸©
        '04' => 'C',  // µÜ¾ë¸©
        '05' => 'B',  // ½©ÅÄ¸©
        '06' => 'C',  // »³·Á¸©
        '07' => 'C',  // Ê¡Åç¸©
        '08' => 'D',  // °ñ¾ë¸©
        '09' => 'D',  // ÆÊÌÚ¸©
        '10' => 'D',  // ·²ÇÏ¸©
        '11' => 'D',  // ºë¶Ì¸©
        '12' => 'D',  // ÀéÍÕ¸©
        '13' => 'D',  // ÅìµþÅÔ
        '14' => 'D',  // ¿ÀÆàÀî¸©
        '15' => 'E',  // ¿·³ã¸©
        '16' => 'G',  // ÉÙ»³¸©
        '17' => 'G',  // ÀÐÀî¸©
        '18' => 'G',  // Ê¡°æ¸©
        '19' => 'D',  // »³Íü¸©
        '20' => 'E',  // Ä¹Ìî¸©
        '21' => 'F',  // ´ôÉì¸©
        '22' => 'F',  // ÀÅ²¬¸©
        '23' => 'F',  // °¦ÃÎ¸©
        '24' => 'F',  // »°½Å¸©
        '25' => 'H',  // ¼¢²ì¸©
        '26' => 'H',  // µþÅÔÉÜ
        '27' => 'H',  // ÂçºåÉÜ
        '28' => 'H',  // Ê¼¸Ë¸©
        '29' => 'H',  // ÆàÎÉ¸©
        '30' => 'H',  // ÏÂ²Î»³¸©
        '31' => 'I',  // Ä»¼è¸©
        '32' => 'I',  // Åçº¬¸©
        '33' => 'I',  // ²¬»³¸©
        '34' => 'I',  // ¹­Åç¸©
        '35' => 'I',  // »³¸ý¸©
        '36' => 'J',  // ÆÁÅç¸©
        '37' => 'J',  // ¹áÀî¸©
        '38' => 'J',  // °¦É²¸©
        '39' => 'J',  // ¹âÃÎ¸©
        '40' => 'K',  // Ê¡²¬¸©
        '41' => 'K',  // º´²ì¸©
        '42' => 'K',  // Ä¹ºê¸©
        '43' => 'K',  // ·§ËÜ¸©
        '44' => 'K',  // ÂçÊ¬¸©
        '45' => 'K',  // µÜºê¸©
        '46' => 'K',  // ¼¯»ùÅç¸©
        '47' => 'L', // ²­Æì¸©
    ];

    public $pelican_zoon = [
        '01' => 'A',  // ËÌ³¤Æ»
        '02' => 'B',  // ÀÄ¿¹¸©
        '03' => 'B',  // ´ä¼ê¸©
        '04' => 'B',  // µÜ¾ë¸©
        '05' => 'B',  // ½©ÅÄ¸©
        '06' => 'B',  // »³·Á¸©
        '07' => 'C',  // Ê¡Åç¸©
        '08' => 'C',  // °ñ¾ë¸©
        '09' => 'C',  // ÆÊÌÚ¸©
        '10' => 'C',  // ·²ÇÏ¸©
        '11' => 'C',  // ºë¶Ì¸©
        '12' => 'C',  // ÀéÍÕ¸©
        '13' => 'C',  // ÅìµþÅÔ
        '14' => 'C',  // ¿ÀÆàÀî¸©
        '15' => 'C',  // ¿·³ã¸©
        '16' => 'D',  // ÉÙ»³¸©
        '17' => 'D',  // ÀÐÀî¸©
        '18' => 'D',  // Ê¡°æ¸©
        '19' => 'C',  // »³Íü¸©
        '20' => 'C',  // Ä¹Ìî¸©
        '21' => 'D',  // ´ôÉì¸©
        '22' => 'D',  // ÀÅ²¬¸©
        '23' => 'D',  // °¦ÃÎ¸©
        '24' => 'D',  // »°½Å¸©
        '25' => 'E',  // ¼¢²ì¸©
        '26' => 'E',  // µþÅÔÉÜ
        '27' => 'E',  // ÂçºåÉÜ
        '28' => 'E',  // Ê¼¸Ë¸©
        '29' => 'E',  // ÆàÎÉ¸©
        '30' => 'E',  // ÏÂ²Î»³¸©
        '31' => 'F',  // Ä»¼è¸©
        '32' => 'F',  // Åçº¬¸©
        '33' => 'F',  // ²¬»³¸©
        '34' => 'F',  // ¹­Åç¸©
        '35' => 'F',  // »³¸ý¸©
        '36' => 'F',  // ÆÁÅç¸©
        '37' => 'G',  // ¹áÀî¸©
        '38' => 'G',  // °¦É²¸©
        '39' => 'G',  // ¹âÃÎ¸©
        '40' => 'H',  // Ê¡²¬¸©
        '41' => 'H',  // º´²ì¸©
        '42' => 'H',  // Ä¹ºê¸©
        '43' => 'H',  // ·§ËÜ¸©
        '44' => 'H',  // ÂçÊ¬¸©
        '45' => 'H',  // µÜºê¸©
        '46' => 'H',  // ¼¯»ùÅç¸©
        '47' => 'I', // ²­Æì¸©
    ];

    public $transporter_send = [
        0 => 'AREA',
        1 => 'SIZE',
        2 => 'WEIGHT',
        3 => 'A',
        4 => 'B',
        5 => 'C',
        6 => 'D',
        7 => 'E',
        8 => 'F',
        9 => 'G',
        10 => 'H',
        11 => 'I',
        12 => 'J',
        13 => 'K',
        14 => 'L',
        15 => 'M',
        16 => 'N',
        17 => 'O',
        18 => 'P',
    ];

    public $transporter_name = '';

    public $luck_carriage;

    public $fname;

    public $carriage_table = [];

    public $field = [];

    public $column = [];

    public $flip;

    public function __construct()
    {
        global $xoopsModule;

        if (empty($xoopsModule)) {
            require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';

            $moduleHandler = xoops_getHandler('module');

            $this->module = $moduleHandler->getByDirname('uu_cart');
        } else {
            $this->module = &$xoopsModule;
        }

        require_once XOOPS_ROOT_PATH . '/modules/' . $this->module->getVar('dirname') . '/include/user_function.class.php';

        $this->cart = &uu_cart_user::getInstance();

        $row = $this->cart->get_notation();

        foreach ($row as $key => $val) {
            $this->{$key} = $val;
        }

        if (1 == $this->islock) {
            $this->luck_carriage = $this->carriage;
        } else {
            $this->flip = $this->get_transporter_zoon($this->transport);

            foreach ($this->flip as $key => $val) {
                if ($key == $this->send_out) {
                    $this->_out_zoon = $val;

                    break;
                }
            }

            $this->get_carriage_table();
        }
    }

    public function _sagawa($field, $weight)
    {
        $weight_pulus = 0;

        if ($weight > 30) {
            $weight_pulus = ($weight - 30) * 270;
        }

        foreach ($field as $key => $val) {
            if ($field['2'] < $weight) {
                continue;
            }

            if ($field['2'] >= $weight) {
                $dummyweight = $val;

                break;
            }
        }

        return $dummyweight[$this->field] + $weight_pulus;
    }

    public function get_carriage_table()
    {
        $dat = file($this->fname);

        reset($dat);

        foreach ($dat as $val) {
            $this->carriage_table[] = explode(',', $val);
        }

        foreach ($this->carriage_table as $key) {
            $this->column[] = $key[0] . '-' . $key[1] . '-' . $key[2];
        }

        $this->field = array_search($this->_out_zoon, $this->carriage_table[0], true);
    }

    public function _field($receipt, $weight, $length)
    {
        if (1 == $this->islock) {
            return $this->luck_carriage;
        }

        if (1 == $this->transport || 2 == $this->transport) {
            $d_length = $this->_kuroneko_weight($weight);

            if (!$d_length) {
                return _UUCART_CARGO_OVER;
            }

            $length = ($length >= $d_length) ? $length : $d_length;
        }

        foreach ($this->flip as $key => $val) {
            if ($receipt == $key) {
                $receipt_zoon = $val;

                break;
            }
        }

        if ('M' == $receipt_zoon && 0 == $this->transport) {
            return _UUCART_SAGAWA_ATR;
        }

        foreach ($this->carriage_table as $key => $val) {
            if ($receipt_zoon == $val[0]) {
                $field[] = $val;
            }
        }

        $dummy = [];

        foreach ($field as $key => $val) {
            if ($field['1'] < $length) {
                continue;
            }

            if ($field['1'] >= $length) {
                $dummy = $val;

                break;
            }

            $this->luck_carriage = $dummy[$this->field];
        }

        $dummyweight = '';

        if (0 == $this->transport) {
            $dummyweight = $this->_sagawa($field, $weight);

            $this->luck_carriage = ($dummyweight >= $dummy[$this->field]) ? $dummyweight : $dummy[$this->field];
        }

        if (!$this->luck_carriage) {
            return _UUCART_CARGO_OVER;
        }

        return $this->luck_carriage;
    }

    public function _transporter_name()
    {
        return $this->transporter_name;
    }

    public function _transporter()
    {
        return $this->transport;
    }

    public function get_transporter_zoon($sender)
    {
        global $xoopsModule;

        $path = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/';

        switch ($sender) {
            case 0:
                $this->fname = $path . 'sagawa.csv';
                $this->transporter_name = _UUCART_SAGAWA;

                return $this->sagawa_zoon;
            case 1:
                $this->fname = $path . 'kuroneko.csv';
                $this->transporter_name = _UUCART_KURONEKO;

                return $this->kuroneko_zoon;
            case 2:
                $this->fname = $path . 'perikan.csv';
                $this->transporter_name = _UUCART_PELICAN;

                return $this->pelican_zoon;
            case 3:
                $this->fname = $path . 'sagawa.csv';
                $this->transporter_name = _UUCART_UPACK;

                return $this->pelican_zoon;
        }
    }

    public function get_transporter_url($transport)
    {
        switch ($transport) {
            case 0:
                return 'http://k2k.sagawa-exp.co.jp/cgi-bin/SagawaWeb.pcgi';
            case 1:
                return 'http://toi.kuronekoyamato.co.jp/cgi-bin/tneko?init';
            case 2:
                return 'http://www19.nittsu.co.jp/confirm/index.php';
            case 3:
                return '';
        }
    }

    public function _kuroneko_weight($weight)
    {
        switch (true) {
            case ($weight < 2):
                return 60;
                break;
            case ($weight < 5):
                return 80;
                break;
            case ($weight < 10):
                return 100;
                break;
            case ($weight < 15):
                return 120;
                break;
            case ($weight < 20):
                return 140;
                break;
            case ($weight < 25 && 1 == $this->transport):
                return 160;
                break;
            case ($weight < 30 && 2 == $this->transport):
                return 170;
                break;
            default:
                return false;
        }
    }

    public function _cool($length)
    {
        switch (true) {
            case ($length < 80):
                return 210;
                break;
            case ($length < 100):
                return 310;
                break;
            case ($length < 120):
                return 610;
                break;
            default:
                return 610;
        }
    }

    public function _kuroneko_reduce($num)
    {
        switch (true) {
            case (2 == $num):
                return 200;
                break;
            case (3 == $num):
                return 300;
                break;
            case (4 == $num):
                return 400;
                break;
            case ($num >= 5):
                return 500;
                break;
            default:
                return false;
        }
    }

    public function _e_collect($pay)
    {
        switch (true) {
            case ($pay <= 10000):
                return 315;
                break;
            case ($pay <= 30000):
                return 420;
                break;
            case ($pay <= 100000):
                return 630;
                break;
            case ($pay <= 300000):
                return 1050;
                break;
            case ($pay <= 500000):
                return 2100;
                break;
            case ($pay <= 1000000):
                return 3150;
                break;
            case ($pay > 1000000):
                return 4200;
                break;
            default:
                return false;
        }
    }

    public function _kuro_collect($pay)
    {
        switch (true) {
            case ($pay <= 10000):
                return 315;
                break;
            case ($pay <= 30000):
                return 420;
                break;
            case ($pay <= 100000):
                return 630;
                break;
            case ($pay <= 300000):
                return 1050;
                break;
            default:
                return false;
        }
    }

    public function _kuroneko_payment($pay)
    {
        if ($pay > 300000) {
            return false;
        }

        return $pay * 5.25;
    }
}
//Particularly I am not entering the completion tag
