<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
	}


// Проверка авторизации
require_once(dirname(__FILE__) . '/embed.php');
GalleryEmbed::init(array('fullInit' => true, 'embedUri' => '/gallery2/main.php', 'g2Uri' => '/gallery2/'));
/* check user */
$user = $gallery->getActiveUser();
list ($ret, $isAnon) = GalleryCoreApi::isAnonymousUser($user->getId());
if($ret || $isAnon){
    print 'Anonymous user prohibited';
    exit;
}

$dbname = $storeConfig['database'];
$tablePrefix = $storeConfig['tablePrefix'];
$con = mysqli_connect ("localhost",$storeConfig['username'],$storeConfig['password']);
$sql = "SELECT {$tablePrefix}Item.g_keywords
		FROM
		{$dbname}.{$tablePrefix}Item
		WHERE
		{$tablePrefix}Item.g_keywords <> ''";

if (isset($_GET['term'])) {
	$term = mb_strtolower($_GET['term'], 'utf-8');
	$sql=$sql." AND {$tablePrefix}Item.g_keywords LIKE '%".mysqli_real_escape_string($con,$term)."%'";
	}
mysqli_query($con, 'SET NAMES \'UTF8\'');
$rs = mysqli_query($con,$sql);

mysqli_close($con);

$ac='';
while($data = mysqli_fetch_assoc($rs)) {
	$ac=$ac.mb_strtolower($data['g_keywords'], 'utf-8').',';
	//$ac=$data['g_keywords'].',';
	}
mysqli_free_result($rs);
$ac=str_replace(' ;',',',$ac);
$ac=str_replace('; ',',',$ac);
$ac=str_replace(' ;',',',$ac);
$ac=str_replace(';;',',',$ac);

$ac=str_replace(' ,',',',$ac);
$ac=str_replace(', ',',',$ac);
$ac=str_replace(' ,',',',$ac);
$ac=str_replace(',,',',',$ac);
$ac=str_replace('-',' ',$ac);
$acm=explode(',', $ac);

foreach ($acm as $key=>$value) { $acm[$key]=trim($value); 	}
$acm = array_unique($acm);
natcasesort($acm);

$i=0;
$emp = array();
if (isset($_GET['term'])) 
	{
	foreach ($acm as $key=>$value) 
		{
		//echo $value;
		if (strpos($value, $term) !== false) 
			{
			$emp[$i]=$value; 
			$i=$i+1;
			}
		}
	echo json_encode ($emp);
	} else {echo json_encode ($acm);}

