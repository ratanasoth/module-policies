<?php
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

include '../../functions.php';
include '../../config.php';

include './moduleFunctions.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/policies_manage_add.php&search='.$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $scope = $_POST['scope'];
    $gibbonDepartmentID = null;
    if (isset($_POST['gibbonDepartmentID'])) {
        $gibbonDepartmentID = $_POST['gibbonDepartmentID'];
    }
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $active = $_POST['active'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $link = $_POST['link'];
    $gibbonRoleIDList = '';
    for ($i = 0; $i < $_POST['roleCount']; ++$i) {
        if (isset($_POST['gibbonRoleID'.$i])) {
            if ($_POST['gibbonRoleID'.$i] != '') {
                $gibbonRoleIDList .= $_POST['gibbonRoleID'.$i].',';
            }
        }
    }
    if (substr($gibbonRoleIDList, -1) == ',') {
        $gibbonRoleIDList = substr($gibbonRoleIDList, 0, -1);
    }

    if ($scope == '' or ($scope == 'Department' and is_null($gibbonDepartmentID)) or $name == '' or $nameShort == '' or $active == '' or $type == '' or ($type == 'Link' and $link == '')) {
        //Fail 3
        $URL = $URL.'&return=error3';
        header("Location: {$URL}");
    } else {
        $partialFail = false;
        if ($type == 'Link') {
            $location = $link;
        } else {
            //Check extension to see if allowed
            try {
                $ext = explode('.', $_FILES['file']['name']);
                $dataExt = array('extension' => end($ext));
                $sqlExt = 'SELECT * FROM gibbonFileExtension WHERE extension=:extension';
                $resultExt = $connection2->prepare($sqlExt);
                $resultExt->execute($dataExt);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            if ($resultExt->rowCount() != 1) {
                $partialFail = true;
            } else {
                //Attempt file upload
                $time = time();
                if ($_FILES['file']['tmp_name'] != '') {
                    //Check for folder in uploads based on today's date
                    $path = $_SESSION[$guid]['absolutePath'];
                    if (is_dir($path.'/uploads/'.date('Y', $time).'/'.date('m', $time)) == false) {
                        mkdir($path.'/uploads/'.date('Y', $time).'/'.date('m', $time), 0777, true);
                    }
                    $unique = false;
                    while ($unique == false) {
                        $suffix = randomPassword(16);
                        $location = 'uploads/'.date('Y', $time).'/'.date('m', $time).'/policy_'.str_replace(' ', '_', trim($name))."_$suffix".strrchr($_FILES['file']['name'], '.');
                        if (!(file_exists($path.'/'.$location))) {
                            $unique = true;
                        }
                    }

                    if (!(move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$location))) {
                        //Fail 5
                        $URL = $URL.'&return=error5';
                        header("Location: {$URL}");
                    }
                } else {
                    $partialFail = true;
                }
            }
        }

        if ($partialFail == true) {
            //Fail 5
            $URL = $URL.'&return=error5';
            header("Location: {$URL}");
            exit();
        } else {
            //Write to database
            try {
                $data = array('scope' => $scope, 'gibbonDepartmentID' => $gibbonDepartmentID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'description' => $description, 'type' => $type, 'location' => $location, 'gibbonRoleIDList' => $gibbonRoleIDList, 'gibbonPersonIDCreator' => $_SESSION[$guid]['gibbonPersonID'], 'timestampCreated' => date('Y-m-d H:i:s'));
                $sql = 'INSERT INTO policiesPolicy SET scope=:scope, gibbonDepartmentID=:gibbonDepartmentID, name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, type=:type, location=:location, gibbonRoleIDList=:gibbonRoleIDList, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreated=:timestampCreated';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

            //Success 0
            $URL = $URL.'&return=success0&editID='.$AI;
            header("Location: {$URL}");
        }
    }
}
