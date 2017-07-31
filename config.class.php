<?php
	
session_start();
/**
 * Class for organisation of global configuration data
 * @category  global data
 * @package   php-config
 * @author    Jonas Embach
 * @copyright Copyright (c) 2017
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      https://github.com/jembach/php-menu
 * @version   1.0-master
 */

class config {

	const DB="db";										//just defining the name
	const COOKIE="cookies";								//just defining the name
	const SESSION="session";							//just defining the name
	const TMP="tmp";									//just defining the name
	protected static $db;
	protected static $data=array(self::DB=>array(),		//stores all conifgurations
								 self::COOKIE=>array(),
								 self::SESSION=>array(),
								 self::TMP=>array());
	
	/**
	 * initialize the class to access functions static
	 * also it creates the database table
	 */
	public static function checkObject() {
		if (self::$db===null) {
			if(defined("DB_USER") && defined("DB_PASSWORD") && defined("DB_HOST") && defined("DB_DATABASE"))
				self::$db=new db(DB_HOST,DB_USER,DB_PASSWORD,DB_DATABASE);
			else {
				throw new Exception("To use this extension you have to set the databse connection information!", 1);
				return;
			}

			if(self::$db->tableExists('config')==false){
				self::$db->startTransaction();
				self::$db->rawSQL("CREATE TABLE `config` (`key` varchar(50) NOT NULL,`value` text NOT NULL
									) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				self::$db->rawSQL("ALTER TABLE `config`
									ADD UNIQUE KEY `key` (`key`);");
				self::$db->commit();
			}
			//set database configurations
			foreach (self::$db->Select("config") as $value) {
				self::$data[self::DB][$value['key']]=$value['value'];
			}
			//set cookie configurations
			foreach ($_COOKIES as $key => $value) {
				self::$data[self::COOKIE][$key]=$value;
			}
			//set session configurations
			foreach ($_SESSION as $key => $value) {
				self::$data[self::SESSION][$key]=$value;
			}
		}
	}
	
	/**
	 * set the configuration data
	 *
	 * @param      <type>  $key    The key
	 * @param      <type>  $value  The value
	 * @param      string  $type   The type
	 */
	public function set($key,$value,$type="tmp"){
		switch ($type) {
			case 'db':
				if(isset(self::$data[self::DB][$key]))
					self::$db->Update("config",array("value"=>$value),new dbCond("key",$key));
				else
					self::$db->Insert("config",array("key"=>$key,"value"=>$value));
				self::$data[self::DB][$key]=$value;
			  break;
			case 'cookies':
				setcookie($key,$value,time()+365*24*3600);
				self::$data[self::COOKIE][$key]=$value;
			  break;
			case 'session':
				$_SESSION[$key]=$value;
				self::$data[self::SESSION][$key]=$value;
			  break;
			case 'tmp':
				self::$data[self::TMP][$key]=$value;
			  break;
		}
	}

	/**
	 * returns a configuration
	 * @param      string   $key    The configuration key
	 * @return     string  			The configuration
	 */
	public static function get($key,$type=false){
		if($type!==false){
			return self::$data[$type];
		} else{
			foreach (array(self::$data) as $value) {
				if(isset($value['key']))
					return $value['key'];
			}
		}
	}
	
}
config::checkObject();

?>
