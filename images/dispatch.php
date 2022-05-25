<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dispatch</title>
</head>

<body style="background-color: #BFBFBF">
	<style>
img {
  display: block;
  margin-left: auto;
  margin-right: auto;
}
</style>
	<?php require 'nav.php'?>
	<?php require 'db.php'?>
	<?php
	  if (isset($_POST["btnDispatch"]))
		  require_once'db.php';
		  
		  $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		  
		  if($mysqli->connect_errno){
			  die("Unable to connect to MySql: ".$mysqli->connect-errno);
		  }
		  
		  $patrolcarDispatched = $_POST["chkPatrolcar"];
		  $numOfPatrolcarDispatched = count($patrolcarDispatched);
		  
		  $incidentStatus;
		  if ($numOfPatrolcarDispatched > 0){
			  $incidentStatus='2';
		  }
		  else
		  {
			  $incidentStatus='1';
		  }
		  $sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId) VALUES (?, ?, ?, ?, ?, ?)";
		  
		  echo $sql;
		  echo $incidentStatus;
		  echo $_POST['callerName'];
          echo $_POST['contactNo'];
          echo $_POST['incidentType'];
          echo $_POST['location'];
          echo $_POST['incidentDesc'];
		 
		  if(!($stmt = $mysqli->prepare($sql)))
		  {
              die("Prepare failed: ".$mysqli->errno);
          }

          if(!$stmt->bind_param('ssssss', $_POST['callerName'],
                                          $_POST['contactNo'],
                                          $_POST['incidentType'],
                                          $_POST['location'],
                                          $_POST['incidentDesc'],
                                          $incidentStatus)){

              die("Binding parameters failed: ".$stmt->errno);
          }
      
    if(!$stmt->execute()){
        die("Insert incident table failed: ".$stmt->errno);
    }
	
	$incidentId=mysqli_insert_id($mysqli);
	for($i=0; $i < $numOfPatrolcarDispatched; $i++){
		$sql = "UPDATE patrolcar SET patrolcarStatusId = '1' WHERE patrolcarId = ?";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $patrolcarDispatched[$i])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Update patrolcar_status table failed: ".$stmt->errno);
		}
		
		$sql = "INSERT INTO dispatch (incidentId, patrolcarId, timeDispatched) VALUES (?, ?, NOW())";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('ss',$incidentId, $patrolcarDispatched[$i])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->excute()){
			die("Insert dispatch table failed: ".$stmt->errno);
		}
	}
	
	$stmt->close();
	
	$mysqli->close();
	?>
	<fieldset>
 <legend>Dispatch Patrol Cars</legend>
		<form name="formdispatch" method="post" action="">
		<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>
		<div>
			<table width="40%" border="1" align="center" cellpadding="4" cellspacing="4">
			<tr>
				<td colspan="2"><strong><center>Incident Detail</center></strong></td>
				<hr>
			</tr>
				<tr>
					<td>Name of Caller:</td>
					<td><?php echo $_POST['callerName']?>
						<input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST['callerName']?>">
					</td>
				</tr>
				<tr>
					<td>Contact Number:</td>
					<td><?php echo $_POST['contactNo']?>
						<input type="hidden" name="contactNo" id="contactNo" value="<?php echo $_POST['contactNo']?>">
					</td>
					
				</tr>
				<tr>
					<td>Location:</td>
					<td><?php echo $_POST['location']?><input type="hidden" name="location" id="location" value="<?php echo $_POST['location']?>"></td>
				</tr>
				<tr>
					<td>Incident Type:</td>
					<td><?php echo $_POST['incidentType']?>
					<input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST['incidentType']?>">
					</td>
				</tr>
				<tr>
					<td>Description:</td>
					<td><textarea name="incidentDesc" cols="45" rows="5" readonly id="incidentDesc"><?php echo $_POST['incidentDesc'] ?></textarea>
					<input name="incidentDesc" type="hidden" id="incidentDesc" value="<?php echo $_POST['incidentDesc']?>">
					</td>
				</tr>
			</table>
			<?php
	        require_once'db.php'; //Connect to database 
	        $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE); //Create database connection
			//Check Connection
			if($mysqli->connect_errno){ 
				die("Failed to connect to MySQL: ".$mysqli->connect_errno);
			}
			$sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarstatusId='3'";
					
					if (!($stmt = $mysqli->prepare($sql))){
						die("Prepare failed: ".$mysqli->errno);
					}
					if(!$stmt->execute()){
						die("Cannot run SQL command: ".$stmt->errno);
					}
					if(!($resultset = $stmt->get_result())){
						die("No data in resultset: ".$stmt->errno);
					}
					
					$patrolcarArray;
						
					while ($row = $resultset->fetch_assoc()){
						$patrolcarArray[$row['patrolcarId']] =
						$row['statusDesc'];
					}
					
					$stmt->close();
					$resultset->close();
					$mysqli->close();
			?>
			<br><br>
			<table border="1" align="center" width="100%">
				<tr>
					<td colspan="3"><center><strong>Dispatch Patrolcar Panel</strong></center></td>
				</tr>
				
				<?php
				
				foreach($patrolcarArray as $key=>$value){
					
				?>
				<tr>
					<td align="center"><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>"></td>
					<td align="center"><?php echo $key ?></td>
					<td align="center"><?php echo $value ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td height="42" align="center"><input type="reset" name="btnCancel" id="btnCanel" value="Reset" class="jarrellbutton1"></td>
				  <td colspan="2" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" value="Dispatch" class="jarrellbutton2"></td>
				</tr>
			</table>
		</div>
		</form>	
	</fieldset>
</body>
</html>