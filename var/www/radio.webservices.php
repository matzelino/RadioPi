<?php
	error_reporting(E_ALL);
	setlocale (LC_ALL, 'de_DE');
	setlocale(LC_TIME, "de_DE");
	
	$operation = getRequest("operation");
	$result = "";
	
	switch ($operation)
	{
		// Station Services
		//----------------------------------
		case "ListStations":
			$result = ListStations();
			break;

		//----------------------------------
		case "ReadStation":
			$result = ReadStation();
			break;

		//----------------------------------
		case "AddStation":
			$result = AddStation();
			break;

		//----------------------------------
		case "UpdateStation":
			$result = UpdateStation();
			break;

		//----------------------------------
		case "SaveStation":
			$result = SaveStation();
			break;
			
		//----------------------------------
		case "DeleteStation":
			$result = DeleteStation();
			break;
		
		// Program Services
		//----------------------------------
		case "ListPrograms":
			$result = ListPrograms();
			break;

		//----------------------------------
		case "ReadProgram":
			$result = ReadProgram();
			break;

		//----------------------------------
		case "AddProgram":
			$result = AddProgram();
			break;

		//----------------------------------
		case "UpdateProgram":
			$result = UpdateProgram();
			break;

		//----------------------------------
		case "SaveProgram":
			$result = SaveProgram();
			break;

		//----------------------------------
		case "SetActiveFlag":
			$result = SetActiveFlag();
			break;

		//----------------------------------
		case "DeleteProgram":
			$result = DeleteProgram();
			break;

		// General Services
		//----------------------------------
		case "ReadSystemInformation":
			$result = ReadSystemInformation();
			break;
	}

	print json_encode($result);


	/********************************************
	 * Webservice Functions: Station
	 */
	function ListStations()
	{
		try
		{
			$station = new Station();
			$data = $station->ListAll();
			$obj["type"] = "data";
			$obj["data"] = $data;
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}
		
		return $obj;
	}

	function ReadStation()
	{
		$id = getRequest("id");

		try
		{
			$station = new Station();
			$data = $station->Read($id);
			$obj["type"] = "data";
			$obj["data"] = $data;
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}

	//http://localhost/radio.webservices.php?operation=AddStation&name=RadioBoB&description=&stream_url=http://stream.hoerradar.de/mp3-radiobob
	function AddStation()
	{
		$name = getRequest("name");
		$description = getRequest("description");
		$stream_url = getRequest("stream_url");

		try
		{
			$station = new Station();
			$station->Add($name, $description, $stream_url);
			$obj["type"] = "success";
			$obj["data"] = "Sender gespeichert.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}

	function UpdateStation()
	{
		$id = getRequest("id");
		$name = getRequest("name");
		$description = getRequest("description");
		$stream_url = getRequest("stream_url");

		try
		{
			$station = new Station();
			$station->Update($id, $name, $description, $stream_url);
			$obj["type"] = "success";
			$obj["data"] = "Sender gespeichert.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}

	function SaveStation()
	{
		$id = getRequest("id");

		if ($id == "")
			$obj = AddStation();
		else
			$obj = UpdateStation();

		return $obj;
	}

	function DeleteStation()
	{
		$id = getRequest("id");
		
		try
		{
			$station = new Station();
			$station->Delete($id);
			$obj["type"] = "success";
			$obj["data"] = "Sender gelöscht.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}
	
	/********************************************
	 * Webservice Functions: Station
	 */
	function ListPrograms()
	{
		try
		{
			$program = new Program();
 			$data = $program->ListAll();
			$obj["type"] = "data";
			$obj["data"] = $data;
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}
		
		return $obj;
	}

	function ReadProgram()
	{
		$id = getRequest("id");

		try
		{
			$program = new Program();
			$data = $program->Read($id);
			$obj["type"] = "data";
			$obj["data"] = $data;
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}
	
//	http://radiopi/radio.webservices.php?operation=AddProgram&minute=55&hour=9&day=-1&month=-1&weekday=-1&duration=1&description=&station_id=1
	function AddProgram() 
	{
		$day = -1;
		$month = -1;
		$hour = -1;
		$minute = -1;
		
		$weekday = getRequest("weekday");
		if ($weekday == '-1')
			list($day, $month, $year) = explode(".", getRequest("date"));
		list($hour, $minute) = explode(":", getRequest("time"));
		$duration = getRequest("duration");
		$description = getRequest("description");
		$station_id = getRequest("station_id");
		
		try
		{
			$program = new Program();
			$program->Add($minute, $hour, $day, $month, $weekday, $duration, $description, $station_id);
			$obj["type"] = "success";
			$obj["data"] = "Programm gespeichert.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}
		
		return $obj;
	}
	
	function UpdateProgram() 
	{
		$day = -1;
		$month = -1;
		$hour = -1;
		$minute = -1;
		
		$id = getRequest("id");
		$weekday = getRequest("weekday");
		if ($weekday == '-1')
			list($day, $month, $year) = explode(".", getRequest("date"));
		list($hour, $minute) = explode(":", getRequest("time"));
		$duration = getRequest("duration");
		$description = getRequest("description");
		$station_id = getRequest("station_id");
		
		try
		{
			$program = new Program();
			$program->Update($id, $minute, $hour, $day, $month, $weekday, $duration, $description, $station_id);
			$obj["type"] = "success";
			$obj["data"] = "Programm gespeichert.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}
		
		return $obj;
	}
	
	function SaveProgram()
	{
		$id = getRequest("id");

		if ($id == "")
			$obj = AddProgram();
		else
			$obj = UpdateProgram();

		return $obj;
	}
	
	function SetActiveFlag()
	{
		$id = getRequest("id");
		$flag = getRequest("flag");
		
		try
		{
			$program = new Program();
			$program->SetActiveFlag($id, $flag);
			$obj["type"] = "success";
			$obj["data"] = "Programm gespeichert.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}
		
		return $obj;
	}

	function DeleteProgram()
	{
		$id = getRequest("id");
		
		try
		{
			$program = new Program();
			$program->Delete($id);
			$obj["type"] = "info";
			$obj["data"] = "Programm gelöscht.";
		}
		catch(exception $e)
		{
			$obj["type"] = "error";
			$obj["data"] = $e->getMessage();
		}

		return $obj;
	}

	// General Services
	//----------------------------------
	function ReadSystemInformation()
	{
		$config = new Config();
		$obj["type"] = "data";
		$obj["data"]["Aufnahmeverzeichnis"] = $config->sysdata["RecordingDirectory"];
		$obj["data"]["Freier Speicher"] = $config->sysdata["disk_free_space"];
		//$obj["data"]["Anzahl Aufnahmen"] = $config->sysdata["CountMP3"];
		$obj["data"]["Aktuelles Datum"] = $config->sysdata["CurrentDate"];
		
		return $obj;
	}
	
	
	/********************************************
	 * Helper functions
	 */
	function __autoload($class)
	{
		require 'class/'.$class.'.class.php';
	}
	
	function getRequest($key)
	{
		if ($_SERVER['REQUEST_METHOD'] == "POST")
		{
			return $_POST[$key];
		}
		else
		{
			return $_GET[$key];
		}
	}
?>