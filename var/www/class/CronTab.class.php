<?php
	/**
	* @author Mark Wecke <mark.wecke@googlemail.com>
	* @license free :P
	*/ 
	class CronTab 
	{
		/**
		* Speichert die geparsten Einträge aller eingetragenen Cronjobs des Users
		*
		* @var array
		*/
		protected $rows = array();

		public function __construct() 
		{
			$this->_parse();
		}

		/**
		* Liest alle eingetragenen Cronjobs des Users aus und parst sie in ein Array
		*/
		protected function _parse() 
		{
			$rows = array();
			exec('crontab -l', $rows);
			foreach ($rows AS $row) 
			{
				$this->rows[] = preg_split('/\s+/', trim($row), 6);
			}
		}

		/**
		* Entfernt alle Whitespaces aus dem Hinzugefügten Cronjob
		* Wird auf alle Felder bis auf das Command Feld angewendet
		*
		* @see Cronjob::add()
		* @param string $cell
		* @return string
		*/
		protected function _sanitize($cell) 
		{
			return preg_replace('/\s+/', '', $cell);
		}

		/**
		* Liefert alle Cronjobs als Array
		*
		* @return array
		*/
		public function getAll() 
		{
			return $this->rows;
		}

		/**
		* Findet alle eingetragen Cronjobs die mit den Suchparameters übereinstimmen
		* <dl>
		*  <dt>minute, hoer, dom(day of month), month, dow(day of week), command</dt>
		*  <dd>Sucht in dem entsprechenden Feld, find nur komplette übereinstimmungen</dd>
		*  <dt>commadSW</dt>
		*  <dd>Findet alle Einträge die mit dem übergebenen Wert anfangen</dd>
		*  <dt>commandPM</dt>
		*  <dd>Findet alle Einträge auf die der übergebene RegEx passt(muss mit delimiter übergeben werden)<br />Beispiel:<br />
		*  /# Comment ID1$/ - findet alle Einträge mit dem Kommentar '# Comment ID1' am Ende
		*  </dd>
		* </dl>
		* Beispiel:<br />
		* <pre>
		* $crontab = new Crontab();
		* $crontab->find(array(
		*     'hour' => 12,
		*     'commandSW' => 'php '
		* ));
		* </pre>
		* Findet alle Cronjobs die zwischen 12 und 12:59 aufgerufen werden und des Befehl mit 'php ' startet
		*
		* @param array[optional] $spec
		* @return array
		*/
		public function find(array $spec=array()) 
		{
			$result = array();
			foreach ($this->rows as $i => $row) 
			{
				if ((!isset($spec['minute'])		|| $spec['minute'] == $row[0])
					&& (!isset($spec['hour'])		|| $spec['hour'] == $row[1])
					&& (!isset($spec['dom'])		|| $spec['dom'] == $row[2])
					&& (!isset($spec['month'])		|| $spec['month'] == $row[3])
					&& (!isset($spec['dow'])		|| $spec['dow'] == $row[4])
					&& (!isset($spec['command'])	|| $spec['command'] == $row[5])
					&& (!isset($spec['commandSW'])	|| $spec['commandSW'] == substr($row[5], 0, strlen($spec['commandSW'])))
					&& (!isset($spec['commandPM'])	|| preg_match($spec['commandPM'], $row[5]))) 
				{ 
					$result[$i] = $row;
				}
			}
			return $result;
		}

		/**
		* Entfernt alle Cronjobs die mit den Suchparameters übereinstimmen<br />
		* gleiche Suchparameter wie Crontab::find() und zusätzlich 'row' damit kann gezielt eine Zeile gelöscht werden
		*
		* @param array $spec
		* @return int Anzahl der gelöschten Zeilen
		* @see Crontab::find()
		*/
		public function remove(array $spec) 
		{
			if (isset($spec['row'])) 
			{
				unset($this->rows[$spec['row']]);
				return true;
			}
			$rows = $this->find($spec);
			foreach ($rows as $i => $row) 
			{
				unset($this->row[$i]);
			}
			return count($rows);
		}  

		/**
		* Benötigt ein sechs Spalten numerisches Array mit den Feldern: <br />
		* Minute, Stunde, Tag im Monat, Monat, Tag der Woche, Befehl
		*
		* @param array $row
		* @return boolean
		*/
		public function add(array $row) 
		{
			if (count($row) != 6) 
			{
				return false;
			}
			$command = array_pop($row);
			$row = array_map(array($this, '_sanitize'), $row);
			$row[] = $command;
			$this->rows[] = $row;
			return true;
		}

		/**
		* Speichert alle Cronjobs in die Crontab Datei des Users
		*
		* @return boolean 
		*/
		public function save() 
		{
			$file = tempnam(sys_get_temp_dir(), 'PHP_CRONTAB');
			$handle = fopen($file, 'w');
			foreach ($this->rows as $row) 
			{
				fwrite($handle, implode(' ', $row).PHP_EOL);
			}
			fclose($handle);
			exec("crontab $file");
			unlink($file);
			$tmp = $this->rows;
			$this->_parse();
			return ($tmp === $this->rows);
		}
		
		public function getRows()
		{
			foreach ($this->rows as $row) 
			{
				print(implode(' ', $row));
				print("<br>");
			}
			
			print("----");
		}
		
		public function reset()
		{
			$this->rows = array();
		}
	}
?>