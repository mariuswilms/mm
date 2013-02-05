<?php

if (class_exists('Cache')) {
	$class = 'Cache';
} elseif (class_exists('\lithium\storage\Cache')) {
	$class = 'Cache';
}
$cacheRead = function($key) use ($class) { return $class::read($key); };
$cacheWrite = function($key, $value) use ($class){ return $class::write($key, $value); };

/*
 * Test for features on this system.
 */
$hasFileinfo = extension_loaded('fileinfo');
$hasImagick = extension_loaded('imagick');

/*
 * Bootstrap the `mm` library. We are putting the library into the include path which
 * is expected (by the library) in order to be able to load classes.
 */
$mm = __DIR__;

if (strpos(ini_get('include_path'), $mm) === false) {
	ini_set('include_path', "{$mm}/src" . PATH_SEPARATOR . ini_get('include_path'));
}

/**
 * Configure the MIME type detection. The detection class is two headed which means it
 * uses both a glob (for matching against file extensions) and a magic adapter (for
 * detecting the type from the content of files). Available `Glob` adapters are `Apache`,
 * `Freedesktop`, `Memory` and `Php`. These adapters are also available as a `Magic`
 * variant with the addtion of a `Fileinfo` magic adapter. Not all adapters require
 * a file to be passed along with the configuration.
 */
require_once 'Mime/Type.php';

if ($hasFileinfo) {
	Mime_Type::config('Magic', array(
		'adapter' => 'Fileinfo'
	));
} else {
	Mime_Type::config('Magic', array(
		'adapter' => 'Freedesktop',
		'file' => "{$mm}/data/magic.db"
	));
}
if ($cached = $cacheRead('mime_type_glob')) {
	Mime_Type::config('Glob', array(
		'adapter' => 'Memory'
	));
	foreach ($cached as $item) {
		Mime_Type::$glob->register($item);
	}
} else {
	Mime_Type::config('Glob', array(
		'adapter' => 'Freedesktop',
		'file' => "{$mm}/data/glob.db"
	));
	$cacheWrite('mime_type_glob', Mime_Type::$glob->to('array'));
}

/**
 * Configure the adpters to be used by the media process class. Adjust this
 * mapping of media names to adapters according to your environment. For example:
 * most PHP installations have GD enabled thus should choose the `Gd` adapter for
 * image transformations. However the `Imagick` adapter may be more desirable
 * in other cases and also supports transformations for documents.
 */
require_once 'Media/Process.php';

Media_Process::config(array(
	// 'audio' => 'SoxShell',
	'document' => $hasImagick ? 'Imagick' : null,
	'image' => $hasImagick ? 'Imagick' : 'Gd',
	// 'video' => 'FfmpegShell'
));

/**
 * Configure the adpters to be used by the media info class. Adjust this
 * mapping of media names to adapters according to your environment. In contrast
 * to `Media_Process` which operates only with one adapter per media type
 * `Media_Info` can use multiple adapters per media type.
 */
require_once 'Media/Info.php';

Media_Info::config(array(
	// 'audio' => array('NewWave'),
	// 'document' => array('Imagick'),
	'image' => $hasImagick ? array('ImageBasic', 'Imagick') : array('ImageBasic'),
	// 'video' => array()
));

?>