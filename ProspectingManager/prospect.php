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
    $size = $_SESSION['size'];
    $name = $_SESSION['name'];
    $x = $_SESSION['x'];
    $y = $_SESSION['y'];
    $systemname = $_SESSION['systemname'];
    $systemx = $_SESSION['systemx'];
    $systemy = $_SESSION['systemy'];
    $sectorname = $_SESSION['sectorname'];
}
elseif(isset($_POST['submit'])) {
    //Get planet details
    $id = $_POST['planetlist'];
    $queryplanet = $con -> query("SELECT name, system, size, x, y FROM planets WHERE id = $id");
    $planetresult = $queryplanet -> fetch_assoc();
    $name = $planetresult['name'];
    $systemid = $planetresult['system'];
    $size = $planetresult['size'];
    $x = $planetresult['x'];
    $y = $planetresult['y'];
    //Get system details
    $querysystem = $con -> query("SELECT name, sector, x, y FROM systems WHERE id = $systemid");
    $systemresult = $querysystem -> fetch_assoc();
    $systemname = $systemresult['name'];
    $sectorid = $systemresult['sector'];
    $systemx = $systemresult['x'];
    $systemy = $systemresult['y'];
    //Get sector details
    $querysector = $con -> query("SELECT name FROM sectors WHERE id = $sectorid");
    $sectorresult = $querysector -> fetch_assoc();
    $sectorname = $sectorresult['name'];
    //Save details
    $_SESSION['id'] = $id;
    $_SESSION['size'] = $size;
    $_SESSION['name'] = $name;
    $_SESSION['x'] = $x;
    $_SESSION['y'] = $y;
    $_SESSION['systemname'] = $systemname;
    $_SESSION['systemx'] = $systemx;
    $_SESSION['systemy'] = $systemy;
    $_SESSION['sectorname'] = $sectorname;
}
elseif(isset($_POST['update'])) {
    //If no name entered stop and return with error msg
    if(trim($_POST['city']) == "")
    {
        header('Location:prospect.php?msg=noname');
    }
    //If no user entered stop and return with error msg
    elseif(trim($_POST['prospector']) == "")
    {
        header('Location:prospect.php?msg=nouser');
    }
    else {
        $id = $_SESSION['id'];
        $size = $_SESSION['size'];
        $name = $_SESSION['name'];
        $x = $_SESSION['x'];
        $y = $_SESSION['y'];
        $systemname = $_SESSION['systemname'];
        $systemx = $_SESSION['systemx'];
        $systemy = $_SESSION['systemy'];
        $sectorname = $_SESSION['sectorname'];
        $city = $_POST['city'];
        $cityx = $_POST['x'];
        $cityy = $_POST['y'];
        $terrain = $_POST['terrain'];
        $deposit = $_POST['deposit'];
        $depositAm = $_POST['amount'];
        $prospector = $_POST['prospector'];
        $now = date('Y-m-d H:i:s');
        //Check to see if already a result at same location, then either insert or update accordingly
        $count = $con -> query("SELECT id from planetmap Where x = $cityx AND y = $cityy AND planet = $id");
        $result = $count -> num_rows;
        if($result == 0) {
            if($stmt = $con -> prepare("INSERT INTO planetmap (planet, x, y, name, deposit, depositAmount, terrain, prospector, updateTime)  VALUES ($id, $cityx, $cityy, ?, ?, ?, ?, ?, '$now')")) {
                $stmt -> bind_param("siiis", $city, $deposit, $depositAm, $terrain, $prospector);
                $stmt -> execute();
                $stmt-> close();
            }
        }
        else {
            if($stmt = $con -> prepare("UPDATE planetmap SET name=?, deposit=?, depositAmount=?, terrain=?, prospector=?, updateTime='$now'  WHERE planet=$id AND x=$cityx AND y=$cityy")) {
                $stmt -> bind_param("siiis", $city, $deposit, $depositAm, $terrain, $prospector);
                $stmt -> execute();
                $stmt-> close();
            }
        }
        header('Location:prospect.php?msg=success');
    }
}
else {
    header('Location:index.php');
}
//Create array of all terrains
$list = $con->query("SELECT id, name from terrain");
while($row = $list->fetch_array()) {
    $terrainarry[$row[0]] = $row[1];
}
//Create array of all deposit names and images
$list = $con->query("SELECT id, image, name from deposit");
while($row = $list->fetch_array()) {
    $depositarry[$row[0]] = $row[1];
    $depositnamearry[$row[0]] = ucfirst($row[2]);
}
//Create a map of planet
function map($con, $id, $size, $terrainarry, $depositarry, $depositnamearry) {
    //Create first row and increase y after loop through each x on that row
    for($a=0; $a < $size; $a++) {
        echo "<div class='cityrow'>";
        for($b=0; $b < $size; $b++) {
            //Create city div, then populate with information if they exist at that coordinate
            $querycity = $con->query("SELECT name, deposit, depositAmount, terrain, prospector from planetmap Where x = $b AND y = $a AND planet = $id");
            $citycount = $querycity -> num_rows;
            echo "<div class='city ";
            if($citycount > 0) {
                $cityresult = $querycity -> fetch_assoc();
                $cityterrain = $terrainarry[$cityresult['terrain']];
                $citydeposit = $depositarry[$cityresult['deposit']];
                $citydepositname = $depositnamearry[$cityresult['deposit']];
                $citydepositAm = number_format($cityresult['depositAmount']);
                $cityprospector = $cityresult['prospector'];
                $cityname = $cityresult['name'];
                //Alter class for terrain to give appropriate background
                echo $cityterrain . "'";
            }
            echo "'>";
            if($citycount > 0) {
                echo "<p>$cityname ($b, $a)</p><p>$citydepositname</p>";
                //Check if city has deposit, otherwise state none
                if($citydepositname != "None") {
                    echo "<p>$citydepositAm</p><p>$cityprospector</p>";
                }
                else {
                    echo "<p>-<p>$cityprospector</p>";
                }
            }
            //Display image for deposit
            if($citycount > 0 && $citydepositname != "None") {
                echo "<img src ='" . $citydeposit . "'>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
}
//Create a list of deposit totals for planet
function planetDeposits ($con, $depositarry, $depositnamearry, $id) {
    for($x=1; $x <= count($depositarry); $x++) {
        if($depositnamearry[$x] != "None") {
            echo "<p>$depositnamearry[$x]: ";
            $amount = 0;
            $list = $con->query("SELECT depositAmount from planetmap WHERE planet = $id && deposit = $x");
            while($row = $list->fetch_array()) {
                $amount += $row[0];
            }
            echo number_format($amount) . " </p>";
        }
    }
}
//Create options for coordinates dependant on planet size
function coordOptions($con, $size) {
    echo "<select name='x'>";
    for($a=0; $a < $size; $a++) {
        echo "<option name='x' value='$a'> $a</option>";
    }
    echo "</select><select name='y'>";
    for($a=0; $a < $size; $a++) {
        echo "<option name='y' value='$a'> $a</option>";
    }
    echo "</select>";
}
//Create options for terrain
function terrainOptions($con, $terrainarry) {
    foreach($terrainarry as $row => $terrain) {
        echo "<option name='terrain' value='" . $row . "'>" . ucfirst($terrain) . "</option>";
    }
}
//Create options for deposits
function depositOptions($con, $depositnamearry) {
    foreach($depositnamearry as $row => $deposit) {
        if($deposit == "None") {
            echo "<option selected='selected' name='deposit' value='$row'>$deposit";
        }
        echo "<option name='deposit' value='" . $row . "'>" . $deposit;
    }
}
//Echo message if one exists
function formMessage ($con) {
    if(isset($_GET['msg'])) {
        if($_GET['msg'] == 'noname') {
            echo "<p class='form unsuc'>Please enter a city name.</p>";
        }
        elseif($_GET['msg'] == 'nouser') {
            echo "<p class='form unsuc'>Please enter your name.</p>";
        }
        elseif($_GET['msg'] == 'success') {
            echo "<p class='form suc'>Updated!</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>    
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <link rel="stylesheet" type="text/css" href="prospect.css" />
        <title>Prospecting Manager</title>
        <script>
            //View map function
            function map(){
                document.getElementById('map').style.display = "inline-block";
                document.getElementById('mapbutton').style.display = "none";
            }
            //Dragable map function
            var xStart = null;
            var yStart = null;
            var clicked = 0;
            function mouse(e) {
                xStart = e.clientX;
                yStart = e.clientY;
                clicked = 1;
            }
            function stop() {
                clicked = 0;
            }
            function move(e, t) {
                if (clicked == 1) {
                    var x = e.clientX;
                    var y = e.clientY;
                    t.scrollLeft += (xStart-x)/3;
                    t.scrollTop += (yStart-y)/3;
                }
            }
        </script>
    </head>   
    <body>
        <?php include 'header.php'; ?>
        <div class='contpros'>
            <div class='detail'>
                <h2>Planet Details</h2>
                <p>Sector: <?php echo $sectorname; ?></p>
                <p>System: <?php echo $systemname  . " (" . $systemx . ", " . $systemy . ")"; ?></p>
                <p>Planet: <?php echo $name . " (" . $x . ", " . $y . ")"; ?></p>
                <p>Size: <?php echo $size; ?> x <?php echo $size; ?></p>
            </div>
            <div class='matlist'>
                <h2>Total Planet Deposits</h2>
                <?php planetDeposits ($con, $depositarry, $depositnamearry, $id)?>
            </div>
            <div class='prospect'>
                <form method='post' action='prospect.php'>
                    <div>
                        <p class='form'>City name:</p><input type='text' name='city'>
                    </div>
                    <div>
                        <p class='form'>City coordinates:</p><?php coordOptions($con, $size); ?>
                    </div>
                    <div>
                        <p class='form'>Terrain:</p><select name='terrain'>
                        <?php terrainOptions($con, $terrainarry); ?>
                        </select>
                    </div>
                    <div>
                        <p class='form'>Deposit:</p><select name='deposit'>
                            <?php depositOptions($con, $depositnamearry); ?>
                        </select><p class='form'>Amount:</p><input type='text' name='amount'>
                    </div>
                    <div>
                        <p class='form'>Prospector:</p><input type='text' name='prospector'><input type='submit' name='update' value='Update!'><?php formMessage ($con); ?>
                    </div>
                </form>
            </div>
            <h3 class='button' id='mapbutton' onclick='map()'>View Map</h3>
            <div id='test'></div>
            <div class='map' id='map' onmouseover='move(event, this);' onmousedown='mouse(event);' onmouseup='stop();'>
                <?php map($con, $id, $size, $terrainarry, $depositarry, $depositnamearry); ?>
            </div>
        </div>
    </body>
</html>
<?php $con->close(); ?>