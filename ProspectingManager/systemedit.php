<?php
session_start();
require_once 'config.php';
$con = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if ($con->connect_errno) {
    echo "Failed to connect to database!";
}
//If there is an error msg, reset variables
if(isset($_GET['msg'])) {
    $id = $_SESSION['id'];
    $querysystem = $con -> query("SELECT name, sector, x, y from systems Where id = $id");
    $systemresult = $querysystem -> fetch_assoc();
    $name = $systemresult['name'];
    $sectorid = $systemresult['sector'];
    $x = $systemresult['x'];
    $y = $systemresult['y'];
    $querysec = $con -> query("SELECT name from sectors Where id = $sectorid");
    $sector = $querysec -> fetch_assoc();
    $sector = $sector['name'];
}
//Get details of system if it exists.
elseif(isset($_POST['edit'])) {
    $id = $_POST['systemname'];
    $querysystem = $con -> query("SELECT name, sector, x, y from systems Where id = $id");
    $result = $querysystem -> num_rows; 
    if ($result == 0) {
        header('Location:index.php');
    }
    else {
        $systemresult = $querysystem -> fetch_assoc();
        $name = $systemresult['name'];
        $sectorid = $systemresult['sector'];
        $x = $systemresult['x'];
        $y = $systemresult['y'];
        $querysec = $con -> query("SELECT name from sectors Where id = $sectorid");
        $sector = $querysec -> fetch_assoc();
        $sector = $sector['name'];
        $_SESSION['id'] = $id;
    }
}
elseif(isset($_POST['update'])) {
    //If no x entered stop and return with error msg
    if(trim($_POST['x']) == "" || is_numeric($_POST['x']) == false) {
        header('Location:systemedit.php?msg=nocoord');
    }
    //If no y entered stop and return with error msg
    elseif(trim($_POST['y']) == "" || is_numeric($_POST['y']) == false) {
        header('Location:systemedit.php?msg=nocoord');
    }
    //If no name entered stop and return with error msg
    elseif(trim($_POST['system']) == "") {
        header('Location:systemedit.php?msg=noname');
    }
    //Update system with success msg
    else {
        $name = $_POST['system'];
        $sectorid = $_POST['sectorname'];
        $x = $_POST['x'];
        $y = $_POST['y'];
        $id = $_SESSION['id'];
        if($stmt = $con -> prepare("UPDATE systems SET sector=?, name=?, x=?, y=?  WHERE id=$id")) {
            $stmt -> bind_param("isii", $sectorid, $name, $x, $y);
            $stmt -> execute();
            $stmt-> close();
            header('Location:system.php?msgu=successful');
        }
    }
}
else {
    header('Location:index.php');
}
//Loops through and find sectors to populate selection list
function sectorOptions($con, $sector) {
    $list = $con->query("SELECT id, name from sectors ORDER BY name");
    while($row = $list->fetch_array()) {
        if($row[0] == $sector) {
            echo "<option selected='selected' id='" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
        }
        else {
            echo "<option id='" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
        }
    }
}
//Echos message if one exists
function formMessage($con)  {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'nocoord') {
            echo "<p class='form unsuc'>Please enter both coordinates.</p>";
        }
        if($_GET['msg'] == 'noname') {
            echo "<p class='form unsuc'>Please enter a name.</p>";
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
            <p>System ID: <?php echo $id; ?></p>
            <form method='post' action='systemedit.php'>
                <div>
                    <p class='form'>System sector:</p><select name='sectorname'><?php sectorOptions($con, $sector); ?></select>
                </div>
                <div>
                    <p class='form'>Sector Coordinates:</p><input type='text' name='x' value='<?php echo $x?>' class='coord'><input type='text' name='y' value='<?php echo $y?>' class='coord'>
                </div>
                <div>
                    <p class='form'>System Name:</p><input type='text' name='system' value='<?php echo $name ?>'><input type='submit' name='update' value='Update!'><?php formMessage($con); ?>
                </div>
            </form>
        </div>   
    </body>
</html>
<?php $con->close(); ?>