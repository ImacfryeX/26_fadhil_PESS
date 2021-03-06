<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>dispatch</title>
</head>

<body>
	
	<?php require 'nav.php'; ?>
	<?php require 'db.php'; ?>
		
	<?php 																														// if post back
	
	if (isset($_POST["btnDispatch"]))
	{
		require_once 'db.php';
		
	// create database connection
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		
	// check connection
	if ($mysqli->connect_errno)	
	{
		die("Unable to connect to MySQL: ".$mysqli->connect_errno);
	}
		
	$patrolcarDispatched = $_POST["chkPatrolcar"];	// array of patrolcar being dispatched from post back
	$numOfPatrolcarDispatched = count($patrolcarDispatched);
		
	// insert new incident
	$incidentStatus;
		
	if ($numOfPatrolcarDispatched > 0)	
	{
		$incidentStatus='2'; // incident status to be set as Dispatched
	}
	else
	{
		$incidentStatus='1'; // incident status to be set as Pending
	}
		
	$sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId) VALUES (?, ?, ?, ?, ?, ?)";
		
	if(!($stmt = $mysqli->prepare($sql)))
	{
		die("Prepare failed: ".$mysqli->errno);
	}
		
	if(!$stmt->bind_param('ssssss',$_POST['callerName'], $_POST['contactNo'], $_POST['incidentType'], $_POST['location'], $_POST['incidentDesc'], $incidentStatus))
	{
		die("Binding parameters failed: ".$stmt->errno);
	}
		
	if (!$stmt->execute())	
	{
		die("Insert incident table failed: ".$stmt->errno);
	}
		
	// retrieve incident_id for the newly inserted incident
	$incidentId=mysqli_insert_id($mysqli);
		
	// update patrolcar status table and add into dispatch table
	for($i=0; $i < $numOfPatrolcarDispatched; $i++)
	{
		
	//Update patrol car status
	$sql = "UPDATE patrolcar SET patrolcarStatusId ='1' WHERE patrolcarID = ?";
		
	if (!($stmt = $mysqli->prepare($sql)))
	{
		die("Prepare failed: ".$mysqli->errno);
	}
		
	if (!$stmt->bind_param('s', $patrolcarDispatched[$i]))
	{
		die ("Binding parameters failed: ".$stmt->errno);
	}
		
	if (!$stmt->execute())
	{
		die("Update patrolcar_status table failed: ".$stmt->errno);
	}
		
	//Insert dispatch data
	$sql = "INSERT INTO dispatch (incidentId, patrolCarId, timeDispatched) VALUES (?, ?, NOW())";
			
	if (!($stmt = $mysqli->prepare($sql)))
	{
		die("Prepare failed: ".$mysqli->errno);
	}
		
	if (!$stmt->bind_param('ss', $incidentId, $patrolcarDispatched[$i]))
	{
		die("Binding parameters failed: ".$stmt->errno);
	}
		
	if(!$stmt->execute())
	{
		die("Insert dispatch table failed: ".$stmt->errno);
	}
	}
		
	$stmt->close();	
	$mysqli->close();
		
	}
	
	?>
	<br>
	<fieldset style=" margin: auto; border: 5PX solid">
	
	<legend>Dispatch Patrol Cars</legend>
	
	<form name="formdispatch" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		
	<table style="background-color:#bfbfbf" width="40%" border="3" align="center" cellpadding="4"  cellspacing="4">
	
	<tr>
		<td colspan="2"><strong><center>Incident Detail</center></strong></td>
	<hr>
	</tr>
		
	<tr>
		<td>Caller's Name :</td>
		<td><?php echo $_POST['callerName']?>																					<input type="hidden" name="callerName" id="callerName" 																value="<?php echo $_POST['callerName']?>"></td>
	</tr>
	<tr>
		<td>Contact No :</td>
		<td><?php echo $_POST['contactNo']?>																				<input type="hidden" name="contactNo" id="contactNo" 																value="<?php echo $_POST['contactNo']?>"></td>
	</tr>
	<tr>
		<td>Location :</td>
		<td><?php echo $_POST['location']?>																					<input type="hidden" name="location" id="location" 																	value="<?php echo $_POST['location']?>"></td>
	</tr>
	<tr>
		<td>Incident Type :</td>
		<td><?php echo $_POST['incidentType']?>																				<input type="hidden" name="incidentType" id="incidentType" 															value="<?php echo $_POST['incidentType']?>"></td>
	</tr>
	<tr>
		<td>Description :</td>
		<td><textarea name="incidentDesc" cols="45" rows="5" readonly id="incidentDesc"><?php echo $_POST['incidentDesc'] ?></textarea>																											<input name="incident" type="hidden" 																		id="incidentDesc" value="<?php echo $_POST['incidentDesc']?>"></td></tr>
	
	</table>
	
	<?php
	
	// connect to database
	require_once 'db.php';
				
	// Create connection
	$mysqli = mysqli_connect(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
	
	// Check connection
	if($mysqli->connect_errno)	{
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	//retrive from patrolcar table those patrol cars that are 2:Patrol or 3:Free
	$sql = "SELECT patrolCarId, statusDesc FROM patrolcar JOIN patrolcar_status 												ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId 																	WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'";	
			
	if(!($stmt = $mysqli->prepare($sql)))	{
		die("Prepare failed: ".$mysqli->errno);
	}
		
	if(!$stmt->execute())	{
		die("Cannot run SQL command: ".$stmt->errno);
	}
				
	if(!($resultset = $stmt->get_result()))	{
		die("No data in resultset: ".$stmt->errno);
	}
				
	$patrolcarArray; // an array variable
				
	while ($row = $resultset->fetch_assoc())	{
		$patrolcarArray[$row['patrolCarId']] = $row['statusDesc'];	
	}
				
	$stmt->close();
	$resultset->close();
	$mysqli->close();
		
	?>
		
	<!--populate table with patrol car data -->
		
	<br><br>
	<table style="background-color:#bfbfbf" border="3" align="center" width="50%">
		
	<tr>
		<td colspan="3"><center><strong>Dispatch Patrolcar Panel</strong></center></td></tr>
		
	<?php
		foreach($patrolcarArray as $key=>$value){
	?>
		
	<tr>
		<td align="center"><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>"></td>
		<td align="center"><?php echo $key ?></td>
		<td align="center"><?php echo $value ?></td></tr>
		
	<?php } ?>
		
	<tr>
		<td align="center"><input type="reset" name="btnCancel" id="btnCancel" value="Reset" class="boyubutton"></td>
		<td colspan="2" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" value="Dispatch" class="boyubutton"</td></tr>
		
	</table>
		
	</form>
		
	</fieldset>
	<footer align="center">
  <p>POLICE EMERGENCY SERVICE SYSTEM<BR>PROPERTY OF THE NPA</P>
</footer>
		
</body>
</html>