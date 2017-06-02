function loadProgramTable()
{
	$('#Program tbody tr').remove();

	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "ListPrograms"
		},
		type: "GET",
		datatype: "json",
		async: true,
		success: function(data) {
			var response = $.parseJSON(data);
			var table = $('#Program tbody');

			if (response["data"] != false)
			{
				for (var i = 0; i < response["data"].length; i++)
				{ 
					var id = response["data"][i]["minute"];
					var description = response["data"][i]["description"];
					var station = response["data"][i]["station"];
					var weekday = response["data"][i]["weekday"];
					var day = response["data"][i]["day"];
					var month = response["data"][i]["month"];
					var hour = response["data"][i]["hour"];
					var minute = response["data"][i]["minute"];
					
					var time = '';
					if (weekday >= 0)
					{
						var daynames = ['Sonntags', 'Montags', 'Dienstags', 'Mittwochs', 'Donnerstags', 'Freitags', 'Samstags'];
						time = daynames[weekday];
					}
					else
					{
						time = day.padZero(2) + '.' + month.padZero(2) + '.';
					}
					time = '(' + hour.padZero(2) + ':' + minute.padZero(2) + ') ' + time;
					
					var IsActive = "";
					if (response["data"][i]["active"] == "1")
						IsActive = "checked";
					
					var newLine = '<tr><td><input class="ProgramCheckBox" type="checkbox" value="' + response["data"][i]["id"] + '"></td><td><a href="javascript:editProgram(' + response["data"][i]["id"] + ');">' + response["data"][i]["description"] + '</a></td><td>' + time + '</td><td>' + response["data"][i]["station"] + '</td><td><input class="ProgramActiveFlag" onclick="handleActiveFlag(' + response["data"][i]["id"] + ', this);" type="checkbox" value="' + response["data"][i]["active"] + '" ' + IsActive + '></td></tr>';
					$(table).append(newLine);
				}
				$('.ProgramActiveFlag').each(function() {
					if (!$(this).attr('checked'))
						$(this).parent().parent().css('text-decoration', 'line-through');
				});
			}
			else
			{
				$(table).append('<tr><td colspan="4">Kein Programm gefunden.</td></tr>');
			}
		}
	});
}

function addProgram()
{
	doEditProgram('', '', '', '', '', '', '');
}

function editProgram(ProgramID)
{
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "ReadProgram",
			id: ProgramID
		},
		type: "GET",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);
			
			if (response["type"] == 'data')
			{
				programID = response["data"][0]["id"];
				description = response["data"][0]["description"];
				weekday = response["data"][0]["weekday"];
				day = response["data"][0]["day"];
				month = response["data"][0]["month"];
				hour = response["data"][0]["hour"];
				minute = response["data"][0]["minute"];
				duration = response["data"][0]["duration"];
				station_id = response["data"][0]["station_id"];
				
				var date = '';
				if (weekday == '-1')
				{
					date = day.padZero(2) + '.' + month.padZero(2) + '.2013';
				}
				var time = hour.padZero(2) + ':' + minute.padZero(2);
				
				doEditProgram(programID, description, date, time, weekday, duration, station_id);
			}
			else
			{
				displayMessage(response["data"], response["type"]);
			}
		}
	});	
}

function deleteProgram()
{
	if ($('.ProgramCheckBox:checked').size() > 0)
	{
		var hasError = false;
		
		$('.ProgramCheckBox:checked').each(function() {
			doDeleteProgram($(this).val())
		});
	}

	//hide modal
	$('#dataConfirmModal').modal('hide');
	
	//re-load overview table
	loadProgramTable();
	
	//display message
	if (!hasError)
	{
		displayMessage('Programm gelöscht.', 'success')
	}
	else
	{
		displayMessage('Ein oder mehrere Programme konnten nicht gelöscht werden.', 'error')
	}

	$('.ProgramSelector').attr('checked', false);
}

function doEditProgram(ProgramID, PrgDescription, PrgDate, PrgTime, PrgWeekDay, PrgDuration, PrgStationID)
{
	fillStationListBox();
	$('#inputProgramID').val(ProgramID);
	$('#inputProgramDescription').val(PrgDescription);
	$('#inputWeekday').val(PrgWeekDay);
	$('#inputDuration').val(PrgDuration);
	$('#inputDate').attr('data-date', PrgDate);
	$('#inputDateValue').val(PrgDate);
	$('#inputTime').val(PrgTime);
	$('#inputProgramStationID').val(PrgStationID);
	
	$('#inputDate').datepicker({
		autoclose: true,
		language: 'de'
	}).on('changeDate', function(ev) {
		$('#inputWeekday').val('');
	});

	$('#inputWeekday').change(function() {
		$('#inputDate').attr('data-date', '');
		$('#inputDateValue').val('');
	});

	$('#inputTime').timepicker({
		minuteStep: 5,
		showSeconds: false,
		showMeridian: false
	});
	
	$('#ProgramModal').modal({
		keyboard: true,
		show: true
	});
}

function doDeleteProgram(programID)
{
	var hasError = false;
	
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "DeleteProgram",
			id: programID
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

function doSaveProgram(ProgramID, PrgDescription, PrgDate, PrgTime, PrgWeekDay, PrgDuration, PrgStationID)
{
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "SaveProgram",
			id: ProgramID,
			description: PrgDescription,
			date: PrgDate,
			time: PrgTime,
			weekday: PrgWeekDay,
			duration: PrgDuration,
			station_id: PrgStationID
		},
		type: "POST",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);

			if (response["type"] == "success")
			{
				//hide modal
				$('#ProgramModal').modal('hide');

				//re-load overview table
				loadProgramTable();
			}
			
			//display message
			displayMessage(response["data"], response["type"]);
		}
	});
}

function handleActiveFlag(ProgramID, obj)
{
	var flag = 1;
	
	if (!obj.checked)
		flag = 0;
	
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "SetActiveFlag",
			id: ProgramID,
			flag: flag
		},
		type: "POST",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);

			if (response["type"] == "success")
			{
				//hide modal
				$('#ProgramModal').modal('hide');

				//re-load overview table
				loadProgramTable();
			}
			
			//display message
			displayMessage(response["data"], response["type"]);
		}
	});
}

function fillStationListBox()
{
	//get current list of all stations
	$.ajax({
		url: "radio.webservices.php",
		data: {
			operation: "ListStations"
		},
		type: "GET",
		datatype: "json",
		async: false,
		success: function(data) {
			var response = $.parseJSON(data);
			var listObject = $('#inputProgramStationID');

			listObject.empty();
			if (response["data"] != false)
			{
				listObject.append('<option value="0">Bitte wählen</option>');
				for (var i = 0; i < response["data"].length; i++)
				{
					var s = '<option value="' + response["data"][i]["id"] + '">' + response["data"][i]["name"] + '</option>';
					listObject.append(s);
				}
			}
			else
			{
				listObject.append('<option value="0">Kein Sender vorhanden</option>');
			}
		}
	});
}