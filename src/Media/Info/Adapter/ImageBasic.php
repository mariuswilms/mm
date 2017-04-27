<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Info\Adapter;

/**
 * This adapter makes us of image related PHP functions which
 * act directly on a given stream or file. It aims to deliver
 * some few but most common values (i.e. width and height).
 */
class ImageBasic extends \mm\Media\Info\Adapter {

	protected $_object;

	protected $_cached = [];

	protected $_map = [
		'width' => 'width',
		'height' => 'width',
		'channels' => 'channels',
		'bits' => 'bits'
	];

	public function __construct($file) {
		$this->_object = $file;
	}

	public function all() {
		$results = [];

		foreach (array_keys($this->_map) as $name) {
			$results[$name] = $this->get($name);
		}
		return $results;
	}

	public function get($name, $args = []) {
		if (isset($this->_map[$name])) {
			return call_user_func_array([$this, $this->_map[$name]], $args);
		}
	}

	public function channels() {
		$data = $this->_getimagesize();

		if (isset($data['channels'])) {
			return $data['channels'];
		}
	}

	public function bits() {
		$data = $this->_getimagesize();

		if (isset($data['bits'])) {
			return $data['bits'];
		}
	}

	public function width() {
		return $this->_getimagesize()[0];
	}

	public function height() {
		return $this->_getimagesize()[1];
	}

	protected function _getimagesize() {
		if (isset($this->_cached['getimagesize'])) {
			return $this->_cached['getimagesize'];
		}
		return $this->_cached['getimagesize'] = getimagesize($this->_object);
	}
}

?>