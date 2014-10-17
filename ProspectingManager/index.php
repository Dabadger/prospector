<?php
require_once 'config.php';
$con = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if ($con->connect_errno) {
    echo "Failed to connect to database!";
}

//Loops through and find planets to populate selection list
function formOptions($con) {
    $list = $con->query("SELECT id, name from planets ORDER BY name");
    while($row = $list->fetch_array()) {
        echo "<option id='" . $row[0] . "' value='" . $row[0] . "'>" . $row[1];
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
            <h2>Admin Tools</h2>
            <a href='sector.php'><h3 class='button'>Sector</h3></a> 
            <a href='system.php'><h3 class='button'>System</h3></a>
            <a href='planet.php'><h3 class='button'>Planet</h3></a>
            <h2>Deposit Tool</h2>
            <form method='post' action='prospect.php'>
                <div>
                    <p>Select a planet to get started:</p>
                    <select name='planetlist'>
                        <?php formOptions($con); ?>
                    </select>
                    <input type='submit' name='submit' value='Prospect!'>
                </div>
            </form>
        </div>
    </body>

</html>