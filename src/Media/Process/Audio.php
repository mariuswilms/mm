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

namespace mm\Media\Process;

/**
 * `Audio` provides methods to manipulate audio files and streams.
 */
class Audio extends \mm\Media\Process\Generic {

	public function channels($value) {
		return $this->_adapter->channels((integer) $value);
	}

	public function sampleRate($value) {
		return $this->_adapter->sampleRate((integer) $value);
	}
}

?>