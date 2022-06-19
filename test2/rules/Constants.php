<?php
	$DB_HOST='localhost';
	$DB_USER='root';
	$DB_PASSWORD='';
	$DB_NAME='devprox';
	
	function test_input($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
	
	function createCsvFile($recordsToBeGenerated)
	{
		$names = ["Aldo","Dominic","Liam","Natasha","John","Jack","Noma","Alicia","Sean","Kate","Morgan","Matt","Simon","George","Gary","Mark","Sam","Arnold","David","Mike"];
		$surnames = ["Williams","Feather","Greenwood","Ice","Smith","Nile","Heart","Wells","Black","Bond","Wick","Glover","Whale","Fox","Micheals","Jackson","Rodriguez","Johnson","Jacobs","Hemsworth"];

		$randomLine =  array();																//will hold the random record to be generated.
		$randomLinesArray = array();														//an array to hold all the random records.

		$totalLines = 0;																	//$totalLines will be used to keep track of records created.
		$headers = array("Id","Name","Surname","Initials","Age","DateOfBirth");				//array to hold the column names.
		$currentYear = date("Y");															//$currentYear holds the value for the year we are in.

		do
		{
			$randomName = rand(0,19);
			$randomSurname = rand(0,19);
			$randomDay = rand(1,28);
			$randomMonth = rand(1,12);
			$randomYear = rand(1900,2020);													//lines 29 to 33 pick random values to create the record.
			$randomDate = $randomYear."-".$randomMonth."-".$randomDay;						//random date for date of birth.
			
			if($randomMonth < 10)
				$randomMonth = "0".$randomMonth;											//add a leading zero (0) in makes the date reader friendly.
			if($randomDay < 10)
				$randomDay = "0".$randomDay;												//add a leading zero (0) in makes the date reader friendly.
			$dateOfBirth = $randomDay."/".$randomMonth."/".$randomYear;						//store the date of birth in reader friendly format.

			/*$today = date("Y-m-d");															//today's date.
			$age = date_diff(date_create($randomDate),date_create($today))->format('%y');		//find the age.*/
			
			$age = $currentYear - $randomYear;										/*get the age by doing simple math instead of using php date
																					functions [date(), date_create() and date_diff()] to save resources -
																					default memory_limit is 128MB and this is not sufficient to allow
																					1 000 000 entries. this means the age is not always accurate.*/

			$ranID = $totalLines + 1;
			$ranName = $names[$randomName];
			$ranSur = $surnames[$randomSurname];
			$ranInit = substr($names[$randomName],0,1);										//lines 50 to 53 select values to create the record.
			$randomLine = [$ranID,$ranName,$ranSur,$ranInit,$age,$dateOfBirth];				//random record created and stored in $randomLine

			if(!array_search($randomLine,$randomLinesArray ))								//check if $randomLine already exists in $randomLinesArray.
			{
				array_push($randomLinesArray,$randomLine);									/*$randomLine array is now added to $randomLinesArray array,
																							 making $randomLinesArray multidimensional.*/
																							
				$totalLines = count($randomLinesArray);										//count of number of random records in the $randomLinesArray array.
			}
		}
		while($totalLines < $recordsToBeGenerated);											/*keep generating unique random lines until we have the desired number
																							of records.*/

		$file = fopen("output.csv","w");													//open or create the file "output.csv" file.
		if(fputcsv($file,$headers,";"," ")){													//create column titles.
			$linePrinted = false;																//false by default to evaluate if a record was created.
			foreach ($randomLinesArray as $line){
				if(fputcsv($file, $line,";"," "))												//populate "output.csv" with a record stored from $randomLinesArray.
					$linePrinted = true;														//keep track that a record was created in the csv file.
				else 
					$linePrinted = false;														//keep track that a record was created in the csv file.
			}
			fclose($file);																		//close the file.
			if($linePrinted)
				return true;
			else
				return false;
		}
		else
			return false;
	}
?>