<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2012 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2012 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Mime/Type.php';

class Mime_TypeInternalTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';

		Mime_Type::config('Magic', array(
			'adapter' => 'Fileinfo'
		));
		Mime_Type::config('Glob', array(
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		));
	}

	protected function tearDown() {
		Mime_Type::reset();
	}

	public function testGuessTypeFile() {
		$files = array(
			'a.mp4' => 'video/mp4'
		);
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Mime_Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessNameFile() {
		$map = array(
			'a.mp4' => 'video'
		);
		foreach ($map as $file => $name) {
			$this->assertEquals(
				$name,
				Mime_Type::guessName($this->_files . '/' . $file),
				"File `{$file}`."
			);
		}
	}
}

?>