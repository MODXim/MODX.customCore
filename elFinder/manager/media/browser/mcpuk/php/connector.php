<?php

error_reporting(0); // Set E_ALL for debuging

list($base_url,) = explode('/manager/', $_SERVER['REQUEST_URI']);
$base_url .= '/';
define('MODX_BASE_URL', $base_url);
include_once('../../../../includes/config.inc.php');
startCMSSession(); 
if(!isset($_SESSION['mgrValidated'])) {
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') 
			die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
		else 
			die('{"error" : "errPerm"}');
}

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';

function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

function cleanFilename($filename) {
	$trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"yo","ж"=>"zh","з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ы"=>"i","э"=>"e","ю"=>"yu","я"=>"ya",
	"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E","Ё"=>"Yo","Ж"=>"Zh", "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Shh","Ы"=>"I","Э"=>"E","Ю"=>"Yu","Я"=>"Ya",
	"ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>""," "=>"-","№"=>"N","+"=>"-",":"=>"-",";"=>"-","!"=>"-","?"=>"-","&"=>"and","\'" =>"");
	$filename = strtr($filename, $trans);
	$filename = preg_replace('/[^\.%A-Za-z0-9 _-]/', '', $filename); // strip non-alphanumeric characters
	$filename = preg_replace('/-+/', '-', $filename);  // convert multiple dashes to one
	$filename = trim($filename, '-'); // trim excess
	return $filename;
}

// translit
if (isset($_FILES['upload']) && is_array($_FILES['upload'])) {
	foreach ($_FILES['upload']['name'] as &$name) {
		$name = cleanFilename($name);
	}
}

switch($_GET['type']) {
	case "files":
		$roots = array(
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => MODX_BASE_PATH . 'assets/files/',
			'URL'           => MODX_BASE_URL  . 'assets/files/',
			'alias'        => 'Файлы',
			'accessControl' => 'access'
		),
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => MODX_BASE_PATH . 'assets/docs/',
			'URL'           => MODX_BASE_URL  . 'assets/docs/',
			'alias'        => 'Документы',
			'uploadOrder' => array('allow', 'deny'), 
			'uploadAllow' => array(
				'text',
				'application/pdf',
				'application/msword',
				'application/vnd.ms-excel',
				'application/vnd.ms-powerpoint',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation'
			),
			'accessControl' => 'access'
		));
	break;
	case "media":
		$roots = array(
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => MODX_BASE_PATH . 'assets/media/',
			'URL'           => MODX_BASE_URL  . 'assets/media/',
			'alias'        => 'Медиа',
			'uploadOrder' => array('allow', 'deny'), 
			'uploadAllow' => array('audio','video'),
			'accessControl' => 'access'
		));
	break;
	case "flash":
		$roots = array(
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => MODX_BASE_PATH . 'assets/flash/',
			'URL'           => MODX_BASE_URL  . 'assets/flash/',
			'alias'        => 'Флеш',
			'uploadOrder' => array('allow', 'deny'),
			'uploadAllow' => array('application/x-shockwave-flash', 'application/flash-video'),
			'accessControl' => 'access'
		));
	break;
	default:
		$roots = array(
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => MODX_BASE_PATH . 'assets/images/',
			'URL'           => MODX_BASE_URL  . 'assets/images/',
			'alias'        => 'Изображения',
			'uploadOrder' => array('allow', 'deny'), 
			'uploadAllow' => array('image'),
			'accessControl' => 'access'
		));
	break;
}

$opts = array(
	'locale' => 'ru_RU.UTF-8',
	'bind' => array('upload' => 'translit'),
	'roots' => $roots
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run(); 