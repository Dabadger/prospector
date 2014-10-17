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
    $name = $_SESSION['name'];
}
//Get name of sector if it exists
elseif(isset($_POST['edit'])) {
    $id = $_POST['sectorname'];
    $querysector = $con -> query("SELECT name from sectors Where id = $id");
    $result = $querysector -> num_rows;
    if ($result == 0) {
        header('Location:index.html');
    }
    else {
        $name = $querysector -> fetch_assoc();
        $name = $name['name'];
        $_SESSION['id'] = $id;
    }
}
elseif(isset($_POST['update'])) {
    $name = $_POST['sector'];
    $id = $_SESSION['id'];
    $trim = trim($name);
    //If no name entered stop and return with error msg and sector name
    if($trim == "") {
        $querysector = $con -> query("SELECT name from sectors Where id = $id");
        $name = $querysector -> fetch_assoc();
        $name = $name['name'];
        $_SESSION['name'] = $name;
        header('Location:sectoredit.php?msg=noname');
    }
    //Update sector with success msg
    else {
        $id = $_SESSION['id'];
        if($stmt = $con -> prepare("UPDATE sectors SET name=? WHERE id=$id")) {
            $stmt -> bind_param("s", $name);
            $stmt -> execute();
            $stmt-> close();
            header('Location:sector.php?msgu=successful');
        }
    }
}
else {
    header('Location:index.php');
}
//Echos message if one exists
function formMessage ($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'noname') {
            echo"<p class='form unsuc'>Please enter a name.</p>";
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
        <div>
            <p>Sector ID: <?php echo $id; ?></p>
            <form method='post' action='sectoredit.php'>
                <div>
                    <p class='form'>Sector Name:</p><input type='text' name='sector' value='<?php echo $name?>'><input type='submit' name='update' value='Update!'><?php formMessage($con); ?>
                </div>
            </form>
        </div>   
    </body>
</html>
<?php $con->close(); ?>