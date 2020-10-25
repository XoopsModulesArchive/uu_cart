<?php
// $Id: Myformdate.php,v 1.1 2006/03/27 12:21:33 mikhail Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * @author        Kazumi Ono    <onokazu@xoops.org>
 * @copyright     copyright (c) 2000-2005 XOOPS.org
 */

/**
 * Date and time selection field
 *
 * @author       Kazumi Ono    <onokazu@xoops.org>
 * @copyright    copyright (c) 2000-2005 XOOPS.org
 */
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

class MyFormDateTime extends XoopsFormElementTray
{
    public function __construct($caption, $name, $size = 15, $value = '', $limit_y = '')
    {
        if ($value) {
            [$y, $m, $d] = explode('-', $value);
        } else {
            [$y, $m, $d] = explode('-', date('Y-n-j', time()));
        }

        $ylist = ($limit_y) ? $y : 1999;

        $datetime = $this->make_day_options('use', $ylist);

        $this->XoopsFormElementTray($caption, '&nbsp;');

        $year = new XoopsFormSelect('', $name . '_y', $y);

        $year->addOptionArray($datetime['year']);

        $this->addElement($year);

        $month = new XoopsFormSelect('', $name . '_m', $m);

        $month->addOptionArray($datetime['month']);

        $this->addElement($month);

        $day = new XoopsFormSelect('', $name . '_d', $d);

        $day->addOptionArray($datetime['day']);

        $this->addElement($day);
    }

    public function make_day_options($d, $limit_y)
    {
        $date_option = [];

        $ylist = ($limit_y) ? range(1926, $limit_y) : range(1925, 2010);

        $date_option['year']['--'] = '--' . _UUCART_Y;

        $date_option['month']['--'] = '--' . _UUCART_M;

        $date_option['day']['--'] = '--' . _UUCART_D;

        $loop = count($ylist);

        for ($i = 0; $i < $loop; $i++) {
            $wareki = '';

            if (1926 == $ylist[$i]) {
                $wareki = _UUCART_SHOWA;
            } elseif ($ylist[$i] < 1989) {
                $wareki = _UUCART_S . ($ylist[$i] - 1925) . _UUCART_Y;
            } elseif (1989 == $ylist[$i]) {
                $wareki = _UUCART_S . ($ylist[$i] - 1925) . _UUCART_Y_H;
            } elseif ($ylist[$i] > 1989) {
                $wareki = _UUCART_H . ($ylist[$i] - 1988) . _UUCART_Y;
            }

            $date_option['year'][$ylist[$i]] = (string)$ylist[$i] . _UUCART_Y . $wareki;
        }

        for ($i = 1; $i < 13; $i++) {
            $date_option['month'][$i] = (string)$i . _UUCART_M;
        }

        if ($d) {
            for ($i = 1; $i < 32; $i++) {
                $date_option['day'][$i] = (string)$i . _UUCART_D;
            }
        }

        return $date_option;
    }
}
