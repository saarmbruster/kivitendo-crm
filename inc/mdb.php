<?
if (! @include_once('MDB2.php') ) {
	echo "Konnte das Modul MDB2 nicht laden!<br>";
	echo "Pr&uuml;fen Sie Ihre Installation:<br>";
	echo "pear list | grep MDB2<br>";
	echo "Variable '\$include_path' in der php.ini<br>"; 
	echo "aktueller Wert: ".ini_get('include_path');
	echo "<br><br><a href='inc/install.php?check=1'>Installations - Check durchf&uuml;hren</a><br>";
	exit (1);
};

class myDB extends MDB2 {

 var $db = false;
 var $rc = false;
 var $showErr = false; // Browserausgabe
 var $log = true;     // Alle Abfragen mitloggen
 var $errfile = "tmp/lxcrm.err";
 var $logfile = "tmp/lxcrm.log";
 var $lfh = false;

	/**********************************************
	* dbFehler - Fehler in ein Log-File ausgeben
	* IN: $sql - SQL-Statement
	* IN: $err - Fehlermeldung
	* OUT: NONE
	**********************************************/ 
	function dbFehler($sql,$err) {
		$efh=fopen($this->errfile,"a");
		fputs($efh,date("Y-m-d H:i:s \n"));
		fputs($efh,'SQL:'.$sql."\n");
		fputs($efh,'Msg:'.$err."\n");
		fputs($efh,print_r($this->rc->backtrace[0],true)."\n");
		$cnt=count($this->rc->backtrace);
		for ($i=0; $i<$cnt; $i++) {
			fputs($efh,$this->rc->backtrace[$i]['line'].':'.$this->rc->backtrace[$i]['file']."\n");
		}
		fputs($efh,"--------------------------------------------- \n");
		fputs($efh,"\n");
		fclose($efh);
		if ($this->showErr)
			echo "</td></tr></table><font color='red'>$sql : $err</font><br>";
	}

	/**********************************************
	* writelog - Texte in ein Log-File ausgeben
	* IN: $txt - SQL-Statement oder Text
	* OUT: NONE
	**********************************************/ 
	function writeLog($txt) {
		if ($this->lfh===false)
			$this->lfh=fopen($this->logfile,"a");
		fputs($this->lfh,date("Y-m-d H:i:s ->"));
		fputs($this->lfh,$txt."\n");
		if (!empty($this->rc->backtrace[0])) {
			fputs($this->lfh,'Fehler: '."\n");
			fputs($this->lfh,print_r($this->rc->backtrace[0],true)."\n");
			$cnt=count($this->rc->backtrace);
			fputs($this->lfh,$this->rc->backtrace[$cnt]['line'].':'.$this->rc->backtrace[$cnt]['file']."\n");
		} else {
			fputs($this->lfh,print_r($this->rc,true));
		}
		fputs($this->lfh,"\n");
	}

	function closeLogfile() {
		fclose($this->lfh);
	}
	
	/**********************************************
	* myDB - Konstruktor
	* IN: $host,$user,$pwd,$db,$port - Parameter der Datenbank
	* OUT: DB-Objekt
	**********************************************/ 
	function myDB($host,$user,$pwd,$db,$port) {
		$dsn = array(
                    'phptype'  => 'pgsql',
                    'username' => $user,
                    'password' => $pwd,
                    'hostspec' => $host,
                    'database' => $db,
                    'port'     => $port
                );
		$options = array(
		    'result_buffering' => false,
		);
		$this->db=& MDB2::factory($dsn,$options);
		if (!$this->db || PEAR::isError($this->db)) {
			if ($this->log) $this->writeLog('Connect Error: '.$dns);
			$this->dbFehler('Connect '.print_r($dsn,true),$this->db->getMessage()); 
			die ($this->db->getMessage());
		}
		$this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);
		if ($this->log) $this->writeLog('Connect: ok ');
		return $this->db;
	}

	/**********************************************
	* query - beliebiges SQL-Statement absetzen
	* IN: $sql - Statement
	* OUT: true/false
	**********************************************/ 
	function query($sql) {
		if (strpos($sql,";")>0) {
			//Sql-Injection? HTML-Sonderzeichen zulassen
			if (!preg_match("/&[a-zA-Z]+$/",substr($sql,0,strpos($sql,";"))))
				return false;
		}
		$this->rc=@$this->db->query($sql);
		if ($this->log) $this->writeLog($sql);
		if(PEAR::isError($this->rc)) {
			$this->dbFehler($sql,$this->rc->getMessage());
			$this->rollback();
			return false;
		} else {
			return $this->rc;
		}
	}

	/**********************************************
	* update - einen Datensatz modifizieren
	* IN: $table - Tabelle
	* IN: $fields - betroffene Felder
	* IN: $values - dazugehörige Werte
	* IN: $where - welcher Datensatz
	* OUT: true/false
	**********************************************/ 
	function update($table,$fields,$values,$where) {
		if (strpos($where,"=")<1) {
			$this->dbFehler('Update','Where missing or wrong: '.$where);
			$this->dbFehler('Update',print_r(debug_backtrace(),true));
			return false;
		}
		if ($this->log) {
			$this->writeLog('Update auf: '.$table);
			$this->writeLog(print_r($fields,true));
			$this->writeLog(print_r($values,true));
		}
		//SQL-Statement vorbereiten
		$sth = $this->db->prepare("UPDATE $table set ".implode('= ? ',$fields)." ".$where);
		if (PEAR::isError($sth)) {
			$this->dbFehler($sql,$sth->getMessage());
			$this->rollback();
			return false;
		}
		//wenn ok, ausführen
		$this->rc=@$sth->execute( $values);
		if(PEAR::isError($this->rc)) {
			$this->dbFehler($sql,$this->rc->getMessage());
			$this->dbFehler(print_r($fields,true),print_r($values,true));
			$this->rollback();
			return false;
		}
		@$this->db->free();
		return true;
	}

	/**********************************************
	* insert - einen neuen Datensatz anlegen
	* IN: $table - Tabelle
	* IN: $fields - betroffene Felder
	* IN: $values - dazugehörige Werte
	* OUT: true/false
	**********************************************/ 
	function insert($table,$fields,$values) {
		if ($this->log) {
			$this->writeLog('Insert in: '.$table);
			$this->writeLog(print_r($fields,true));
			$this->writeLog(print_r($values,true));
		}
		//SQL-Statement vorbereiten
		$sth = $this->db->prepare("INSERT INTO $table (".implode(',',$fields).") VALUES (".str_repeat("?,",count($fields)-1)."?)");
		if (PEAR::isError($sth)) {
			$this->dbFehler($sql,$sth->getMessage());
			$this->dbFehler(print_r($fields,true),print_r($values,true));
			$this->rollback();
			return false;
		}
		//wenn ok, ausführen
		$this->rc=@$sth->execute( $values);
		if(PEAR::isError($this->rc)) {
			$this->dbFehler($sql,$this->rc->getMessage());
			$this->rollback();
			return false;
		}
		@$this->db->free();
		return true;
	}

	function begin() {
		if ($this->log) $this->writeLog('BEGIN');
		return $this->db->beginTransactio();
	}
	function commit() {
		if ($this->log) $this->writeLog('COMMIT');
		return $this->db->commit();
	}
	function rollback() {
		if ($this->log) $this->writeLog('ROLLBACK');
		return $this->db->rollback();
	}

	function getAll($sql) {
		if (strpos($sql,";")>0) return false;
		$this->rc=$this->db->queryAll($sql);
		if ($this->log) $this->writeLog($sql);
		if(PEAR::isError($this->rc)) {
			$this->dbFehler($sql,$this->rc->getMessage());
			return false;
		} else {
			return $this->rc;
		}
	}

	function getOne($sql) {
		$rs = $this->db->queryRow($sql);
		if ($rs) {
			return $rs;
		} else {
			return false;
		}
	}
	function saveData($txt) {
		//if (get_magic_quotes_gpc()) { 	
		if (get_magic_quotes_runtime()) { 	
			return $txt;
		} else {
			return $this->db->escape($txt); 
		}
	}

}
?>