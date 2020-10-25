<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';

if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
    trigger_error('Access Denied');

    exit('Access Denied');
}

function zip_install($fp)
{
    global $xoopsModule, $xoopsDB;

    /*
        $sql = "SELECT count(zid) FROM ".$xoopsDB->prefix("zipcode");
        $result = $xoopsDB->fetchRow($xoopsDB->query($sql));
        if (0 < $result[0]) {
            echo '<a href="index.php">'._AD_UUCART_ZIP_EXISTS.'</a>';
            xoops_cp_footer();
            exit;
        }
    */

    $sql = 'INSERT INTO ' . $xoopsDB->prefix('zipcode') . ' (zip,prefk,cityk,townk,pref,city,town) VALUES (%s)';

    $i = 0;

    if ($fp) {
        while (!feof($fp)) {
            $execsql = '';

            $buf = fgets($fp);

            $execsql = sprintf($sql, $buf);

            $result = $xoopsDB->queryF($execsql);

            $i++;

            if (10000 == $i) {
                break;
            }
        }
    }

    echo 'Line number that processed it::' . $i . "<br>\n";

    return $fp;
}

function getexextime($sTime, $eTime)
{
    [$susec, $ssec] = explode(' ', $sTime);

    [$eusec, $esec] = explode(' ', $eTime);

    return (((float)$esec + (float)$eusec) - ((float)$ssec + (float)$susec));
}

xoops_cp_header();

$zipfile = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/sql/2KEN_ALL.csv';
$outfile = XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/sql/ken_all.out';

if (file_exists($zipfile)) {
    $fp = fopen($zipfile, 'rb');

    $time_start = microtime();

    while (!feof($fp)) {
        ob_start();

        $fp = zip_install($fp);

        $ex_time = getexextime($time_start, microtime());

        echo '<span style="color:blue">' . _AD_UUCART_EXECTIME . '::' . $ex_time . '</span><br><br>';

        ob_end_flush();
    }

    fclose($fp);

    echo '<span style="color:red">ZIPCODE INSTALL END</span><br>';
} else {
    echo '<span style="color:blue">' . _AD_UUCART_ZIP_FILE . '</span><br>';
}
echo '<a href="index.php">' . _AD_UUCART_ADMIN_MENU . '</a>';
xoops_cp_footer();
