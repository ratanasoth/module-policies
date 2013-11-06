<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

session_start() ;

//Module includes
include "./modules/IB PYP/moduleFunctions.php" ;


if (isActionAccessible($guid, $connection2, "/modules/Policies/policies_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/policies_manage.php'>Manage Policies</a> > </div><div class='trailEnd'>Edit Policy</div>" ;
	print "</div>" ;
	
	$updateReturn = $_GET["updateReturn"] ;
	$updateReturnMessage ="" ;
	$class="error" ;
	if (!($updateReturn=="")) {
		if ($updateReturn=="fail0") {
			$updateReturnMessage ="Update failed because you do not have access to this action." ;	
		}
		else if ($updateReturn=="fail1") {
			$updateReturnMessage ="Update failed because a required parameter was not set." ;	
		}
		else if ($updateReturn=="fail2") {
			$updateReturnMessage ="Update failed due to a database error." ;	
		}
		else if ($updateReturn=="fail3") {
			$updateReturnMessage ="Update failed because your inputs were invalid." ;	
		}
		else if ($updateReturn=="fail4") {
			$updateReturnMessage ="Update failed some values need to be unique but were not." ;	
		}
		else if ($updateReturn=="fail5") {
			$updateReturnMessage ="Update failed because your attachment could not be uploaded." ;	
		}
		else if ($updateReturn=="success0") {
			$updateReturnMessage ="Update was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $updateReturnMessage;
		print "</div>" ;
	} 
	
	//Check if school year specified
	$policiesPolicyID=$_GET["policiesPolicyID"];
	if ($policiesPolicyID=="") {
		print "<div class='error'>" ;
			print "You have not specified a policy." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("policiesPolicyID"=>$policiesPolicyID);  
			$sql="SELECT * FROM policiesPolicy WHERE policiesPolicyID=:policiesPolicyID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "The selected policy does not exist." ;
			print "</div>" ;
		}
		else {
			//Let's go!
			$row=$result->fetch() ;
			
			if ($_GET["search"]!="") {
				print "<div class='linkTop'>" ;
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Policies/policies_manage.php&search=" . $_GET["search"] . "'>Back to Search Results</a>" ;
				print "</div>" ;
			}
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/Policies/policies_manage_editProcess.php?policiesPolicyID=$policiesPolicyID&search=" . $_GET["search"] ?>" enctype="multipart/form-data">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td> 
							<b>Scope *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed</i></span>
						</td>
						<td class="right">
							<input readonly name="scope" id="scope" value="<? print $row["scope"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					
					<?
					if ($row["scope"]=="Department") {
						?>
						<tr id='learningAreaRow'>
							<td> 
								<b>Department *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed</i></span>
							</td>
							<td class="right">
								<?
								try {
									$dataSelect=array("gibbonDepartmentID"=>$row["gibbonDepartmentID"]); 
									$sqlSelect="SELECT * FROM gibbonDepartment WHERE gibbonDepartmentID=:gibbonDepartmentID" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								if ($resultSelect->rowCount()!=1) {
									print "<i>Error retreiving department.</i>" ;
								}
								else {
									$rowSelect=$resultSelect->fetch() ;
									print "<input readonly name=\"gibbonDepartmentID\" id=\"gibbonDepartmentID\" value=\"" . $rowSelect["name"] . "\" type=\"text\" style=\"width: 300px\">" ;
								}
								?>
						</tr>
						<?
					}
					?>
					<tr>
						<td> 
							<b>Name *</b><br/>
						</td>
						<td class="right">
							<input name="name" id="name" maxlength=100 value="<? print htmlPrep($row["name"]) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var name = new LiveValidation('name');
								name.add(Validate.Presence);
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Name Short *</b><br/>
						</td>
						<td class="right">
							<input name="nameShort" id="nameShort" maxlength=14 value="<? print htmlPrep($row["nameShort"]) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var nameShort = new LiveValidation('nameShort');
								nameShort.add(Validate.Presence);
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Active *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="active" id="active" style="width: 302px">
								<option <? if ($row["active"]=="Y") { print "selected" ; } ?> value="Y">Y</option>
								<option <? if ($row["active"]=="N") { print "selected" ; } ?> value="N">N</option>
							</select>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Category</b><br/>
						</td>
						<td class="right">
							<input name="category" id="category" maxlength=50 value="<? print htmlPrep($row["category"]) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								$(function() {
									var availableTags = [
										<?
										try {
											$dataAuto=array();  
											$sqlAuto="SELECT DISTINCT category FROM policiesPolicy ORDER BY category" ;
											$resultAuto=$connection2->prepare($sqlAuto);
											$resultAuto->execute($dataAuto);
										}
										catch(PDOException $e) { }
								
										while ($rowAuto=$resultAuto->fetch()) {
											print "\"" . $rowAuto["category"] . "\", " ;
										}
										?>
									];
									$( "#category" ).autocomplete({source: availableTags});
								});
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Description</b><br/>
						</td>
						<td class="right">
							<textarea name='description' id='description' rows=5 style='width: 300px'><? print htmlPrep($row["description"]) ?></textarea>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Type *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed</i></span>
						</td>
						<td class="right">
							<input readonly name="type" id="type" value="<? print $row["type"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Policy *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed</i></span>
						</td>
						<td class="right">
							<div style='text-align: left ;float: right; width: 300px;'>
							<?
							if ($row["type"]=="File") {
								print "<a style='font-weight: bold' href='" . $_SESSION[$guid]["absoluteURL"] . "/" . $row["location"] ."'>" . $_SESSION[$guid]["absoluteURL"] . "/" . $row["location"] . "</a><br/>" ;
							}
							else if ($row["type"]=="Link") {
								print "<a style='font-weight: bold' target='_blank' href='" . $row["location"] ."'>" . $row["location"] . "</a><br/>" ;
							}
							?>
							</div>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Audience</b><br/>
							<span style="font-size: 90%"><i>User role groups who should have view access.<br/></i></span>
						</td>
						<td class="right">
							<? 
							$roleCount=0 ;
							try {
								$dataRoles=array();  
								$sqlRoles="SELECT * FROM gibbonRole ORDER BY name" ; 
								$resultRoles=$connection2->prepare($sqlRoles);
								$resultRoles->execute($dataRoles);
							}
							catch(PDOException $e) { }
							while ($rowRoles=$resultRoles->fetch()) {
								$checked="" ;
								if (is_numeric(strpos($row["gibbonRoleIDList"], $rowRoles["gibbonRoleID"]))) {
									$checked="checked" ;
								}
								print $rowRoles["name"] . " <input $checked type='checkbox' name='gibbonRoleID$roleCount' value='" . $rowRoles["gibbonRoleID"] . "'><br/>" ; 
								$roleCount++ ;
							}
							?>
							<input type="hidden" name="roleCount" value="<? print $roleCount ?>">
						</td>
					</tr>
			
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}
	}
}
?>