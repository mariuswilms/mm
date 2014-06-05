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

require_once 'Media/Info/Image.php';

class Media_Info_ImageSystemTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testColors() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}
		$media = new Media_Info_Image([
			'source' => "{$this->_files}/image_png.png",
			'adapters' => ['Imagick']
		]);
		$expected = array(
			[
				103,
				103,
				103,
				1
			],
			[
				209,
				210,
				202,
				1
			]
		);
		$result = $media->colors(2);
		$this->assertEquals($expected, $result);
	}
}

?>