<?php
include_once 'rules/Constants.php';
$errorMessage = $errorMessage2 = "";														//relevant message to user.
$stepNumber = "1";																			//$stepNumber is used to keep track of events.

if(isset($_POST["generate-button"]))														//when the user clicks the GENERATE button.
{
	$recordsToBeGenerated = test_input($_POST["num-records"]);								//user input for the amount of records to be generated.
	
	if( !is_numeric($recordsToBeGenerated) || (!$recordsToBeGenerated > 0)) 				//check if user input is a numberic value above zero (0).
	{
		$errorMessage2 = "Only numbers above zero (0) allowed for NUMBER OF RECORDS.";
	}
	else
	{
		if(createCsvFile($recordsToBeGenerated))											//function to generate CSV file.
		{
			$errorMessage = "$recordsToBeGenerated records generated successfully!";		//relevant message to the user.
			$stepNumber = "2";																//$stepNumber is used to keep track of events.
		}
		else
		{
			$errorMessage2 = "Failed to generate all $recordsToBeGenerated records. Please try again.";		//relevant message to the user.
			$stepNumber = "1";																//$stepNumber is used to keep track of events.
		}
	}
}

if(isset($_POST["upload-button"]))
{
	$maxSize = 45242880;																		//maximum allowed file size (45MB).
	
	$target_dir = "uploads/";																	//foler to save the file.
	$target_file = $target_dir . test_input($_FILES["document"]["name"]);						//file path to the uploaded file.

	$documentFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));					//get file extension of uplaoded file.
	$extensions_arr = array("csv");																//allowed file extensions.

	if( in_array($documentFileType,$extensions_arr) )											//check file extension.
	{
		if(($_FILES["document"]["size"] >= $maxSize) || ($_FILES["document"]["size"] == 0)){	//check file size.
			$errorMessage2 = "File size invalid. File must be less than 45MB.";					//relevant message to user.
		}
		else
		{
			if(move_uploaded_file($_FILES["document"]["tmp_name"],$target_file))				//if file uploaded and stored successfully.
			{
				$errorMessage2 = "File uploaded successfully.";									//relevant message to user.
				$file = fopen($_FILES["document"]["name"],"r");									//open and read the file

				$recordsCount = $count = 0;
				$queryCreate = $queryDrop = "";													//SQL to create and delete table.
				$recordFromFile = array();
				if($file !== FALSE){															//if the file is valid.
					while(($data = fgetcsv($file, 100, ';')) !== FALSE){						//read a line from the file.
						if($count > 0)															//if this is the second line in the file.
						{
							$data[5] = substr($data[5],6).substr($data[5],3,2).substr($data[5],0,2);		//save the date in a format recognized by MySQL.
							array_push($recordFromFile,$data);											//save the read line into this $recordFromFile array.
							
							$myQuerys[$recordsCount] = "insert into csv_import (IdNo,Name,Surname,Initial,Age,DateOfBirth)
														values(?,?,?,?,?,?);";					/*SQL to create record, saved as a php Prepared Statement 
																								<prepare()> instead of a Multi Query because there is a limit
																								to number of queries Multi Query <multi_query()> can perform.*/
							$recordsCount++;													//keep track of number of records.
						}
						$count += 1;
					}
				}
				if(fclose($file))																//after closing  the file.
				{
					$queryDrop = "DROP TABLE `csv_import`;";									//SQL to delete DB table.
					$queryCreate = "CREATE TABLE `csv_import`(
									`IdNo` varchar(7) NOT NULL,
									`Name` varchar(30) NOT NULL,
									`Surname` varchar(30) NOT NULL,
									`Initial` varchar(1) NOT NULL,
									`Age` varchar(3) NOT NULL,
									`DateOfBirth` date NOT NULL,
									UNIQUE KEY `IdNo` (`IdNo`)
									);";														//SQL to create DB table.
					$stmtExcuted = false;														//$stmtExcuted will be used to evaulate if DB was populated.				
					$con = new mysqli($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_NAME);					//create DB connection.
					if(mysqli_query($con,$queryDrop))											//attempt to delete CSV_IMPORT table.
					{
						$errorMessage2 .= "<br>Table CSV_IMPORT dropped.";						//relevant message to user.

						if(mysqli_query($con,$queryCreate))										//recreate CSV_IMPORT table after deleting it in line 84.
						{
							$errorMessage2 .= "<br>Table CSV_IMPORT created successfully.";		//relevant message to user.
							for($x = 0; $x < count($myQuerys); $x++)
							{
								$stmt = $con->prepare($myQuerys[$x]);							//php function and SQL to create a new record.
								
								$stmt->bind_param("ssssss",$recordFromFile[$x][0],$recordFromFile[$x][1],$recordFromFile[$x][2],$recordFromFile[$x][3],$recordFromFile[$x][4],$recordFromFile[$x][5]);
								if($stmt->execute())
									$stmtExcuted = true;
								else
									$stmtExcuted = false;
							}
							if($stmtExcuted == true && $recordsCount == count($myQuerys))							//if all the queries ran without an error.
								$errorMessage2 .= "<br>All $recordsCount records were recorded successfully.";		//relevant message to user.
							else
								echo $errorMessage2 .= "<br>Failed to record.";					//relevant message to user.
						}
						else{
							$errorMessage2 .= "<br>Error creating table CSV_IMPORT.";			//relevant message to user.
						}
					}
					else																		//if attempt to delete CSV_IMPORT table in line 84 failed.
					{
						if(mysqli_query($con, $queryCreate))									//create CSV_IMPORT table.
						{
							for($x = 0; $x < count($myQuerys); $x++)
							{
								$stmt = $con->prepare($myQuerys[$x]);							//php function and SQL to create a new record.
								$stmt->bind_param("ssssss",$recordFromFile[$x][0],$recordFromFile[$x][1],$recordFromFile[$x][2],$recordFromFile[$x][3],$recordFromFile[$x][4],$recordFromFile[$x][5]);
								if($stmt->execute())
									$stmtExcuted = true;
								else
									$stmtExcuted = false;
							}
							if($stmtExcuted == true && $recordsCount == count($myQuerys))
								$errorMessage2 .= "<br>All $recordsCount records were recorded successfully.";		//relevant message to user.
							else
								$errorMessage2 .= "<br>Failed to record.";											//relevant message to user.
						}
						else
						{
							if(mysqli_error($con) == "Table 'csv_import' already exists")
							{
								$errorMessage2 .= "<br>Failed to record, error creating table CSV_IMPORT.";			//relevant message to user.
							}
							else
								$errorMessage2 .= "<br>Another error may have occured. Please try again.";			//relevant message to user.
						}
					}
					$con->close();																	//close DB connection.
				}
			}
			else{
				$stepNumber = "2";																	//$stepNumber is used to keep track of events.
				$errorMessage2 = "File upload failed.";												//relevant message to user.
			}
		}
	}
	else
	{
		$stepNumber = "2";																			//$stepNumber is used to keep track of events.
		$errorMessage = "Invalid file type.";														//relevant message to user.
	}
}
?>


<!DOCTYPE html>
<html>
	<head>
		<style>
			.error{
				color: red;
			}
		</style>
	</head>
	<body>

		<h2>DEVPROX Proficiency Test 2</h2>
		<?php
		if($stepNumber === "1")
		{?>
			<p class="error"><?php echo $errorMessage2;?></p>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
				<label for="num-records">Number of records to be generated:</label><br>
				<input type="text" name="num-records" value="" required><br><br>			
				<input type="submit" value="Generate Records" name="generate-button">
			</form>
		<?php
		}
		if($stepNumber === "2")
		{?>
			<p class="error"><?php echo $errorMessage;?></p>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" enctype="multipart/form-data">
				<label for="document">You may upload your CSV file below:</label><br>
				<input type="hidden" name="MAX_FILE_SIZE" value="45242880" required>
				<input type="file" name="document"><br><br>			
				<input type="submit" value="Upload" name="upload-button">
			</form>
		<?php
		}
		?>

	</body>
</html>