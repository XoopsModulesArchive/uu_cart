<?php

if (!defined('UUCART_CATEGORY_TOP')) {
    require XOOPS_ROOT_PATH . '/modules/uu_cart/language/japanese/main.php';
}

//header("Content-Type: image/png");

class uu_cart_image
{
    //var $db;
    public $cart;
    public $module;
    public $upload   = '';
    public $image    = '';
    public $_WX      = 140;
    public $org_size = [];
    public $im_org   = '';

    public function __construct()
    {
        global $xoopsModule;

        if (empty($xoopsModule) || $xoopsModule->getVar('dirname') != 'uu_cart') {
            require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';
            $moduleHandler = xoops_getHandler('module');
            $this->module  = $moduleHandler->getByDirname('uu_cart');
        } else {
            $this->module =& $xoopsModule;
        }
        require_once XOOPS_ROOT_PATH . '/modules/' . $this->module->getVar('dirname') . '/include/user_function.class.php';
        $this->cart =& uu_cart_user::getInstance();
        $row        = $this->cart->get_notation();
        foreach ($row as $key => $val) {
            $this->{$key} = $val;
        }
        $this->upload = XOOPS_ROOT_PATH . '/modules/' . $this->module->dirname() . '/images';
        $this->url    = XOOPS_URL . '/modules/' . $this->module->dirname() . '/images';
    }

    public function & getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new uu_cart_image();
        }
        return $instance;
    }

    public function get_image($_wx)
    {
        $size = $this->_im($this->image);
        if (false === $size) {
            $c    = $this->im_resize($_wx);
            $size = @getimagesize($this->upload . '/uploads/' . $this->image);
        }
        return '<img src="' . $this->url . '/uploads/' . $this->image . '" ' . $size[3] . ' name="im" alt="item" style="vertical-align : middle;margin : 2px;">';
    }

    public function image($im, $type)
    {
        $imcheck = false;
        $im      = strtolower($im);
        switch ($type) {
            case 'main':
                $_wx     = $this->thum;
                $imcheck = $this->_main($im);
                break;
            case 'block':
                $_wx     = $this->main;
                $imcheck = $this->_block($im);
                break;
            case 'pickup':
                $_wx     = $this->pic;
                $imcheck = $this->_pickup($im);
                break;
            default:
                break;
        }
        if (false === $imcheck) {
            return _UCART_NOWPRINT_IMG;
        }
        return $this->get_image($_wx);
    }

    public function other_image($im, $type)
    {
        $imcheck = false;
        $im      = strtolower($im);
        switch ($type) {
            case 'main':
                $_wx     = $this->thum;
                $imcheck = $this->_main($im);
                break;
            case 'block':
                $_wx     = $this->main;
                $imcheck = $this->_block($im);
                break;
            case 'pickup':
                $_wx     = $this->pic;
                $imcheck = $this->_pickup($im);
                break;
            default:
                break;
        }
        if (false === $imcheck) {
            return _UCART_NOWPRINT1_IMG;
        }
        return $this->url . '/uploads/' . $this->image;
    }

    //List Image
    public function _main($im)//90
    {
        if (!$this->_imcheck($im) || !$im) {
            return false;
        }
        $this->image  = 'thum_' . strtolower($im);
        $this->im_org = $im;
        return true;
    }

    //center block
    public function _block($im)//150
    {
        if (!$this->_imcheck($im) || !$im) {
            return false;
        }
        $this->image  = 'main_' . strtolower($im);
        $this->im_org = $im;
        return true;
    }

    //Block Image
    public function _pickup($im)//240
    {
        if (!$this->_imcheck($im) || !$im) {
            return false;
        }
        $this->image  = 'pic_' . strtolower($im);
        $this->im_org = $im;
        return true;
    }

    public function _original($im)
    {
        $size = $this->_im($im);
        return '<img src="' . $this->url . '/' . $im . '" ' . $size[3] . ' alt="NowPrinting" style="vertical-align : middle;margin : 2px;">';
    }

    public function _im($im = 'none')
    {
        if (!file_exists($this->upload . '/uploads/' . $im)) {
            return false;
        }
        return getimagesize($this->upload . '/uploads/' . $im);
    }

    public function _imcheck($im)
    {
        if (file_exists($this->upload . '/uploads/' . $im)) {
            $this->org_size = @getimagesize($this->upload . '/uploads/' . $im);
            return true;
        }
        return false;
    }

    public function im_resize($_WX)
    {
        if ($this->org_size[0] > $_WX) {
            $coef    = $_WX / $this->org_size[0];
            $this->w = (int)($this->org_size[0] * $coef);
            $this->h = (int)($this->org_size[1] * $coef);
        } else {
            $this->w = $this->org_size[0];
            $this->h = $this->org_size[1];
        }

        if ($this->use_im == 1 && !preg_match("/(image\/)(gif)/", strtolower($this->org_size['mime']))) {
            return $this->useGD();
        } elseif ($this->use_im == 2) {
            return $this->useImageMagic();
        } else {
            return true;
        }
    }

    public function useGD()
    {
        $tmp = true;
        if (preg_match("/(image\/)(x-png|png)/", strtolower($this->org_size['mime']))) {
            $src = @imagecreatefrompng($this->upload . '/uploads/' . $this->im_org);
            $dst = imagecreatetruecolor($this->w, $this->h);
            $mp  = imagecopyresampled($dst, $src, 0, 0, 0, 0, $this->w, $this->h, $this->org_size[0], $this->org_size[1]);
            $mp  = imagepng($dst, $this->upload . '/uploads/' . $this->image);
        } else {
            $src = @imagecreatefromjpeg($this->upload . '/uploads/' . $this->im_org);
            $dst = imagecreatetruecolor($this->w, $this->h);
            $tmp = imagecopyresampled($dst, $src, 0, 0, 0, 0, $this->w, $this->h, $this->org_size[0], $this->org_size[1]);
            $tmp = imagejpeg($dst, $this->upload . '/uploads/' . $this->image);
        }
        if (!$tmp) {
            return false;
        }
        return true;
    }

    public function useImageMagic()
    {
        $command = $this->magicpath . ' -geometry ' . (int)$this->w . 'x' . (int)$this->h . ' ' . $this->upload . '/uploads/' . $this->im_org . ' ' . $this->upload . '/uploads/' . $this->image;
        exec($command, $rtn, $rc);
        if ($rc != 0) {
            return false;
        }
        return true;
    }

    public function pic_view($im_org, $image)
    {
        global $_SERVER;
        $im_org = strtolower($im_org);
        $size   = $this->_im($im_org);
        if (false === $size) {
            return false;
        }
        return '<a href="javascript:openPreviewWindow(\'' . $_SERVER['SCRIPT_NAME'] . '?ucart=open_pic&amp;target_img=' . $im_org . '&amp;width=' . $size[0] . '&amp;height=' . $size[1] . '\',\'pic_open\',' . ($size[0] + 50) . ',' . ($size[1] + 100) . ');">' . $image . '</a>';
    }
}


