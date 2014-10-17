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
    $queryplanet = $con -> query("SELECT name, system, x, y, size from planets Where id = $id");
    $planetresult = $queryplanet -> fetch_assoc();
    $name = $planetresult['name'];
    $systemid = $planetresult['system'];
    $x = $planetresult['x'];
    $y = $planetresult['y'];
    $size = $planetresult['size'];
    $querysys = $con -> query("SELECT name from systems Where id = $systemid");
    $system = $querysys -> fetch_assoc();
    $system = $system['name'];
}
//Get details of planet if it exists.
elseif(isset($_POST['edit'])) {
    $id = $_POST['planetname'];
    $queryplanet = $con -> query("SELECT name, system, x, y, size from planets Where id = $id");
    $result = $queryplanet -> num_rows;    
    if ($result == 0) {
        header('Location:index.php');
    }
    else {
        $planetresult = $queryplanet -> fetch_assoc();
        $name = $planetresult['name'];
        $systemid = $planetresult['system'];
        $x = $planetresult['x'];
        $y = $planetresult['y'];
        $size = $planetresult['size'];
        $querysys = $con -> query("SELECT name from systems Where id = $systemid");
        $system = $querysys -> fetch_assoc();
        $system = $system['name'];
        $_SESSION['id'] = $id;
        $_SESSION['size'] = $size;
    }
}
elseif(isset($_POST['update'])) {
    //If no x entered stop and return with error msg
    if(trim($_POST['x']) == "" || is_numeric($_POST['x']) == false) {
        header('Location:planetedit.php?msg=nocoord');
    }
    //If no y entered stop and return with error msg
    elseif(trim($_POST['y']) == "" || is_numeric($_POST['y']) == false) {
        header('Location:planetedit.php?msg=nocoord');
    }
    //If no name entered stop and return with error msg
    elseif(trim($_POST['planet']) == "") {
        header('Location:planetedit.php?msg=noname');
    }
    //If no size entered stop and return with error msg
    elseif(trim($_POST['planetsize']) == "" || is_numeric($_POST['planetsize']) == false || $_POST['planetsize'] == 0)
    {
        header('Location:planetedit.php?msg=nosize');
    }
    //Update planet with success msg
    else {
        $name = $_POST['planet'];
        $systemid = $_POST['systemname'];
        $x = $_POST['x'];
        $y = $_POST['y'];
        $size = $_POST['planetsize'];
        $id = $_SESSION['id'];
        $check = $_SESSION['size'];
        if($stmt = $con -> prepare("UPDATE planets SET system=?, name=?, x=?, y=?, size=?  WHERE id=$id")) {
            $stmt -> bind_param("isiii", $systemid, $name, $x, $y, $size);
            $stmt -> execute();
            $stmt-> close();
            $change = $check - $size;
            //If size smaller, remove appropriate prospect results
            if($change > 0) {
                for($a=$check-1; $a > $size-1; $a--) {
                    for($b=0; $b < $check; $b++) {
                        $remove = $con -> query("DELETE FROM planetmap where planet = '$id' AND x = '$a' AND y = '$b'");
                    }
                }
            }
        }
        header('Location:planet.php?msgu=successful');
    }
}
else {
    header('Location:index.php');
}
//Loops through and find systems to populate selection list
function systemOptions($con, $system) {
    $list = $con->query("SELECT id, name from systems ORDER BY name");
    while($row = $list->fetch_array()) {
        if($row[0] == $system) {
            echo "<option selected='selected' id='system" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
        }
        else {
            echo "<option id='system" . $row[0] . "' value='" . $row[0] . "'>" . $row[1] . "</option>";
        }
    }
}
//Echos message if one exists
function formMessage($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'nocoord') {
            echo "<p class='form unsuc'>Please enter both coordinates.</p>";
        }
        elseif($_GET['msg'] == 'noname') {
            echo "<p class='form unsuc'>Please enter a name.</p>";
        }
        elseif($_GET['msg'] == 'nosize') {
            echo "<p class='form unsuc'>Please enter a size greater than 0.</p>";
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
            function mirror(){
                var x = document.getElementById('planetsize');
                document.getElementById('sizemirror').innerHTML = x.value;
            }
        </script>
    </head>
    
    <body>
        <?php include 'header.php'; ?>
        <div class='cont'>
            <p>Planet ID: <?php echo $id; ?></p>
            <form method='post' action='planetedit.php'>
                <div>
                    <p class='form'>Planet system:</p><select name='systemname'>
                        <?php systemOptions($con, $system); ?>
                    </select>
                </div>
                <div>
                    <p class='form'>Planet Coordinates:</p><input type='text' name='x' value='<?php echo $x?>' class='coord'><input type='text' name='y' value='<?php echo $y?>' class='coord'>
                </div>
                <div>
                    <p class='form'>Planet Name: </p><input type='text' name='planet' value='<?php echo $name ?>'>
                </div>
                <p class='unsuc'>Warning! Reducing size may delete any propsecting results from higher(numerically) coordinates.</p>
                <div>
                    <p class='form'>Planet size: </p><input onkeyup='mirror()' type='text' class='sizemirror' name='planetsize' id='planetsize' value='<?php echo $size ?>'><p class='form' id='sizemirror'> x <?php echo $size ?></p> <input type='submit' name='update' value='Update!'><?php formMessage($con); ?>
                </div>
            </form>
        </div>   
    </body>
</html>
<?php $con->close(); ?>