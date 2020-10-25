<?php

if (!defined('UUCART_CATEGORY_TOP')) {
    require XOOPS_ROOT_PATH . '/modules/uu_cart/language/japanese/main.php';
}
global $xoopsModule;

require_once __DIR__ . '/user_function.php';

class uu_cart_user extends uu_cart_db
{
    public $db;
    public $myts;
    public $categorys    = [];
    public $goods        = [];
    public $use_im       = false;
    public $sid          = false;
    public $categorydata = false;
    public $goodsdata    = false;
    public $category_row = [];
    public $categorys    = [];
    public $pickup       = [];
    public $dat          = [];
    public $prefecture   = [
        '--' => '--',
        '01' => 'ËÌ³¤Æ»',
        '02' => 'ÀÄ¿¹¸©',
        '03' => '´ä¼ê¸©',
        '04' => 'µÜ¾ë¸©',
        '05' => '½©ÅÄ¸©',
        '06' => '»³·Á¸©',
        '07' => 'Ê¡Åç¸©',
        '08' => '°ñ¾ë¸©',
        '09' => 'ÆÊÌÚ¸©',
        '10' => '·²ÇÏ¸©',
        '11' => 'ºë¶Ì¸©',
        '12' => 'ÀéÍÕ¸©',
        '13' => 'ÅìµþÅÔ',
        '14' => '¿ÀÆàÀî¸©',
        '15' => '¿·³ã¸©',
        '16' => 'ÉÙ»³¸©',
        '17' => 'ÀÐÀî¸©',
        '18' => 'Ê¡°æ¸©',
        '19' => '»³Íü¸©',
        '20' => 'Ä¹Ìî¸©',
        '21' => '´ôÉì¸©',
        '22' => 'ÀÅ²¬¸©',
        '23' => '°¦ÃÎ¸©',
        '24' => '»°½Å¸©',
        '25' => '¼¢²ì¸©',
        '26' => 'µþÅÔÉÜ',
        '27' => 'ÂçºåÉÜ',
        '28' => 'Ê¼¸Ë¸©',
        '29' => 'ÆàÎÉ¸©',
        '30' => 'ÏÂ²Î»³¸©',
        '31' => 'Ä»¼è¸©',
        '32' => 'Åçº¬¸©',
        '33' => '²¬»³¸©',
        '34' => '¹­Åç¸©',
        '35' => '»³¸ý¸©',
        '36' => 'ÆÁÅç¸©',
        '37' => '¹áÀî¸©',
        '38' => '°¦É²¸©',
        '39' => '¹âÃÎ¸©',
        '40' => 'Ê¡²¬¸©',
        '41' => 'º´²ì¸©',
        '42' => 'Ä¹ºê¸©',
        '43' => '·§ËÜ¸©',
        '44' => 'ÂçÊ¬¸©',
        '45' => 'µÜºê¸©',
        '46' => '¼¯»ùÅç¸©',
        '47' => '²­Æì¸©',
    ];

    //class object constructor
    public function __construct()
    {
        global $xoopsDB;
        parent::__construct();
        if (empty($this->myts) || !is_object($this->myts)) {
            $this->myts = MyTextSanitizer::getInstance();
        }
        if (empty($this->db) || !is_object($this->db)) {
            $this->set_BD_instance();
        }
    }

    public function make_categorys($on_view = null)
    {
        $this->categorys['--'] = '--' . UUCART_NEW_CATEGORY;
        $op                    = '';
        if ($on_view) {
            $op = ' WHERE on_view = 1';
        }// AND on_sale = 1
        $order  = ' ORDER BY gcid DESC';
        $sql    = 'SELECT * FROM ' . $this->db->prefix('goods_category') . $op . $order;
        $result =& $this->db->query($sql);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $this->categorys[$row['gcid']]    = $row['top'];
                $this->category_row[$row['gcid']] = $row;
            }
            $this->categorydata = true;
        } else {
            $this->categorydata = false;
        }
    }

    public function make_goods($gcid = null, $item = null, $limt = 0, $start = 0)
    {
        $this->goods['--'] = '--' . UUCART_NEW_CATEGORY;
        $op                = '';
        $plus              = '';
        if ($gcid) {
            $op = ' WHERE gcid = ' . $gcid;
        }
        if ($gcid && $item) {
            $plus = ' AND on_view = 1';
        }
        $sql    = 'SELECT * FROM ' . $this->db->prefix('goods') . $op . $plus;
        $result =& $this->db->query($sql, $limt, $start);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $this->goods[$row['gid']]     = $this->myts->htmlSpecialChars($row['top']);
                $this->goods_row[$row['gid']] = $row;
            }
            $this->goodsdata = true;
        } else {
            $this->goodsdata = false;
        }
    }

    public function hits_count($gid)
    {
        $sql    = 'UPDATE ' . $this->db->prefix('goods') . ' SET hit = hit+1 WHERE gid = ' . $gid;
        $result = $this->result_sql($sql);
    }

    public function _buy_goods_count($gid)
    {
        $sql = 'SELECT count(*) as c,gid FROM ' . $this->db->prefix('buy_table') . ' WHERE gid = ' . $gid . ' GROUP BY gid';
        return $this->_result($sql);
    }

    public function _goods_count($gcid)
    {
        $sql = 'SELECT count(*) as c,gcid FROM ' . $this->db->prefix('goods') . ' WHERE gcid = ' . $gcid . ' GROUP BY gcid';
        return $this->_result($sql);
    }

    public function _category($gcid)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('goods_category') . ' WHERE gcid = ' . $gcid;
        return $this->_result($sql);
    }

    public function _buy_user($id)
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('buy_user') . ' WHERE id = ' . $id;
        return $this->_result($sql);
    }

    public function _goods_next($gid)
    {
        $sql = 'SELECT gid,gcid FROM ' . $this->db->prefix('goods') . ' WHERE gid > ' . $gid;
        return $this->_result($sql, 1);
    }

    public function _goods($gid, $on_view = null)
    {
        $op = '';
        if ($on_view) {
            $op = ' AND on_view = 1';
        }
        $sql = 'SELECT * FROM ' . $this->db->prefix('goods') . ' WHERE gid = ' . $gid . $op;
        return $this->_result($sql);
    }

    public function _goods_plural($gid_arr)
    {
        $n_gid = array_map(create_function('$a', 'return intval($a);'), $gid_arr);
        $op    = implode(' OR gid = ', $n_gid);
        $sql   = 'SELECT * FROM ' . $this->db->prefix('goods') . ' WHERE gid =' . $op;
        return $this->_result_rows($sql);
    }

    public function _goods_rev($gid)
    {
        $sql = 'SELECT gid,gcid FROM ' . $this->db->prefix('goods') . ' WHERE gid < ' . $gid;
        return $this->_result($sql, 1);
    }

    public function _goods_stock($gid)
    {
        $sql = 'SELECT gid,stock FROM ' . $this->db->prefix('goods') . ' WHERE gid = ' . $gid;
        return $this->_result($sql, 1);
    }

    public function _goods_category($gid)
    {
        //$sql = "SELECT gcid,top FROM ".$this->db->prefix("goods_category")." WHERE gcid = (SELECT gcid FROM ".$this->db->prefix("goods")." WHERE gid = ".$gid.");
        $sql = 'SELECT gid,gcid FROM ' . $this->db->prefix('goods') . ' WHERE gid = ' . $gid;
        $res = $this->_result($sql);
        $sql = 'SELECT gcid,top,images FROM ' . $this->db->prefix('goods_category') . ' WHERE gcid = ' . $res['gcid'];
        return $this->_result($sql);
    }

    public function _pickup($on_view = null)
    {
        $today = date('Y-m-d', time());
        $op    = '';
        $user  = [];
        if ($on_view) {
            $op = ' AND tt.on_view = 1 AND t.type = 0';
        }
        $sql    = 'SELECT * FROM ' . $this->db->prefix('u_pickup') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('goods') . " as tt USING(gid) WHERE t.ondays <= '" . $today . "' AND t.offdays >= '" . $today . "'" . $op;
        $result =& $this->db->query($sql);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $this->pickup_row[$row['gid']] = $row;
            }
            return $this->pickup_row;
        }
        return false;
    }

    public function _pickup_lock()
    {
        $today = date('Y-m-d', time());
        $sql   = 'SELECT * FROM ' . $this->db->prefix('u_pickup') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('goods') . " as tt USING(gid) WHERE t.type = 1 AND t.ondays <= '" . $today . "' AND t.offdays >= '" . $today . "' AND tt.on_view = 1";
        return $this->_result($sql);
    }

    public function _ranking()
    {
        $sql = 'SELECT * FROM ' . $this->db->prefix('goods') . ' WHERE rank > 0 ORDER BY rank DESC';
        return $this->_result_rows($sql, 10, 0);
    }

    public function _order_status($sess)
    {
        $sql    = 'SELECT * FROM ' . $this->db->prefix('goods') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('buy_table') . " as tt USING(gid) WHERE tt.sessions = '" . $sess . "' AND tt.msend = 1";
        $result =& $this->db->query($sql);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            return $this->_arr_add($result);
        }
        return false;
    }

    public function _order_inquiry($uniq)//(t.price*tt.num) as p
    {
        $sql    = 'SELECT * FROM ' . $this->db->prefix('goods') . ' as t LEFT OUTER JOIN ' . $this->db->prefix('buy_table') . " as tt USING(gid) WHERE tt.uniq = '" . $uniq . "' AND tt.falseness = 't'";
        $result =& $this->db->query($sql);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            return $this->_arr_add($result);
        }
        return false;
    }

    public function _arr_add($result)
    {
        $user = [];
        $arr  = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            array_walk($row, 'myts_call_back', $user);
            $arr[] = $row;
        }
        return $arr;
    }

    public function uu_admin_notation_chack()
    {
        $sql    = 'SELECT * FROM ' . $this->db->prefix('u_main_setting');
        $result =& $this->db->query($sql);
        $row    = $this->db->fetchArray($result);
        if ($row) {
            $this->dat = unserialize(base64_decode($row['setting']));
            unset($row['setting']);
            $this->dat = array_merge($this->dat, $row);
            return true;
        }
        return false;
    }

    public function u_rev_select($gid, $page = 0)
    {
        $start = ($page) ? $page * 5 : 0;
        $sql   = 'SELECT * FROM ' . $this->db->prefix('goods_review') . ' WHERE gid = ' . $gid . ' ORDER BY days DESC';
        $dat   = $this->_result_rows($sql, 5, $start);
        if ($dat) {
            return $this->_array_walks($dat);
        }
        return false;
    }

    public function u_rev_count($gid)
    {
        $sql = 'SELECT count(*) as c FROM ' . $this->db->prefix('goods_review') . ' WHERE gid = ' . $gid;
        return $this->_result($sql);
    }

    public function u_rev_insert($arr)
    {
        $column = $this->sql_parse_insert($arr);
        $sql    = 'INSERT INTO ' . $this->db->prefix('goods_review') . ' (' . implode(',', $column['key']) . ', days) VALUES(' . implode(',', $column['val']) . ', NOW())';
        return $this->result_sql($sql);
    }

    public function get_prefecture_list()
    {
        return $this->prefecture;
    }

    public function get_welcome()
    {
        $dat            = $this->uu_admin_notation_chack();
        $welcome['top'] = $this->myts->htmlSpecialChars($this->dat['welcometop']);
        if ($this->dat['welcometype'] == 1) {
            $welcome['main'] = $this->myts->displayTarea($this->dat['welcome'], 1, 0, 0, 0, 0);
        } else {
            $welcome['main'] = $this->myts->displayTarea($this->dat['welcome'], 0, 1, 1, 1, 1);
        }
        return $welcome;
    }

    public function get_use_im()
    {
        $dat = $this->uu_admin_notation_chack();
        return $this->dat['use_im'];
    }

    public function get_use_ssl()
    {
        $dat = $this->uu_admin_notation_chack();
        return $this->dat['usessl'];
    }

    public function get_sid()
    {
        $dat = $this->uu_admin_notation_chack();
        return $this->dat['sid'];
    }

    public function get_category_check($on_view = null)
    {
        $this->make_categorys($on_view);
        return $this->categorydata;
    }

    public function get_goods_check($gcid = null)
    {
        $this->make_goods($gcid);
        return $this->goodsdata;
    }

    public function get_goods_array($gcid = null)
    {
        $this->make_goods($gcid);
        if (isset($this->goods)) {
            return $this->_array_hSC_walk($this->goods);
        }
        return false;
    }

    public function get_goods_list($gcid, $item = null, $limt = 0, $start = 0)
    {
        $this->make_goods($gcid, $item, $limt, $start);
        if (isset($this->goods_row)) {
            return $this->_array_myts_walk($this->goods_row);
        }
        return false;
    }

    public function get_categorys_array($on_view = null)
    {
        $this->make_categorys($on_view);
        if (isset($this->categorys)) {
            return $this->_array_hSC_walk($this->categorys);
        }
        return false;
    }

    public function get_category_view($on_view = null)
    {
        $this->make_categorys($on_view);
        if (isset($this->category_row)) {
            return $this->_array_walks($this->category_row);
        }
        return false;
    }

    public function get_goods_plural($gid_arr)
    {
        $res = $this->_goods_plural($gid_arr);
        if ($res) {
            return $this->_array_walks($res);
        }
        return false;
    }

    public function get_goods_view($gid = null, $on_view = null)
    {
        $dat = $this->_goods($gid, $on_view);
        if ($dat) {
            return $this->_array_myts_walk($dat);
        }
        return false;
    }

    public function get_goods_form($gid = null, $on_view = null)
    {
        $dat = $this->_goods($gid, $on_view);
        if ($dat) {
            return $this->_array_hSC_walk($dat);
        }
        return false;
    }

    public function get_category($gcid)
    {
        $row = $this->_category($gcid);
        if ($row) {
            return $this->_array_myts_walk($row);
        }
        return false;
    }

    public function get_goods_next($gid)
    {
        $dat = $this->_goods_next($gid);
        if ($dat) {
            return (int)$dat['gid'];
        }
        return false;
    }

    public function get_goods_rev($gid)
    {
        $dat = $this->_goods_rev($gid);
        if ($dat) {
            return (int)$dat['gid'];
        }
        return false;
    }

    public function get_pickup_detail($on_view = 'ON')
    {
        $src = [];
        $dat = [];
        $row = $this->_pickup($on_view);
        if (!$row) {
            return _UUCART_PICK_NONE;
        }
        $i = 0;
        foreach ($row as $val) {
            if (!$val) {
                break;
            }
            $dat   = $this->get_goods_detail($val['gid'], $on_view);
            $src[] = $dat;
            $i++;
            if ($i == 5) {
                break;
            }
        }
        return $src;
    }

    public function get_ranking_detail()
    {
        $src = [];
        $dat = [];
        $row = $this->_ranking();
        foreach ($row as $val) {
            if (!$val) {
                break;
            }
            $dat   = $this->_array_myts_walk($val);//$this->get_goods_detail($val['gid']);
            $src[] = $dat;
        }
        return $src;
    }

    public function get_goods_detail($gid, $on_view = 'ON')
    {
        $dat                  = $this->get_goods_view($gid);
        $dat['rev']           = $this->get_goods_rev($gid);
        $dat['next']          = $this->get_goods_next($gid);
        $category             = $this->_goods_category($gid);
        $dat['categoryid']    = (int)$category['gcid'];
        $dat['categorytop']   = $this->myts->htmlSpecialChars($category['top']);
        $dat['categoryimage'] = $this->myts->htmlSpecialChars($category['images']);
        $src                  = $this->_buy_goods_count($gid);
        $dat['buy_rank']      = $src['c'];
        return $dat;
    }

    public function get_notation_view()
    {
        $res = $this->uu_admin_notation_chack();
        if ($this->dat && isset($this->dat['shop_name'])) {
            return $this->_array_myts_walk($this->dat);
        }
        return false;
    }

    public function get_notation()
    {
        $res = $this->uu_admin_notation_chack();
        if (isset($this->dat)) {
            return $this->_array_hSC_walk($this->dat);
        }
        return false;
    }

    public function _array_walks($arr)
    {
        $user = [];
        $src  = [];
        foreach ($arr as $val) {
            array_walk($val, 'myts_call_back', $user);
            $src[] = $val;
        }
        return $src;
    }

    public function _array_myts_walk($arr)
    {
        $user = [];
        array_walk($arr, 'myts_call_back', $user);
        return $arr;
    }

    public function _array_hSC_walk($arr)
    {
        $user = [];
        array_walk($arr, 'myts_hSC_call_back', $user);
        return $arr;
    }

    public function get_transporter_name($transport)
    {
        switch ($transport) {
            case 0:
                return _UUCART_SAGAWA;
            case 1:
                return _UUCART_KURONEKO;
            case 2:
                return _UUCART_PELICAN;
            case 3:
                return '';
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
}

class uu_cart_db
{
    public $db;
    public $myts;

    public function __construct()
    {
        global $xoopsDB;
        $this->myts = MyTextSanitizer::getInstance();
        $this->db   = &$xoopsDB;
        if (empty($this->db) || !is_object($this->db)) {
            $this->set_BD_instance();
        }
    }

    public function set_BD_instance()
    {
        $this->db = XoopsDatabaseFactory::getDatabaseConnection();
    }

    public function set_BD_this_connection()
    {
        return $this->db;
    }

    public function & getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new uu_cart_user();
        }
        return $instance;
    }

    public function result_sql($sql)
    {
        $sql = (XOOPS_DB_TYPE == 'mysql') ? $result = $this->db->queryF($sql) : $result = $this->result_pg_sql($sql);
        return $result;
    }

    public function result_pg_sql($sql)
    {
        $result = pg_query($this->db->conn, 'BEGIN');
        $result = pg_query($this->db->conn, $sql);
        if ($this->db->error($result)) {
            $result = pg_query($this->db->conn, 'ABORT');
            return false;
        }
        $result = pg_query($this->db->conn, 'COMMIT');
        return $result;
    }

    public function _result($sql, $limit = 0, $start = 0)
    {
        $result =& $this->db->query($sql, $limit, $start);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            return $this->db->fetchArray($result);
        }

        return false;
    }

    public function _result_rows($sql, $limit = 0, $start = 0)
    {
        $src    = [];
        $result =& $this->db->query($sql, $limit, $start);
        if (!$this->db->error($result) && $this->db->getRowsNum($result) > 0) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $src[] = $row;
            }
            return $src;
        }

        return false;
    }

    public function get_db_instance()
    {
        return $this->db;
    }

    public function get_ts_instance()
    {
        return $this->myts;
    }

    public function sql_parse_insert($sql)
    {
        $column = [];
        foreach ($sql as $key => $val) {
            $column['key'][] = $key;
            $column['val'][] = "'" . $this->sql_escape_strings($val) . "'";
        }
        return $column;
    }

    public function sql_parse_updare($sql)
    {
        $column = [];
        foreach ($sql as $key => $val) {
            $column[] = $key . " = '" . $this->sql_escape_strings($val) . "'";
        }
        $column = implode(',', $column);
        return $column;
    }

    public function sql_escape_strings($text)
    {
        if (XOOPS_DB_TYPE == 'pgsql' && function_exists('pg_escape_string')) {
            return pg_escape_string($text);
        }
        if (XOOPS_DB_TYPE == 'mysql' && function_exists('$GLOBALS[\'xoopsDB\']->escape')) {
            return $GLOBALS['xoopsDB']->escape($text);
        }
        if (XOOPS_DB_TYPE == 'mysql' && function_exists('mysql_escape_string')) {
            return $GLOBALS['xoopsDB']->escape($text);
        } else {
            return $this->myts->addSlashes($text);
        }
        return addslashes($text);
    }

    public function _convert_alnum($str)
    {
        return preg_replace('/[^0-9]/i', '', mb_convert_kana($str, 'a'));
    }
}

function myts_call_back(&$val, $key, &$user)
{
    $u_myts = MyTextSanitizer::getInstance();
    if (is_string($val)) {
        $val        = $u_myts->displayTarea($val, 0, 1, 1, 1, 1);
        $user[$val] = $key;
    } elseif (is_int($val)) {
        $val        = (int)$val;
        $user[$val] = $key;
    }
}

function myts_hSC_call_back(&$val, $key, &$user)
{
    $u_myts = MyTextSanitizer::getInstance();
    if (is_string($val)) {
        $val        = $u_myts->htmlSpecialChars($val);
        $user[$val] = $key;
    } elseif (is_int($val)) {
        $val        = (int)$val;
        $user[$val] = $key;
    }
}
