<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2013 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2013 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Media/Process/Image.php';

class Media_Process_ImageSystemTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';

		Mime_Type::config('magic', [
			'adapter' => 'Fileinfo'
		]);
		Mime_Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		]);
	}

	public function testTrim() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}
		$media = new Media_Process_Image([
			'source' => "{$this->_files}/image_letterboxed.png",
			'adapter' => 'Imagick'
		]);
		$media->trim();
		$temporary = fopen('php://temp', 'w+');
		$media->store($temporary);
		rewind($temporary);

		$media = new Imagick();
		$media->readImageFile($temporary);

		$expected = 400;
		$result = $media->getImageWidth();
		$this->assertEquals($expected, $result);

		$expected = 200;
		$result = $media->getImageHeight();
		$this->assertEquals($expected, $result);
	}
}

?>