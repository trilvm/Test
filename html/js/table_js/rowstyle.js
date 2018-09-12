/**
	Su kien chuot den mot hang trong bang
*/


function onMouseMoveRow(id)
{
			if(_ismenu == 0)
			{
				var row = document.getElementById(id);
				row.style.backgroundColor = "#CCCCCC";
			}
}
		

/**
	Su kien chuot qua mot hang trong bang
*/

function onMouseOverRow(id,le_or_chan)
{
			if(_ismenu == 0)
			{
			
				var row = document.getElementById(id);
				if(le_or_chan == "RowLe")
					row.style.backgroundColor = "#FFFFFF";
				if (le_or_chan == "RowChan")
					row.style.backgroundColor = "#CCFFFF";
			}
}
		