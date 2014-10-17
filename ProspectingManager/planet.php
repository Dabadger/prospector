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
        header('Location:planet.php?msg=nocoord');
    }
    //Check if y is empty
    elseif(trim($_POST['y']) == "" || is_numeric($_POST['y']) == false)
    {
        header('Location:planet.php?msg=nocoord');
    }
    //Check if name is empty
    elseif(trim($_POST['planetname']) == "")
    {
        header('Location:planet.php?msg=noname');
    }
    //Check if size is empty    
    elseif(trim($_POST['planetsize']) == "" || is_numeric($_POST['planetsize']) == false || $_POST['planetsize'] == 0)
    {
        header('Location:planet.php?msg=nosize');
    }
    else {
        $name = $_POST['planetname'];
        //Check for duplicate system
        if($stmt = $con -> prepare("SELECT id from planets Where name = UPPER(?)")) {
            $nameU = strtoupper($name);
            $stmt -> bind_param("s", $nameU);
            $stmt -> execute();
            $stmt -> store_result();
            $result = $stmt -> num_rows;
            $stmt-> close();
        }
        //If no duplicate, insert new system with success msg. Otherwise return with error msg
        if ($result == 0) {
            if($stmt = $con -> prepare("INSERT INTO planets (system, name, x, y, size) VALUES (?, ?, ?, ?, ?)")) {
                $systemid = $_POST['systemname'];
                $x = $_POST['x'];
                $y = $_POST['y'];
                $size = $_POST['planetsize'];
                $stmt -> bind_param("isiii", $systemid, $name, $x, $y, $size);
                $stmt -> execute();
                $stmt-> close();
            }
            header('Location:planet.php?msg=successful');
        }
        else {
            header('Location:planet.php?msg=unsuccessful');
        }
    }
}
//Loops through and find systems to populate selection list
function systemOption($con) {
    $list = $con->query("SELECT id, name from systems ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='system" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
    }
}
//Loops through and find planets to populate selection list
function planetOptions($con) {
    $list = $con->query("SELECT id, name from planets ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='planet" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
    }
}
//Echos message if one exists
function formMessage($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'successful') {
            echo "<p class='form suc'>Planet added!</p>";
        }
        elseif($_GET['msg'] == 'unsuccessful') {
            echo "<p class='form unsuc'>Planet already exists!</p>";
        }
        elseif($_GET['msg'] == 'nosize') {
            echo "<p class='form unsuc'>Please enter a size greater than 0.</p>";
        }
        elseif($_GET['msg'] == 'nocoord') {
            echo "<p class='form unsuc'>Please enter both coordinates.</p>";
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
        <script>
            //Duplicates size text
            function mirror(x){
                document.getElementById('sizemirror').innerHTML = "x " + x.value;
            }
        </script>
    </head>   
    <body>
        <?php include 'header.php'; ?>
        <div class='cont'>
            <h2>Create a new planet?</h2>
            <form method='post' action='planet.php'>
                <div>
                    <p class='form'>Planet system:</p><select name='systemname'>
                        <?php systemOption($con); ?>
                    </select>
                </div>
                <div>
                    <p class='form'>Planet coordiantes:</p><input type='text' name='x' value='x' class='coord'> <input type='text' name='y' value='y' class='coord'>
                </div>
                    <p class='form'>Planet name:</p><input type='text' name='planetname'>
                <div>
                    <p class='form'>Planet size:</p><input onkeyup='mirror(this)' type='text' name='planetsize' class='coord'><p class='form' id='sizemirror'>x</p><input type='submit' name='submit' value='Create!'><?php formMessage($con); ?>
                </div>
            </form>
            <h2>Or edit an existing planet?</h2>
            <form method='post' action='planetedit.php'>
                <select name='planetname'>
                    <?php planetOptions($con); ?>
                </select>
                <input type='submit' name='edit' value='Edit'><?php updateMessage($con) ?>
            </form>
        </div>
    </body> 
</html>
<?php $con->close(); ?>