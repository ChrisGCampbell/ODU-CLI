<?php
#######################################
# Class file for ActionReports Software
# Updated by Christopher Campbell
# 12/10/2016
#######################################
define("GROUPFILE", "DataFiles/known_groups.txt");
define("GROUPINCIDENTS", "DataFiles/known_incidents.txt");
define("ACTIONITEMS", "DataFiles/Actionitems.xml");
define("AIREPORTS", "DataFiles/aireports.xml");
define("REGISTERED_USERS", "DataFiles/user_file.xml");
error_reporting(0);



final class ActionReports {
    private $_firstname;
    private $_lastname;
    private $_emailAddress;


    // constructor
    public function __construct()
    {
        $this->_firstname = $_SESSION['firstname'];
        $this->_lastname = $_SESSION['lastname'];
        $this->_emailAddress = $_SESSION['email'];

    }

    public function getFullName()
    {
        return $this->_firstname . " " . $this->_lastname;
    }


    public function getUserEmailAddress()
    {
        return $this->_emailAddress;
    }


    public function displayGroupSelectionList()
    {
        $groupNames = [];
        $fileHandler = fopen(GROUPFILE, "r");
        $groupCount = fgets($fileHandler);

        try {
            if (!$fileHandler) {
                throw new customException("Failed to open groupfile on ");
            }
        }
        catch(customException $e) {
            echo $e->errorMessage();
        }

        echo "<form id='groupList' method='POST' action='";echo $_SERVER['PHP_SELF'] . "'>";
        echo 'Select Group:';
        for ($i = 0; $i < $groupCount; $i++) {
            echo '<input type="radio" name="group" id="group" value="'; echo  $groupNames[$i] = fgets($fileHandler); echo  '">' . $groupNames[$i];
        }
        echo '&nbsp<input type="submit" value="submit" name="submitGroup"></form>';
    }

    ################################################
    # Function displayProjectIncidentList()
    # parameters 0
    # Prints to display a list of action item
    # User is able to select a item
    # Sends a url with parameters to identify item
    # returns (nothing)
    #################################################
    public function displayProjectIncidentList()
    {
        if( !isset($_POST['group']) ) {
            echo "You did not select a group.<br/><a href='actionitems.php'>Back to Group Area</a>";
            exit();
        }
        else {
            $pgroup = $_SESSION['pgroup'];
            $txtfile = file_get_contents(GROUPINCIDENTS);

                try {
                    if ($txtfile == false) {
                        throw new customException("Failed to open Group Incidents on ");
                    }
                }
                catch(customException $e) {
                    echo $e->errorMessage();
                }

            $rows = explode("\n", $txtfile);
            $pid = [];

            for ($i = 0; $i < count($rows); $i++) {
                if ( strpos( $rows[$i], $pgroup ) !== false ) {
                    $projectIncidents = explode( "|", $rows[$i] );
                    break;
                }
            }

            if ( empty($projectIncidents) ) {
                echo "No current listings for " . $pgroup;
            }
            else {
                echo "List of <b>".$pgroup."s</b> <br/>";
                echo "----------------------";
                echo "<table width='550'>";
                //Project Incidents array beings at index 1
                for ($i = 1; $i < count($projectIncidents); $i++) {
                    echo '<tr> <td> <a href="?pid='.$projectIncidents[$i].'&amp;pgroup='.$pgroup.'">'.$projectIncidents[$i].'</a> </td></tr>';
                }
                echo "</table>";
                echo "----------------------";
                echo "<br/><br/><a href='actionitems.php'>Back to Group Selection Area.</a>";
            }
        }
    }


    public function displayActionItemLimitedView()
    {
        $pid = $_SESSION['pid'];
        $pgroup = $_SESSION['pgroup'];
        $aixml = simplexml_load_file(ACTIONITEMS);
        $timearray = []; //array to hold and sort PID by dates

        try {
            if (!$aixml)
            {
                throw new customException("Failed to open Action Items file on ");
            }
        }
        catch(customException $e) {
            echo $e->errorMessage();
        }

        echo "<b>List of Action Items for " . $pid . " in the group "  .$pgroup ."</b></br>";
        echo "<hr>";

        echo "<table width='1000'><th width='350' align='left'>Action Item</th><th width='250' align='center'>Owner</th><th align='left'>Date Created</th><th align='left'>Description</th>";

        //Find PID dates
        for ( $i = 0; $i < count($aixml); $i++ )
        {
            if ( $aixml->Actionitem[$i]->PGROUP == $pgroup && $aixml->Actionitem[$i]->PID == $pid )
            {
                $timearray[$i] = strtotime( $aixml->Actionitem[$i]->CREATED );
            }
        }

        //Sort PID Date Array
        arsort($timearray);

        //output sorted PID by dates
        foreach ( $timearray as $key => $val )
        {
            echo "<tr> <td width='250'>" . $aixml->Actionitem[$key]->AIACRO . "&nbsp";
            echo "<a href='?editAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'> <input type='button' value='edit' name='editAI'> </a>";
            echo "<a href='?viewAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'> <input type='button' value='view' name='viewAI'> </a>";
            echo "<a href='?reportAI=true&aiacronym=" . $aixml->Actionitem[$key]->AIACRO . "'> <input type='button' value='report' name='reportAI'> </a>";

            echo "<td width='250' align='center'>" . $aixml->Actionitem[$key]->OWNER . "</td>";
            echo "<td width='250'>" . $aixml->Actionitem[$key]->CREATED . "</td>";
            echo "<td>" . substr($aixml->Actionitem[$key]->DESCRIPTION,0,30) . "</td></tr> <tr><td></td></tr> <tr ><td></td></tr>";
        }   echo "</table><br><br>";


        echo "<form method='POST' action='";echo $_SERVER['PHP_SELF'];echo "'> 
        <input type='hidden' name='pid' value='{$pid}'>
        <input type='hidden' name='pgroup' value='{$pgroup}'>
        <input type='submit' name='newActionItem' value='Create New Action Item'></form>";
        echo "<br/><br/><a href='actionitems.php'>Back to Group Selection Area</a><br><br>";
    }

    ################################################
    # Function editAI()
    # parameters none
    # Allows users to edit an action item
    # Fields are limited for editing. User
    # must be logged in to utilize this functionality
    # returns (nothing)
    #################################################
    public function editAI()
    {
        $aiacronym = $_SESSION['aiacronym'];
        $aixml = simplexml_load_file(ACTIONITEMS);

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
                $description = $aixml->Actionitem[$i]->DESCRIPTION;
                $pgroup = $aixml->Actionitem[$i]->PGROUP;
                $pid = $aixml->Actionitem[$i]->PID;
                $owner = $aixml->Actionitem[$i]->OWNER;
                $created = $aixml->Actionitem[$i]->CREATED;
                $deadline = $aixml->Actionitem[$i]->DEADLINE;
                $responsible = $aixml->Actionitem[$i]->RESPONSIBLE;
                $rationale = $aixml->Actionitem[$i]->RATIONALE;
                break;
            }
        }

        echo "<p>Group: " . $pgroup . "</p>";
        echo "<p>Incident: " . $pid . "</p>";
        echo "<p>Owner: {$owner}</p>";
        echo "<p>Date Created: {$created}</p>";
        echo "<p>Action Item: {$aiacronym}</p>";
        echo "<table width='800'>";
        echo "<form method='POST' action=\"";
        echo $_SERVER['PHP_SELF'];
        echo "\">";
        $data = $this->getTagData("NRESPONSIBLE", 'aireport');

        echo "<tr><td width='50'>Responsible:</td><td><select name='NRESPONSIBLE'>";
        for ($i = 0; $i < count($data); $i++) {
            echo "<option  value='$data[$i]'>$data[$i]</option>";
        }
        echo "</select></td></tr>";
        echo    "<tr><td width='50'>Deadline:</td><td width='100'> <input type='text' name='deadline' value='{$deadline}'></td></tr>
            <tr><td width='50'>Rationale:</td><td width='100'> <textarea rows='5' cols='50' name='rationale'>{$rationale}</textarea></td></tr>
            <tr><td width='50'>Description:</td><td width='100'><textarea rows=5' cols='50' name='description'>{$description}</textarea></td></tr>
            <input type='hidden' name='aiacronym' value='";
        echo $aiacronym;
        echo "'>";
        echo '<input type="hidden" name="pid" value="';
        echo $pid;
        echo '">';
        echo '<input type="hidden" name="pgroup" value="';
        echo $pgroup;
        echo '">
            <tr><td width=\'50\'></td><td><input type="submit" value="submit" name="submitEditAI"></td></tr>
          </form></table>';

        echo "<br/><br/><a href='actionitems.php'>Back to Group Selection Area</a><br><br>";

    }

    ######################################################################
    # Function saveToFile()
    # Writes to file
    # parameters: zero
    #
    # must be logged in to utilize this functionality
    #
    # returns (nothing)
    ######################################################################
    public function saveToFile()
    {
        $aixml = simplexml_load_file(ACTIONITEMS);

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->AIACRO == trim($_POST['aiacronym'])) {
                echo "found";
                $aixml->Actionitem[$i]->DESCRIPTION = trim($_POST['description']);
                $aixml->Actionitem[$i]->RATIONALE = trim($_POST['rationale']);
                $aixml->Actionitem[$i]->DEADLINE = trim($_POST['deadline']);
                $aixml->Actionitem[$i]->RESPONSIBLE = trim($_POST['responsible']);
                $aixml->asXML(ACTIONITEMS);
            }
        }

        echo "File Edited Successfully!<br/><br/>";
        echo '<a href=?pid=' . trim($_POST['pid']) . '&pgroup=' . trim($_POST['pgroup']) . '>Back To List Area</a>';
    }


    public function newActionItemForm($pincident, $projectgroup)
    {

        $pid = trim($pincident);
        $PGROUP = trim($projectgroup);
        $aixml = simplexml_load_file(ACTIONITEMS);
        $aiacroList = [];
        $ID = rand(1000, 9999);
        $highest = [];

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->PID == $pid) {
                array_push($aiacroList, $aixml->Actionitem[$i]->AIACRO);
            }
        }


        for ($i = 0; $i < count($aiacroList); $i++) {
            $position = 0;
            $pilength = 0;
            $positionOfIncident = 0;
            $aia = $aiacroList[$i]; //example fdsfd-project1-12
            $position = stripos($aia, $pid, 0);//gets the position of word project1
            $pilength = strlen($pid); //gets the length of project1
            $positionOfIncident = $position + $pilength + 1;//finds the position of the incident # (ex. project1-)
            $substring = substr($aia, 0, $positionOfIncident); //assigns project1- to variable
            $oldvalue = intval(substr($aia, $positionOfIncident, 3));//gets the current incident #
            array_push($highest, intval($oldvalue));
        }
        $max = max($highest);//find the highest incident # last assigned
        $newvalue = $max + 1;//returns the number after project1- + 1
        $newstring = $substring . $newvalue;
        $objDateTime = new DateTime('NOW');
        $data = [];

        echo "---------------------------------------------------------------------<br/>";
        echo "CREATE A NEW ACTION ITEM IN THE AREA OF <BR/>";
        echo "Group: " . $PGROUP . "<br/>";
        echo "Project: " . $pid . "<br/>";
        echo "----------------------------------------------------------------------";
        echo "<br/>";

        echo "Please input the fields to add a new action item below:<br/>";
        //add new action Item coming soon.
        echo "<table width='800'>";
        echo "<form method='POST' action=\"";
        echo $_SERVER['PHP_SELF'];
        echo "\">
            <tr><td width='50'>ID:</td><td width='100'><input type='text' name='ID'  value='{$ID}' disabled></td></tr>
            <input type='hidden' name='ID' value='{$ID}'>
           
            <tr><td width='50'>Group:</td><td width='100'><input type='text' name='PGROUP'  value='{$PGROUP}' disabled></td></tr>
            <input type='hidden' name='PGROUP'  value='{$PGROUP}'>
            
            <tr><td width='50'>PID:</td><td width='100'><input type='text' name='PID'  value='$pid' disabled></td></tr>
            <input type='hidden' name='PID'  value='{$pid}'>
            
            <tr><td width='50'>Number:</td><td width='100'><input type='text' name='NUMBER'  value='1' disabled></td></tr>
            <input type='hidden' name='NUMBER'  value='1'>
          
            <tr><td width='50'>AIACRO:</td><td width='100'><input type='text' name='AIACRO'  value='$newstring'></td></tr>
            <input type='hidden' name='AIACRO'  value='{$newstring}'>
            
            <tr><td width='50'>Owner:</td><td width='100'><input type='text' name='OWNER' value='{$_SESSION['firstname']} {$_SESSION['lastname']}'></td></tr>
            <input type='hidden' name='OWNER'  value='{$_SESSION['firstname']} {$_SESSION['lastname']}'>
            
            <tr><td width='50'> Responsible:</td><td width='100'>";

        $data = $this->getTagData('NRESPONSIBLE', 'aireport');

        echo "<select name='NRESPONSIBLE'>";
        for ($i = 0; $i < count($data); $i++) {
            echo "<option  value='$data[$i]'>$data[$i]</option>";
        }
        echo "</select></td></tr>";

        echo "<tr><td width='50'>Created:</td><td width='100'><input type='text' name='CREATED' value='{$objDateTime->format('m/d/Y')}'></td></tr>
              <input type='hidden' name='CREATED'  value='";

        echo $objDateTime->format('m/d/Y');

        echo "'><tr><td width='50'>Deadline:</td><td width='100'><input type='text' name='DEADLINE' value='";

        echo $objDateTime->format('m/d/Y');

        echo "'></td></tr>";

        echo "<tr><td width='50'>Status:</td><td width='100'><input type='text' name='AISTATUS' value=''></td></tr>";


        echo "<tr><td width='50'>Description:</td><td width='100'><textarea row='2' cols='40' name='DESCRIPTION'></textarea></td></tr>";

        echo "<tr><td width='50'>Rationale:</td><td width='100'><textarea row='2' cols='40' name='RATIONALE'></textarea></td></tr>";

        $data = [];
        $data = $this->getTagData('PID', 'actionitem', $pid, 'AIACRO');

        echo "<tr><td width='50'>Dependency:</td><td width='100'><select name='AIDEPENDENCY'>";

        for ($i = 0; $i < count($data); $i++) {
            echo "<option  value='$data[$i]'>$data[$i]</option>";
        }
        echo "</select></td></tr>";

        echo " <tr><td width='50'></td><td width='100'><input type='submit' name ='submitAddedNewActionItem' value='Submit'></td></tr>
               </form></table>";

        echo "<br/><br/><a href='actionitems.php'>Back to Group Selection Area</a><br><br>";
    }


    ######################################################################
    # Function getTagData(tagElement, entitytype, [searchvalue], [returnvalue])
    # This is a universal function that filters values from an xml file
    #
    # Search:
    # parameters: must specify a tag element from xml file along with the entity type
    #             search value and return type are optional.
    # For example to extract the list of owner names in the actions items file
    # call $this->getTagData('OWNER', 'actionitem'), where in this case
    # <OWNER> is the tagElement and this tag belongs to an actionitem.
    #
    # Filter:
    # To filter for a specify value within a file you must add
    # tagElement, entitytype, searchvalue and returnvalue parameters.
    # For example to filter a list of 'OWNERS' that belong to the 'Project4'
    # group within the 'actionitem' file you would call the function like this:
    # call $this->getTagData('PID', 'actionitem', 'project4', 'OWNER')
    #
    # must be logged in to utilize this functionality
    #
    # returns (array)
    ######################################################################
    public function getTagData($tag, $type, $searchvalue=NULL, $returnvalue=NULL)
    {
        if($type == 'aireport') {$root = "Aireport"; $filename = AIREPORTS;}
        if($type == 'actionitem') { $root = "Actionitem"; $filename = ACTIONITEMS;}

        $xmlfile = simplexml_load_file($filename);
        $data = [];

        try {
            if (!$xmlfile) {
                throw new customException("Failed to open xml to retrieve xml tags on ");
            }
        }
        catch(customException $e) {
            echo $e->errorMessage();
        }

        if($searchvalue == NULL && $returnvalue == NULL){
            for ($i = 0; $i < count($xmlfile); $i++) {
                array_push($data, $xmlfile->{$root}[$i]->{$tag});
            }
        }
        else {
            for ($i = 0; $i < count($xmlfile); $i++) {
                if($xmlfile->{$root}[$i]->$tag == $searchvalue)
                    array_push($data, $xmlfile->{$root}[$i]->$returnvalue);
            }
        }

        return $data;
    }

    public function addNewAIToFile()
    {

        $pid = trim($_POST['PID']);
        $pgroup = trim($_POST['PGROUP']);
        $FILENAME = ACTIONITEMS;

        if (file_exists($FILENAME)) {
            $aifile = fopen($FILENAME, 'r+') or die("cant open file");
            $NAI = '<Actionitem>' . PHP_EOL;
            $NAI .= "<ID>" . $_POST['ID'] . "</ID>" . PHP_EOL;
            $NAI .= "<PGROUP>" . $_POST['PGROUP'] . "</PGROUP>" . PHP_EOL;
            $NAI .= "<PID>" . $_POST['PID'] . "</PID>" . PHP_EOL;
            $NAI .= "<NUMBER>" . $_POST['NUMBER'] . "</NUMBER>" . PHP_EOL;
            $NAI .= "<AIACRO>" . $_POST['AIACRO'] . "</AIACRO>" . PHP_EOL;
            $NAI .= "<OWNER>" . $_POST['OWNER'] . "</OWNER>" . PHP_EOL;
            $NAI .= "<NRESPONSIBLE>" . $_POST['NRESPONSIBLE'] . "</NRESPONSIBLE>" . PHP_EOL;
            $NAI .= "<CREATED>" . $_POST['CREATED'] . "</CREATED>" . PHP_EOL;
            $NAI .= "<DEADLINE>" . $_POST['DEADLINE'] . "</DEADLINE>" . PHP_EOL;
            $NAI .= "<DESCRIPTION>" . $_POST['DESCRIPTION'] . "</DESCRIPTION>" . PHP_EOL;
            $NAI .= "<RATIONALE>" . $_POST['RATIONALE'] . "</RATIONALE>" . PHP_EOL;
            $NAI .= "<AIDEPENDENCY>" . $_POST['AIDEPENDENCY'] . "</AIDEPENDENCY>" . PHP_EOL;
            $NAI .= "<AISTATUS>" . $_POST['AISTATUS'] . "</AISTATUS>" . PHP_EOL;
            $NAI .= "</Actionitem>" . PHP_EOL;
            $NAI .= "</ACTIONITEMS>";

            fseek($aifile, -14, SEEK_END);
            fwrite($aifile, $NAI);
            fclose($aifile);

            echo "Action Item added!<br/><br/>";
            echo '<a href=?pid=' . $pid . '&pgroup=' . $pgroup . '>Back To List Area</a>';
        }
    }


    public function addNewReportToFile()
    {

        //$pid = trim($_POST['PID']);
        //$pgroup = trim($_POST['PGROUP']);
        $FILENAME = AIREPORTS;

        if (file_exists($FILENAME)) {
            $airfile = fopen($FILENAME, 'r+') or die("cant open file");
            $NAI = '<Aireport>' . PHP_EOL;
            $NAI .= "<ID>" . $_POST['ID'] . "</ID>" . PHP_EOL;
            $NAI .= "<OWNER>" . $_POST['OWNER'] . "</OWNER>" . PHP_EOL;
            $NAI .= "<DATE>" . $_POST['DATE'] . "</DATE>" . PHP_EOL;
            $NAI .= "<REPORT>" . $_POST['REPORT'] . "</REPORT>" . PHP_EOL;
            $NAI .= "<NDESCRIPTION>" . $_POST['NDESCRIPTION'] . "</NDESCRIPTION>" . PHP_EOL;
            $NAI .= "<NDEADLINE>" . $_POST['NDEADLINE'] . "</NDEADLINE>" . PHP_EOL;
            $NAI .= "<NRESPONSIBLE>" . $_POST['NRESPONSIBLE'] . "</NRESPONSIBLE>" . PHP_EOL;
            $NAI .= "<STATUS>" . $_POST['STATUS'] . "</STATUS>" . PHP_EOL;
            $NAI .= "</Aireport>" . PHP_EOL;
            $NAI .= "</AIREPORTS>";

            fseek($airfile, -14, SEEK_END);
            fwrite($airfile, $NAI);
            fclose($airfile);

            echo "Report added!<br/><br/>";
            echo '<a href=?pid=' . $pid . '&pgroup=' . $pgroup . '>Back To List Area</a>';
        }
    }

    public function addAiReportForm($ai)
    {

        $actionitem = trim($ai);
        $objDateTime = new DateTime('NOW');
        $ID = rand(1000, 9000);
        $responsibleUsers = [];
        $actionitemxml = simplexml_load_file(ACTIONITEMS);

        for ($j = 0; $j < count($actionitemxml); $j++) {
            if ($actionitemxml->Actionitem[$j]->AIACRO == $actionitem) {
                echo "Action item: " . $actionitemxml->Actionitem[$j]->AIACRO . "<br/>";
                echo "Group: " . $actionitemxml->Actionitem[$j]->PGROUP . "<br/>";
                echo "Incident: " . $actionitemxml->Actionitem[$j]->PID . "<br/>";
                echo "Owner: " . $actionitemxml->Actionitem[$j]->OWNER . "<br/>";
                echo "Date Created: " . $actionitemxml->Actionitem[$j]->CREATED . "<br/><br/>";
            }
        }

        echo "Please input the fields to add a new report below:<br/>";
        //add new action Item coming soon.
        echo "<table width='800'>";
        echo "<form method='POST' action=\"";
        echo $_SERVER['PHP_SELF'];
        echo "\">
            
            <tr><td width='50'>ID:</td><td width='100'><input type='text' name='ID'  value='{$ID}' disabled></td></tr>
            <input type='hidden' name='ID' value='{$ID}'>
            
            <tr><td width='50'>Authors:</td><td width='100'><input type='text' name='OWNER'  value='{$_SESSION['firstname']} {$_SESSION['lastname']}' disabled></td></tr>
            <input type='hidden' name='OWNER'  value='{$_SESSION['firstname']} {$_SESSION['lastname']} ' >
            
            <tr><td width='50'>Date:</td><td width='100'><input type='text' name='DATE' value='{$objDateTime->format('m/d/Y')}' disabled></td></tr>
            <input type='hidden' name='DATE'  value='{$objDateTime->format('m/d/Y')}'>
            
            <tr><td width='50'>Report Number:</td><td width='100'><input type='text' name='REPORT'  value='1' disabled></td></tr>
            <input type='hidden' name='REPORT'  value='1'>
           
            <tr><td width='50'>Status:</td><td width='100'><select name='STATUS'><option value='Active'>Active</option><option value='Inactive'>Inactive</option></select></td></tr>
           
           <tr><td width='50'>New Description:</td><td width='100'><textarea row='2' cols='40' name='NDESCRIPTION'></textarea></td></tr>
           
           <tr><td width='50'>New Responsible:</td><td width='100'>";

        $data = [];
        $data = $this->getTagData('NRESPONSIBLE','aireport');

        echo "<select>";
        for ($i = 0; $i < count($data); $i++) {
            echo "<option name='NRESPONSIBLE' value='{$data[$i]}'>$data[$i]</option>";
        }
        echo "</select>";
        echo "</td></tr>";

        echo "<tr><td width='50'>New Deadline:</td><td width='100'><input class='datepicker' type='text' id='datepicker' name='DEADLINE' value=''></td></tr>";

        echo '<tr><td width="50"></td><td width="100"><input type="submit" name="submitNewReport" value="Submit"></td></tr>
           </form></table>';

        echo '<br><br><br><br><a href=?pid=' . $_SESSION['pid'] . '&pgroup=' . $_SESSION['pgroup'] . '>Back To List Area</a>';

    }


    public function viewAI()
    {

        $aiacronym = trim($_GET['aiacronym']);
        $aixml = simplexml_load_file(ACTIONITEMS);
        $aireportxml = simplexml_load_file(AIREPORTS);
        $numofreports = 0;

        for ($i = 0; $i < count($aixml); $i++) {
            if ($aixml->Actionitem[$i]->AIACRO == $aiacronym) {
                $id = trim($aixml->Actionitem[$i]->ID);
                $number = $aixml->Actionitem[$i]->NUMBER;
                $description = $aixml->Actionitem[$i]->DESCRIPTION;
                $pgroup = $aixml->Actionitem[$i]->PGROUP;
                $pid = $aixml->Actionitem[$i]->PID;
                $owner = $aixml->Actionitem[$i]->OWNER;
                $created = $aixml->Actionitem[$i]->CREATED;
                $deadline = $aixml->Actionitem[$i]->DEADLINE;
                $responsible = $aixml->Actionitem[$i]->RESPONSIBLE;
                $rationale = $aixml->Actionitem[$i]->RATIONALE;
                break;
            }
        }

        echo "<h3>".$aiacronym . " </h3><br/>";
        echo "<b>ID:</b> " . $id . "<br/>";
        echo "<b>Project Group:</b> " . $pid . "<br/>";
        echo "<b>Number:</b> " . $number . "<br/>";
        echo "<b>Owner:</b> " . $owner . "<br/>";
        echo "<b>Responsible:</b> " . $responsible . "<br/>";
        echo "<b>Created:</b> " . $created . "<br/>";
        echo "<b>Deadline:</b> " . $deadline . "<br/>";
        echo "<b>Description:</b> " . $description . "<br/>";
        echo "<b>Rationale:</b> " . $rationale . "<br/><br/>";

        echo "Reports on this Action Item:<br/>";
        for ($i = 0; $i < count($aireportxml); $i++) {
            if ($aireportxml->Aireport[$i]->ID == $id) {
                $numofreports = numofreports + 1;
                echo "<b>ID:</b> " . $airid = $aireportxml->Aireport[$i]->ID . "<br/>";
                echo "<b>Owner:</b> " . $airowner = $aireportxml->Aireport[$i]->OWNER . "<br/>";
                echo "<b>Date:</b> " . $airdate = $aireportxml->Aireport[$i]->DATE . "<br/>";
                echo "<b>Report:</b> " . $airreport = $aireportxml->Aireport[$i]->REPORT . "<br/>";
                echo "<b>Description:</b> " . $airndescription = $aireportxml->Aireport[$i]->NDESCRIPTION . "<br/>";
                echo "<b>Deadline:</b> " . $airndeadline = $aireportxml->Aireport[$i]->NDEADLINE . "<br/>";
                echo "<b>Responsible:</b> " . $airnresponsible = $aireportxml->Aireport[$i]->NRESPONSIBLE . "<br/>";
                echo "<b>Status:</b> " . $airstatus = $aireportxml->Aireport[$i]->STATUS;
                echo "<br/><br/>";
            }
        }
        if ($numofreports == 0) {
            echo "<b>Currently no reports on this item.</b><br/><br/>";
        }
        echo '<a href=?pid=' . $pid . '&pgroup=' . $pgroup . '>Back To List of Action Items</a>';
    }
}


final class customException extends Exception {
    public function errorMessage() {
        $currentTime = date('H:i, jS F Y');
        $errorfile = fopen("DataFiles/error-file.txt", "a");
        $errormessage = $this->getMessage() . " " . $currentTime;
        fwrite($errorfile,$errormessage);
        fclose($errorfile);
        echo "An error has occurred. The error has been recorded and sent to the system administror.";
        exit();
    }
}