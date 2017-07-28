<?php
/**************************************************************************************************\
# Datei: config.class.php                                                                          #
#   - Konfigurationsklasse, in der alle globalen, sowie lokalen Konfigurationen abgefragt		   #
#     werden																					   #
	- lokale Konfigurationen werden bevorzugt													   #
#                                                                                                  #
# Entwicklung:                                                                                     #
#   - 2015-03-27, jembach (Jonas Emnbach): Erstellen der Datei                                     #
#   - 2016-06-25, jembach (Jonas Emnbach): Implementierung der globalClass in diese Datei->tmp     #
#																								   #
# TODO: set function implementation																   #
\**************************************************************************************************/
	
class config {
	protected static $db;
	protected static $tmp;
	
	
    /************************************************************************************************\
    # Funktion:                                                                                      #
    #   - prüft, ob die Datenbankverbindung aktiv ist                                                #
    #																								 #
    \************************************************************************************************/
	
	public static function checkObject() {
		if (self::$db===null) {
			self::$db=new db();
			if(self::$db->tableExists('config')==false){
				$db->startTransaction();
				self::$db->rawSQL("CREATE TABLE `config` (
  									`key` varchar(50) NOT NULL,
									 `option` varchar(75) NOT NULL DEFAULT '',
									 `value` text NOT NULL
									) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				self::$db->rawSQL("ALTER TABLE `config`
									ADD UNIQUE KEY `key` (`key`);");
				$db->commit();
			}
		}
	}
	
	
    /************************************************************************************************\
    # Funktion:                                                                                      #
    #   - gibt eine Konfiguration zurück			                                                 #
    #																								 #
    \************************************************************************************************/
	
	public static function get($key){
		try {
			$result=null;
			$result=self::$db->Select('config',new dbCond('key',$key));
			#Konfiguration auswerten
			if($result!=false){
				if(@unserialize($result[0]['value'])!==false)
					return unserialize($result[0]['value']);
				else
					return $result[0]['value'];
			}
			else
				return NULL;	
		} catch (Exception $e) {
			return NULL;
		}
	}
	
	
    /************************************************************************************************\
    # Funktion:                                                                                      #
    #   - passt eine Konfiguration an				                                                 #
    #																								 #
    \************************************************************************************************/
	
	public function set($key,$value,$option){
		
	}
	
    /************************************************************************************************\
    # Funktion:                                                                                      #
    #   - gibt eine Konfiguration zurück, die nur Sessionspezifisch ist                              #
	#	- sie muss jedesmal beim neuladen neu gesetzt werden										 #
	#	- wenn kein Wert gesetzt wird, wird der Inhalt zurückgegeben								 #
    #																								 #
    \************************************************************************************************/
	
	public static function tmp($key,$value=null){
	    if($value==null && isset(self::$tmp[$key]))
	    	return self::$tmp[$key]; 
		else if($value!=null)
			self::$tmp[$key]=$value;
					
	}
	
	
}
config::checkObject();

?>
