<?php

if (file_exists(XOOPS_ROOT_PATH . '/modules/uu_cart/language/' . $xoopsConfig['language'] . '/main.php')) {
    require XOOPS_ROOT_PATH . '/modules/uu_cart/language/' . $xoopsConfig['language'] . '/main.php';
}

eval(
'
function xoops_module_install_uu_cart($module)
{
	global $xoopsDB, $xoopsModule;
	$sql ="INSERT INTO ".$xoopsDB->prefix("goods_category")." (top,sub,exp) VALUES(\'".UUCART_CATEGORY_TOP."\',\'".UUCART_CATEGORY_SUB."\',\'".UUCART_CATEGORY_EXP."\')";
	$result = $xoopsDB->queryF($sql);
}
'
);

?>
