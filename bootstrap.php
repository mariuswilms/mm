<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2014 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

/*
 * Setup caching, check if a cache class is made available i.e. through a
 * framework.
 */
$cacheRead = function($key) { return false; };
$cacheWrite = function($key, $value) { return false; };

$lithium = class_exists('\lithium\storage\Cache');

$version = is_callable('Configure::version') ? Configure::version() : null;
$cakephp20 = $version && version_compare($version, '2.0', '>=');
$cakephp13 = $version && version_compare($version, '1.3', '>=') && !$cakephp20;

if ($cakephp13 || $cakephp20) {
	$cacheRead = function($key) {
		return Cache::read($key);
	};
	$cacheWrite = function($key, $value) {
		return Cache::write($key, $value);
	};
} elseif ($lithium) {
	$cacheRead = function($key) {
		return \lithium\storage\Cache::read('default', $key);
	};
	$cacheWrite = function($key, $value) {
		return \lithium\storage\Cache::write('default', $key, $value);
	};
}

/*
 * Test for features on this system.
 */
$hasFileinfo = extension_loaded('fileinfo');
$hasImagick = extension_loaded('imagick');

/*
 * We are registering a custom autoloader here.
 */
spl_autoload_register(function($class) {
	if (strpos($class, 'mm\\') === false) {
		return;
	}
	$file = __DIR__ . '/src/' . str_replace(['mm\\', '\\'], ['', '/'], $class) . '.php';

	if (file_exists($file)) {
		include $file;
	}
});

/*
 * Configure the MIME type detection. The detection class is two headed which means it
 * uses both a glob (for matching against file extensions) and a magic adapter (for
 * detecting the type from the content of files). Available `glob` adapters are `Apache`,
 * `Freedesktop`, `Memory` and `Php`. These adapters are also available as a `magic`
 * variant with the addtion of a `Fileinfo` magic adapter. Not all adapters require
 * a file to be passed along with the configuration.
 */
use mm\Mime\Type;

if ($hasFileinfo) {
	Type::config('magic', [
		'adapter' => 'Fileinfo'
	]);
} else {
	Type::config('magic', [
		'adapter' => 'Freedesktop',
		'file' => __DIR__ . "/data/magic.db"
	]);
}
if ($cached = $cacheRead('mime_type_glob')) {
	Type::config('glob', [
		'adapter' => 'Memory'
	]);
	foreach ($cached as $item) {
		Type::$glob->register($item);
	}
} else {
	Type::config('glob', [
		'adapter' => 'Freedesktop',
		'file' => __DIR__ . "/data/glob.db"
	]);
	$cacheWrite('mime_type_glob', Type::$glob->to('array'));
}

/*
 * Configure the adpters to be used by the media process class. Adjust this
 * mapping of media names to adapters according to your environment. For example:
 * most PHP installations have GD enabled thus should choose the `Gd` adapter for
 * image transformations. However the `Imagick` adapter may be more desirable
 * in other cases and also supports transformations for documents.
 */
use mm\Media\Process;

Process::config([
	// 'audio' => 'SoxShell',
	'document' => $hasImagick ? 'Imagick' : null,
	'image' => $hasImagick ? 'Imagick' : 'Gd',
	// 'video' => 'FfmpegShell'
]);

/*
 * Configure the adpters to be used by the media info class. Adjust this
 * mapping of media names to adapters according to your environment. In contrast
 * to `Process` which operates only with one adapter per media type
 * `Info` can use multiple adapters per media type.
 */
use mm\Media\Info;

Info::config([
	// 'audio' => ['NewWave'],
	// 'document' => ['Imagick'],
	'image' => $hasImagick ? ['ImageBasic', 'Imagick'] : ['ImageBasic'],
	// 'video' => []
]);

?>