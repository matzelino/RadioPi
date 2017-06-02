function loadStationTable()
{
	$('#Station tbody tr').remove();
	
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "ListStations"
		},
		type: "GET",
		datatype: "json",
		async: true,
		success: function(data) {
			var response = $.parseJSON(data);
			var table = $('#Station tbody');

			if (response["data"] != false)
			{
				for (var i = 0; i < response["data"].length; i++)
				{
					$(table).append('<tr><td><input class="StationCheckBox" type="checkbox" value="' + response["data"][i]["id"] + '"></td><td><a href="javascript:editStation(' + response["data"][i]["id"] + ');">' + response["data"][i]["name"] + '</a></td></tr>');
				}
			}
			else
			{
				$(table).append('<tr><td colspan="3">Keine Sender gefunden.</td></tr>');
			}
		}
	});
}


function addStation()
{
	doEditStation('', '', '', '');
}

function editStation(StationID)
{
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "ReadStation",
			id: StationID
		},
		type: "GET",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);
			if (response["type"] == 'data')
			{
				StationID = response["data"][0]["id"];
				StationName = response["data"][0]["name"];
				StreamURL = response["data"][0]["stream_url"];
				StationDescription = response["data"][0]["description"];
				
				doEditStation(StationID, StationName, StreamURL, StationDescription);
			}
			else
			{
				displayMessage(response["data"], response["type"]);
			}
		}
	});
}

function deleteStation()
{
	if ($('.StationCheckBox:checked').size() > 0)
	{
		var hasError = false;
		
		$('.StationCheckBox:checked').each(function() {
			doDeleteStation($(this).val())
		});
	}

	//hide modal
	$('#dataConfirmModal').modal('hide');
	
	//re-load overview table
	loadStationTable();
	loadProgramTable();
	
	//display message
	if (!hasError)
	{
		displayMessage('Sender gelöscht.', 'success')
	}
	else
	{
		displayMessage('Ein oder mehrere Sender konnten nicht gelöscht werden.', 'error')
	}

	$('.StationSelector').attr('checked', false);
}


function doEditStation(StationID, StationName, StreamURL, StationDescription)
{
	$('#inputStationID').val(StationID);
	$('#inputStationName').val(StationName);
	$('#inputStreamURL').val(StreamURL);
	$('#inputStationDescription').val(StationDescription);
	
	$('#StationModal').modal({
		keyboard: true,
		show: true
	});	
}

function doDeleteStation(StationID)
{
	var hasError = false;
	
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "DeleteStation",
			id: StationID
		},
		type: "GET",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);
			if (response["type"] == 'error')
			{
				hasError = true;
			}
		}
	});

	return hasError;
}

function doSaveStation(StationID, StationName, StreamURL, StationDescription)
{
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "SaveStation",
			id: StationID,
			name: StationName,
			description: StationDescription,
			stream_url: StreamURL
		},
		type: "POST",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);

			if (response["type"] == "success")
			{
				//hide modal
				$('#StationModal').modal('hide');

				//re-load overview table
				loadStationTable();
				loadProgramTable();
			}
			
			//display message
			displayMessage(response["data"], response["type"]);
		}
	});
}	