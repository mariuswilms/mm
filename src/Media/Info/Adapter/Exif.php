<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2017 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Info\Adapter;

class Exif extends \mm\Media\Info\Adapter {

	protected $_object;

	protected $_cached = [];

	public function __construct($file) {
		$this->_object = $file;
	}

	public function all() {
		return $this->_data();
	}

	public function get($name, $args = []) {
		$data = $this->_data();

		if (isset($data[$name])) {
			return $data[$name];
		}
	}

	protected function _data() {
		if ($this->_cached) {
			return $this->_cached;
		}
		return $this->_cached = exif_read_data($this->_object);
	}
}

?>