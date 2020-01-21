<?php

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}


//if ((getenv("REMOTE_ADDR")<>'78.138.130.101')and(getenv("REMOTE_ADDR")<>'89.108.105.71')){exit;}
echo ''; // чистим экран

require_once(dirname(__FILE__) . '/embed.php');
require_once(dirname(__FILE__) . '/simple_html_dom.php');
GalleryEmbed::init(array('fullInit' => true, 'embedUri' => '/gallery2/main.php', 'g2Uri' => '/gallery2/'));


if (!isset($_POST["itemId"]))
    return;
$input = $_POST["itemId"];
$i = 0;
if (is_numeric($input)) {
    $i = $i + 1;
    $ipath = getPathFromId($input);
    echo '<a href="' . $ipath . '" target="_blank"><div>';
    echo '<img src="' . getThumbPath($input) . '" style="max-width:100%; height:auto;"/>' . $ipath . '</div></a><hr />';
} else {
    // find by file name
    getPathFromFilename($input);
}

function getPathFromFilename($path) {
    global $storeConfig;
    $dbname = $storeConfig['database'];
    $tablePrefix = $storeConfig['tablePrefix'];
    $con = mysqli_connect("localhost", $storeConfig['username'], $storeConfig['password']);
    $sql = "
		SELECT {$tablePrefix}FileSystemEntity.g_id
		FROM
                {$dbname}.{$tablePrefix}FileSystemEntity ";


    //$term = mb_strtolower($_GET['term'], 'utf-8');
    $sql = $sql . " WHERE {$tablePrefix}FileSystemEntity.g_pathComponent LIKE '%" . mysqli_real_escape_string($con, trim($path)) . '%\'';

    mysqli_query($con, 'SET NAMES \'UTF8\'');
    $rs = mysqli_query($con, $sql);
    if (mysqli_num_rows($rs) == 0)
        echo "<strong><span style=\"color:#FF0000\">Не найдено!</span></strong>";
    mysqli_close($con);
    if (!$rs) {
        echo "<strong><span style=\"color:#FF0000\">Не найдено!</span></strong>";
        return;
    }



    $i = 0;
    while ($data = mysqli_fetch_assoc($rs)) {
        $ac = $data['g_id'];
        $i = $i + 1;
        $ipath = getPathFromId($ac);
        echo '<a href="' . $ipath . '" target="_blank"><div>ID - ' . $ac;
        if ($i > 10) {
            echo '</div></a><p>And more than 30 images...</p>';
            return;
        }


        echo '<img src="' . getThumbPath($ac) . '" style="max-width:100%; height:auto;"/>' . $ipath . '</div></a><hr />';
    }

    mysqli_free_result($rs);
}

//
function getPathFromId($id) {
    list($ret, $item) = GalleryCoreApi::loadEntitiesById($id);
    if ($ret) {
        echo "<strong><span style=\"color:#FF0000\">Не найдено!</span></strong>";
        exit;
    }
    if (!$item->parentId)
        return null;
    $path = normalisepath(getpath($item));
    return $path . '.html';
}

function normalisepath($path) {
    $p = parse_url($path);
    $path = $p['path'];
    return $path;
}

function getThumbPath($ac) {
    list($ret, $thumbId) = GalleryCoreApi::fetchThumbnailsByItemIds((array) $ac);
    $tid = $thumbId[$ac]->{'id'};

    global $gallery;
    $urlGenerator = & $gallery->getUrlGenerator();
    $thumbHref = $urlGenerator->generateUrl(
            array(
        'view' => 'core.DownloadItem',
        'itemId' => $tid
            ), array(
        'forceSessionId' => false,
        'forceFullUrl' => true
            )
    );
    return $thumbHref;
}

function getpath($item) {
    if (!isset($item->parentId)) {
        return '/gallery2/v';
    }
    $parentid = $item->parentId;
    list($ret, $parent) = GalleryCoreApi::loadEntitiesById($parentid);
    return $path = str_replace('//', '/', getpath($parent) . '/' . str_replace(" ", "+", $item->pathComponent));
}

