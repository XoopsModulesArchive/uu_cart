<?php

if (!defined('UUCART_CATEGORY_TOP')) {
    require XOOPS_ROOT_PATH . '/modules/uu_cart/language/japanese/main.php';
}

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/user_function.class.php';

class uu_sales_customer_management extends uu_cart_user
{
    //class object constructor

    public function __construct()
    {
        global $xoopsDB;

        parent::__construct();

        if (empty($this->db) || !is_object($this->db)) {
            $this->set_BD_instance();
        }
    }
}

//Particularly I am not entering the completion tag
