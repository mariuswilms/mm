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

require_once 'Mime/Type.php';
require_once 'Media/Process.php';

/**
 * `Media_Process_Generic` is the base class for all media processing types. It provides
 * methods used by all type classes.
 */
class Media_Process_Generic {

	protected $_adapter;

	/**
	 * Constructor
	 *
	 * @param array $config Configuration values (at least `source` or `adapter` must be provided):
	 *              - `source`:  An absolute path to a file or a stream.
	 *              - `adapter`: Name (i.e. `'SoxShell'`) or instance of adapter to use when
	 *                           constructing the instance.
	 * @return void
	 */
	public function __construct(array $config = []) {
		$default = ['source' => null, 'adapter' => null];
		extract($config + $default);

		if (!$adapter) {
			throw new InvalidArgumentException("No adapter given.");
		}
		if (!$source && !is_object($adapter)) {
			throw new InvalidArgumentException("No source given and adapter is not an object.");
		}

		if (is_object($adapter)) {
			$this->_adapter = $adapter;
		} else {
			if (!is_resource($source)) {
				$source = fopen($source, 'r');
			}
			if ($adapter) {
				$class = "Media_Process_Adapter_{$adapter}";

				if (!class_exists($class)) { // Allows for injecting arbitrary classes.
					require_once dirname(__FILE__) . "/Adapter/{$adapter}.php";
				}

				$this->_adapter = new $class($source);
			}
		}
	}

	/**
	 * Allows for more-or-less direct access to the adapter
	 * currently in use. Adapters are allowed to react
	 * differently to the arguments passed. This method may
	 * be used for cases where abstraction for i.e. a certain
	 * command is incomplete or doesn't make sense.
	 *
	 * @param string|integer $key
	 * @param mixed $value Optional when `$key` is a boolean switch.
	 * @return boolean `true` on success, `false` if something went wrong.
	 */
	public function passthru($key, $value = null) {
		return $this->_adapter->passthru($key, $value);
	}

	/**
	 * Checks if the name of the type (i.e. `'generic'` or `'image'`)
	 * equals the provided one.
	 *
	 * @param string $name Name of the type to compare against.
	 * @return boolean
	 */
	public function is($name) {
		return $this->name() == $name;
	}

	/**
	 * Returns the lowercase name of the type.
	 *
	 * @return string I.e. `'generic'` or `'image'`.
	 */
	public function name() {
		return strtolower(str_replace('Media_Process_', null, get_class($this)));
	}

	/**
	 * Stores the media to a file or resource.
	 *
	 * @param string|resource $source Either an absolute path to a file or a writable ressource.
	 * @param boolean $overwrite Controls overwriting of an existent file, defaults to `false`.
	 * @return resource Returns the (unrewinded) source used.
	 */
	public function store($source, array $options = []) {
		$options += ['overwrite' => false];

		if (is_resource($source)) {
			$handle = $source;

			rewind($handle);
			$this->_adapter->store($handle);
		} else {
			if (file_exists($source)) {
				if (!$options['overwrite']) {
					throw new Exception("Source `{$source}` exists but not allowed to overwrite.");
				}
				unlink($source);
			}
			$handle = fopen($source, 'w');
			$this->_adapter->store($handle);
			fclose($handle);
		}
		return $source;
	}

	/**
	 * Converts the media to given MIME type.
	 *
	 * @param string $mimeType
	 * @return boolean|object false on error or a Media object on success
	 */
	public function convert($mimeType) {
		$this->_adapter->convert($mimeType);

		if ($this->name() != Mime_Type::guessName($mimeType)) {
			// Crosses media (i.e. document -> image).
			$config = Media_Process::config();

			if ($config[$this->name()] == $config[Mime_Type::guessName($mimeType)]) {
				// ...but using the same adapter.
				$media = Media_Process::factory([
					'source' => $mimeType,
					'adapter' => $this->_adapter
				]);
			} else {
				// ...using different adapters.
				$handle = fopen('php://temp', 'w+');
				$this->_adapter->store($handle);

				$media = Media_Process::factory(['source' => $handle]);
				fclose($handle);
			}
			return $media;
		}

		// Stays entirely in same media (i.e. image -> image).
		return $this;
	}
}

?>