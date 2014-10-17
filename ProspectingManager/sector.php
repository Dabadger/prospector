<?php
require_once 'config.php';
$con = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if ($con->connect_errno) {
    echo "Failed to connect to database!";
}

if(isset($_POST['submit'])) {
    $name = $_POST['sectorname'];
    $trim = trim($name);
    //If no name entered stop and return with error msg
    if($trim == "") {
        header('Location:sector.php?msg=noname');
    }
    else {
        //Check for duplicate sector
        if($stmt = $con -> prepare("SELECT id from sectors Where name = UPPER(?)")) {
            $nameU = strtoupper($name);
            $stmt -> bind_param("s", $nameU);
            $stmt -> execute();
            $stmt -> store_result();
            $result = $stmt -> num_rows;
            $stmt-> close();
        }
        //If no duplicate, insert new sector with success msg. Otherwise return with error msg
        if ($result == 0) {
            if($stmt = $con -> prepare("INSERT INTO sectors (name) VALUES (?)")) {
                $stmt -> bind_param("s", $name);
                $stmt -> execute();
                $stmt-> close();
            }
            header('Location:sector.php?msg=successful');
        }
        else {
            header('Location:sector.php?msg=unsuccessful');
        }
    }
}

//Loops through and find sectors to populate selection list
function formOptions ($con) {
    $list = $con->query("SELECT id, name from sectors ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
    }
}

//Echos message if one exists
function formMessage ($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'successful') {
            echo "<p class='form suc'>Sector added!</p>";
        }
        elseif($_GET['msg'] == 'unsuccessful') {
            echo "<p class='form unsuc'>Sector already exists!</p>";
        }
        elseif($_GET['msg'] == 'noname') {
            echo"<p class='form unsuc'>Please enter a name.</p>";
        }
    }
}

//Echos message if one exists from update page
function updateMessage ($con) {
    if(isset($_GET['msgu'])) {
        if($_GET['msgu'] == 'successful') {
            echo "<p class='form suc'>Sector updated successfully!";
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
            <h2>Create a new sector?</h2>
            <form method='post' action='sector.php'>
                <p class='form'>Sector name: </p><input type='text' name='sectorname'>
                <input type='submit' name='submit' value='Create!'><?php formMessage($con); ?>
            </form>
            <h2>Or edit an existing sector?</h2>
            <form method='post' action='sectoredit.php'>
                <div>
                    <select name='sectorname'>
                        <?php formOptions($con); ?>
                    </select>
                    <input type='submit' name='edit' value='Edit'><?php updateMessage($con); ?>
                </div>
            </form>
        </div>   
    </body>
</html>
<?php $con->close(); ?>