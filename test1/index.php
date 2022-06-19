<?php
	include_once 'rules/Constants.php';

	$errorMessage = "";																			//holds information to be shared with user.
	$fName = $lName = $idNo = $dateOfBirth = "";												//user data;
	
	if(isset($_POST["cancel-button"]))															//if users clicks the CANCEL button.
	{
		$errorMessage = "Cancelled.";															//relevant message to user.
	}
	if(isset($_POST["post-button"]))															//if users clicks the POST button.
	{
		$error = array();																		//to keep track of field errors.
		$fieldErrors = false;																	//checks field errors, false by default.
		
		$fName = test_input($_POST["fname"]);
		$lName = test_input($_POST["lname"]);
		$idNo = test_input($_POST["idno"]);
		$dateOfBirth = test_input($_POST["dob"]);												//lines 16 to 19 are user input fields.
		
		if (!preg_match("/^[a-zA-Z]*$/",$fName) || empty($fName))								//check if first name is of valid nature.
		{
			$errorMessage .= "Only letters allowed for FIRST NAME.<br>";						//relevant message to user.
			$error["fName"] = true;																//relevant field error.
		}
		else
		{
			$error["fName"] = false;															//relevant field error.
		}
		
		if(!preg_match("/^[a-zA-Z ]*$/",$lName) || empty($lName))								//check if last name is of valid nature.
		{
			$errorMessage .= "Only letters and white space allowed for LAST NAME.<br>";			//relevant message to user.
			$error["lName"] = true;																//relevant field error.
		}
		else
		{
			$error["lName"] = false;															//relevant field error.
		}
		
		if( (!empty($idNo)) && (strlen($idNo) == 13) && (is_numeric($idNo)) )					//check if ID number is of valid nature.
		{
			$error["idNo"] = false;																//relevant field error.
		}
		else
		{
			$error["idNo"] = true;																//relevant field error.
			$errorMessage .= "ID NUMBER missing or incorrect.<br>";								//relevant message to user.
		}
		
		if( (!empty($dateOfBirth)) && (strlen($dateOfBirth) == 10) )							//check if date of birth is of valid nature.
		{
			$error["dateOfBirth"] = false;														//relevant field error.
			$day = substr($dateOfBirth,0,2);
			$month = substr($dateOfBirth,3,2);
			$year = substr($dateOfBirth,6);
			if(!checkdate($month,$day,$year))													//php function to check if a valid date was entered.
			{
				$error["dateOfBirth"] = true;													//relevant field error.
				$errorMessage .= "Incorrect format for Date Of Birth. Please use dd/mm/YYYY.";	//relevant message to user.
			}
			else{
				$DOB = $year."-".$month."-".$day;
				if(!( (substr($year,-2) === substr($idNo,0,2)) && ($month === substr($idNo,2,2)) && ($day === substr($idNo,4,2)) ) && $error["idNo"] == false)
					$errorMessage .= "Date of birth and ID Number do not match.<br>";
			}
		}
		else
		{
			$error["dateOfBirth"] = true;
			$errorMessage .= "Incorrect format for Date Of Birth. Please use dd/mm/YYYY.";		//relevant message to user.
		}


		foreach($error as $err => $value){														//check if any field errors exist.
			if( $error[$err] == true ){
				$fieldErrors = true;															//error present in one or more fields.
			}
		}
		
		if(!$fieldErrors == true)																//if no field errors exist.
		{
			$con = new mysqli($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_NAME);							//create DB connection.
			if(!$con->connect_error)															//if connected to DB.
			{
				$stmt = $con->prepare("insert into person(IdNo,Name,Surname,DOB) values(?,?,?,?)");		//php function and SQL to create a new record.
				$stmt->bind_param("ssss",$idNo,$fName,$lName,$DOB);
				if($stmt->execute())																//if recorded is created.
				{
					$errorMessage .= "User recorded!<br>No duplicate ID Number recorded.";			//relevant message to user.
				}
				else{
					if($stmt->error == "Duplicate entry '".$idNo."' for key 'IdNo'")				//check if ID number is already recorded.
						$errorMessage .= "ID Number already recorded.";
					else																			//if record cannot be stored for any other reason
						$errorMessage .= "An error ocuured. Please try again.";
				}
				$con->close();																		//close DB connection.
			}
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

		<h2>DEVPROX Proficiency Test 1</h2>

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
			<p class="error"><?php echo $errorMessage;?></p>
			<label for="lname">Identity Number:</label><br>
			<input type="text" id="idno" name="idno" value="<?php echo $idNo;?>" required><br>
			<label for="fname">First name:</label><br>
			<input type="text" id="fname" name="fname" value="<?php echo $fName;?>" required><br>
			<label for="fname">Last name:</label><br>
			<input type="text" id="lname" name="lname" value="<?php echo $lName;?>" required><br>
			<label for="lname">Date of Birth:</label><br>
			<input type="text" id="dob" name="dob" value="<?php echo $dateOfBirth;?>" required><br><br>
			<input type="submit" value="Post" name="post-button">
		</form>

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
			<input type="submit" value="Cancel" name="cancel-button">
		</form>

	</body>
</html>

