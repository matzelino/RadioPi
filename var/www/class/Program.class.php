<?php
	class Program
	{
		private $sql;
		private $recording_directory;

		public function __construct ()
		{
			$config = new Config();
			$this->sql = new SQL(
				$config->dbdata["DB_ServerName"],
				$config->dbdata["DB_UserName"],
				$config->dbdata["DB_Password"],
				$config->dbdata["DB_DatabaseName"]
			);

			$this->recording_directory = $config->sysdata['RecordingDirectory'];
		}

		public function __destruct ()
		{
		}
		
		public function ListAll()
		{
			$query = "Select p.*, s.name As station From program p, station s Where p.station_id=s.id";
			$res = $this->sql->ExecuteQuery($query);

			return $res;
		}
		
		public function Read($id)
		{
			$query = sprintf("Select * From program Where id=%s",
				mysql_real_escape_string($id)
			);

			$res = $this->sql->ExecuteQuery($query);
			return $res;
		}
		
		public function Add($minute, $hour, $day, $month, $weekday, $duration, $description, $station_id)
		{
			//check required values
			if ($minute == "" || $hour == "")
			{
				throw new exception("Das Feld 'Uhrzeit' wird benötigt.");
			}

			if ($description == "")
			{
				throw new exception("Das Feld 'Beschreibung' wird benötigt.");
			}

			if ($duration == "")
			{
				throw new exception("Das Feld 'Aufnahmedauer' wird benötigt.");
			}

			if ($station_id == "")
			{
				throw new exception("Das Feld 'Sender' wird benötigt.");
			}

			$radio = new Station();
			$radio->Read($station_id);
			
			$query = sprintf("Insert Into program (minute, hour, day, month, weekday, duration, description, station_id) Value (%s, %s, %s, %s, %s, %s, '%s', %s)",
				mysql_real_escape_string($minute),
				mysql_real_escape_string($hour), 
				mysql_real_escape_string($day),
				mysql_real_escape_string($month),
				mysql_real_escape_string($weekday),
				mysql_real_escape_string($duration),
				mysql_real_escape_string($description),
				mysql_real_escape_string($station_id)
			);

			$res = $this->sql->ExecuteQuery($query);
			if (!$res)
			{
				throw new exception("Das Programm konnte nicht hinzugefügt werden.", 1);
			}
			
			$this->CreateCron();
		}
		
		function Update($id, $minute, $hour, $day, $month, $weekday, $duration, $description, $station_id)
		{
			$this->Read($id);
		
			$radio = new Station();
			$radio->Read($station_id);
		
			$query = sprintf("Update program Set minute=%s, hour=%s, day=%s, month=%s, weekday=%s, duration=%s, description='%s', station_id=%s Where id=%s",
				mysql_real_escape_string($minute),
				mysql_real_escape_string($hour), 
				mysql_real_escape_string($day),
				mysql_real_escape_string($month),
				mysql_real_escape_string($weekday),
				mysql_real_escape_string($duration),
				mysql_real_escape_string($description),
				mysql_real_escape_string($station_id),
				mysql_real_escape_string($id)
			);

			$res = $this->sql->ExecuteQuery($query);
			
			$this->CreateCron();
		}
		
		function SetActiveFlag($id, $flag)
		{
			$this->Read($id);
		
			$query = sprintf("Update program Set active=%s Where id=%s",
				mysql_real_escape_string($flag),
				mysql_real_escape_string($id)
			);

			$res = $this->sql->ExecuteQuery($query);
			
			$this->CreateCron();
		}	
		
		function Delete($id)
		{
			$query = sprintf("Delete From program Where id=%s",
				mysql_real_escape_string($id)
			);
			
			$res = $this->sql->ExecuteQuery($query);
			if (!$res)
			{
				throw new exception("Das Programm konnte nicht gelöscht werden.", 1);
			}
			
			$this->CreateCron();
		}
		
		public function CreateCron()
		{
			$cron = new CronTab();
			$cron->reset();

			$query = "Select p.description, p.minute, p.hour, p.day, p.month, p.weekday, p.duration, s.stream_url From program p, station s Where p.station_id=s.id And Active=1";
			$res = $this->sql->ExecuteQuery($query);

			if ($res)
			{
				foreach ($res as $record)
				{
					$description = $this->_parseToken($record["description"]);
					$minute = $this->_parseToken($record['minute']);
					$hour = $this->_parseToken($record['hour']);
					$day = $this->_parseToken($record['day']);
					$month = $this->_parseToken($record['month']);
					$weekday = $this->_parseToken($record['weekday']);
					$duration = $record['duration'];
					$stream_url = $record['stream_url'];
					
					$now = date("m.d.Y - H:i");
					$filename = $description . " (" . $now . ")";

					$command = $this->_createCommand($duration, $stream_url, $filename);

					$cron->add(array(
						$minute,
						$hour,
						$day,
						$month,
						$weekday,
						$command
					));
				}
			}

			$cron->save();
		}

		/********************************************
		* Helper methods
		*/		
		private function _parseToken($token)
		{
			if ($token == -1)
				return '*';
				
			return $token;
		}
		
		private function _createCommand($duration, $stream_url, $filename)
		{
			
			return sprintf('streamripper %s -a -A -o larger -l %s -z -d %s',
				$stream_url,
				($duration * 60),
				$this->recording_directory
			);
			/*return sprintf('downloadstream %s %s %s',
				$stream_url,
				($duration * 60),
				$this->recording_directory
			);*/
		}
	}
?>