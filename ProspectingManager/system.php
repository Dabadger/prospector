<?php
require_once 'config.php';
$con = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if ($con->connect_errno) {
    echo "Failed to connect to database!";
}

if(isset($_POST['submit'])) {
    //Check if x is empty
    if(trim($_POST['x']) == "" || is_numeric($_POST['x']) == false)
    {
        header('Location:system.php?msg=nocoord');
    }
    //Check if y is empty
    elseif(trim($_POST['y']) == "" || is_numeric($_POST['y']) == false)
    {
        header('Location:system.php?msg=nocoord');
    }
    //Check if name is empty
    elseif(trim($_POST['systemname']) == "")
    {
        header('Location:system.php?msg=noname');
    }
    else {
        $name = $_POST['systemname'];
        //Check for duplicate system
        if($stmt = $con -> prepare("SELECT id from systems Where name = UPPER(?)")) {
            $nameU = strtoupper($name);
            $stmt -> bind_param("s", $nameU);
            $stmt -> execute();
            $stmt -> store_result();
            $result = $stmt -> num_rows;
            $stmt-> close();
        }
        //If no duplicate, insert new system with success msg. Otherwise return with error msg
        if ($result == 0) {
            if($stmt = $con -> prepare("INSERT INTO systems (sector, name, x, y) VALUES (?, ?, ?, ?)")) {
                $sectorid = $_POST['sectorname'];
                $x = $_POST['x'];
                $y = $_POST['y'];
                $stmt -> bind_param("isii", $sectorid, $name, $x, $y);
                $stmt -> execute();
                $stmt-> close();
            }
            header('Location:system.php?msg=successful');
        }
        else {
            header('Location:system.php?msg=unsuccessful');
        }
    }
}
//Loops through and find sectors to populate selection list
function sectorOptions($con) {
    $list = $con->query("SELECT id, name from sectors ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='sector" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
    }
}
//Loops through and find systems to populate selection list
function systemOptions($con) {
    $list = $con->query("SELECT id, name from systems ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='system" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
    }
}
//Echos message if one exists
function formMessage($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'nocoord') {
            echo "<p class='form unsuc'>Please enter both coordinates.</p>";
        }
        elseif($_GET['msg'] == 'successful') {
            echo "<p class='form suc'>System added!</p>";
        }
        elseif($_GET['msg'] == 'unsuccessful') {
            echo "<p class='form unsuc'>System already exists!</p>";
        }
        elseif($_GET['msg'] == 'noname') {
            echo "<p class='form unsuc'>Please enter a name.</p>";
        }
    }
}
//Echos message if one exists from update page
function updateMessage($con) {
    if(isset($_GET['msgu'])) {
        if($_GET['msgu'] == 'successful') {
            echo "<p class='form suc'>System updated successfully!</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>    
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <title>Prospecting Manager</title>
    </head>    
    <body>
        <?php include 'header.php'; ?>
        <div class='cont'>
            <h2>Create a new system?</h2>
            <form method='post' action='system.php'>
                <div>
                    <p class='form'>System sector:</p><select name='sectorname'>
                        <?php sectorOptions($con); ?>
                    </select>
                </div>
                <div>
                    <p class='form'>System coordiantes:</p><input type='text' name='x' value='x' class='coord'><input type='text' name='y' value='y' class='coord'>
                </div>
                <div>
                    <p class='form'>System name:</p><input type='text' name='systemname'><input type='submit' name='submit' value='Create!'><?php formMessage($con); ?>
                </div>
            </form>
            <h2>Or edit an existing system?</h2>
            <form method='post' action='systemedit.php'>
                <div>
                    <select name='systemname'>
                        <?php systemOptions($con); ?>
                    </select>
                    <input type='submit' name='edit' value='Edit'><?php updateMessage($con); ?>
                </div>
            </form>
        </div>
    </body>  
</html>
<?php $con->close(); ?>