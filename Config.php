<?php
/**
 * Configuration class
 *
 * Usage:
 * 		echo "Copyright " . Config::get('company') . "2013";
 *
 * @category 	Configuration
 * @author  	Edgar Alves
 * @version 	1.0
 * @since 		11/2013
 * @filesource 	Config.php
 */

class Config {
	/**
	 * Config data
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Config Instance
	 *
	 * @var Config
	 */
	private static $_instance;

	/**
	 * Configuration XML files
	 *
	 * @var array
	 */
	private static $_files = array();

	/**
	 * Loaded Configuration XML files
	 *
	 * @var array
	 */
	private static $_loadedFiles = array();

	/**
	 * Load instance and xml configuration files
	 *
	 * @author Edgar  Alves
	 *
	 * @return void
	 */
	private static function _load() {
		// Create instance if not exists
		if (!self::$_instance) {
			self::$_instance = new Config();

			// Configuration from main conf.php
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/libs/conf.php")) {
				include($_SERVER['DOCUMENT_ROOT'] . "/libs/conf.php");
				self::$_instance->_data = get_defined_vars();
			}

			// Default configuration file
			foreach (array('/config.xml', '/config.json') as $configPath) {
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . $configPath)) {
					self::addFile($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . $configPath);
				}
			}
		}

		// Load xml that aren't loaded
		foreach (array_diff(self::$_files, self::$_loadedFiles) as $file) {
			if (file_exists($file)) {

				$type = pathinfo($file, PATHINFO_EXTENSION);
				$configData = array();

				switch ($type) {
					case 'xml':
						$xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
						if (!$xml) {
							throw new Exception("Erro no carregamento das configurações - Ficheiro '" . $file . "' não é um xml válido");
						}
						$configData = json_decode(json_encode($xml), true);

						break;
					case 'json':
						$configData = json_decode(file_get_contents($file), true);
						if (json_last_error()) {
							throw new Exception("Erro no carregamento das configurações - Ficheiro '" . $file . "' não é um json válido");
						}

						break;
					case 'php':
						$configData = include_once($file);
						if (!is_array($configData)) {
							throw new Exception("Erro no carregamento das configurações - Ficheiro '" . $file . "' tem de retornar um array");
						}

						break;
				}
				self::$_instance->_data = array_merge(self::$_instance->_data, $configData);
				self::$_loadedFiles[] = $file;

			} else {
				throw new Exception("Error loading configuration file '" . $file . "'!");
			}
		}
	}

	/**
	 * Get configuration
	 *
	 * @author Edgar  Alves
	 *
	 * @param  string $configKey Configuration field name
	 *
	 * @return mixed
	 */
	public static function get($configKey) {
		// Load configurations
		self::_load();

		if (isset(self::$_instance->_data[$configKey])) {
			$configValue = self::$_instance->_data[$configKey];
			if (is_array($configValue)) {
				$arrayToObj = function (Array $array) use (&$arrayToObj) {

					foreach($array as &$value){
		                if(is_array($value)){
		                    $value = $arrayToObj($value);
		                }
		            }

					return (object)$array;
				};

				$configValue = $arrayToObj($configValue);
			}
			return $configValue;
		}
	}

	/**
	 * Add configuration file
	 *
	 * @author Edgar  Alves
	 *
	 * @param  string $file File path
	 */
	public static function addFile($file) {
		if (file_exists($file) && !isset(self::$_files[$file])) {
			self::$_files[] = $file;
		} else {
			throw new Exception("Error adding configuration file '" . $file . "'!");
		}
	}
}