<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';

if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
    trigger_error('Access Denied');

    exit('Access Denied');
}

function zip_delete()
{
    global $xoopsModule, $xoopsDB;

    $sql_drop = 'DROP TABLE IF EXISTS ' . $xoopsDB->prefix('zipcode');

    $result = $xoopsDB->queryF($sql_drop);

    $sql_create = '
CREATE TABLE ' . $xoopsDB->prefix('zipcode') . " (
  zid int(7) unsigned NOT NULL auto_increment,
  zip varchar(7) NOT NULL default '',
  prefk varchar(8) NOT NULL default '',
  cityk varchar(128) NOT NULL default '',
  townk varchar(128) NOT NULL default '',
  pref varchar(8) NOT NULL default '',
  city varchar(128) NOT NULL default '',
  town varchar(128) NOT NULL default '',
  PRIMARY KEY  (zid),
  KEY zip (zip),
  KEY prefk (prefk),
  KEY pref (pref)
) ENGINE = ISAM PACK_KEYS=0 AUTO_INCREMENT=1";

    $result = $xoopsDB->queryF($sql_create);

    return $result;
}

$result = zip_delete();

xoops_cp_header();

if ($result) {
    echo '<span style="color:red">' . _AD_UUCART_ZIP_DELETE_DESC . '</span><br>';
} else {
    echo '<span style="color:red">' . _AD_UUCART_ERR01 . '</span><br>';
}
echo '<a href="index.php">' . _AD_UUCART_ADMIN_MENU . '</a>';
xoops_cp_footer();
