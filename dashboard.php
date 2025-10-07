<?php
include_once 'database.php';
session_start();
// Admin-only access
if (!isset($_SESSION['email']) || !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') && (!isset($_SESSION['key']) || $_SESSION['key'] !== 'suryapinky')) {
    header("Location: admin.php");
    exit;
}
$name = $_SESSION['name'];
$email = $_SESSION['email'];
include_once 'database.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard | Quizwhiz Junction</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="css/welcome.css">
    <link rel="stylesheet" href="css/navbar.css">
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    
</head>

<body>
    <header>
        <div class="logo"><img src="image/Logo.png" alt=""></div>
        <ul>
            <li <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?>><a href="dashboard.php?q=1">Users</a></li>
            <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="dashboard.php?q=2">Ranking</a></li>
            <li class="dropdown <?php if (@$_GET['q'] == 4 || @$_GET['q'] == 5) echo 'active"'; ?>">
            <li><a href="dashboard.php?q=4">Add Quiz</a></li>
            <li><a href="dashboard.php?q=5">Remove Quiz</a></li>
            <li id="actionBtn"><a href="logout.php?q=welcome.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Log out</a></li>
        </ul>
    <i class="fa-solid fa-bars menu-icon" id="menuIcon" aria-label="Menu" title="Menu"></i>
        <div class="menu-collapse">
            <ul>
                <li <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?>><a href="dashboard.php?q=1">User</a></li>
                <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="dashboard.php?q=2">Ranking</a></li>
                <li class="dropdown <?php if (@$_GET['q'] == 4 || @$_GET['q'] == 5) echo 'active"'; ?>">
                <li><a href="dashboard.php?q=4">Add Quiz</a></li>
                <li><a href="dashboard.php?q=5">Remove Quiz</a></li>
                <li id="actionBtn"><a href="logout.php?q=welcome.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Log out</a></li>
            </ul>
        </div>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php

                if (@$_GET['q'] == 2) {
                    $q = mysqli_query($con, "SELECT * FROM `rank` ORDER BY `score` DESC LIMIT 3") or die('Error223');
                    echo  '<div class="panel title leaderboard-panel"><div class="table-responsive">
                    <table class="table leaderboard-table title1" >
                    <tr class="leaderboard-head"><td><center><b>Rank</b></center></td><td><center><b>Name</b></center></td><td><center><b>Score</b></center></td></tr>';
                    $c = 0;
                    while ($row = mysqli_fetch_array($q)) {
                        $e = $row['email'];
                        $s = $row['score'];
                        // Default to email if user record missing
                        $name = $e;
                        $q12 = mysqli_query($con, "SELECT name, college FROM user WHERE email='$e' ") or die('Error231');
                        if ($urow = mysqli_fetch_array($q12)) {
                            $name = $urow['name'];
                            $college = $urow['college'];
                        }
                        $c++;
                        $rowClass = ($c === 1) ? ' class="is-top"' : '';
                        echo '<tr'.$rowClass.'><td><center><b>' . htmlspecialchars((string)$c, ENT_QUOTES) . '</b></center></td><td><center>' . htmlspecialchars($name, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars((string)$s, ENT_QUOTES) . '</center></td></tr>';
                    }
                    echo '</table></div></div>';
                }
                ?>
                <?php
                if (@$_GET['q'] == 1) {
                    $result = mysqli_query($con, "SELECT * FROM user") or die('Error');
                    echo  '<div class="panel leaderboard-panel"><div class="table-responsive"><table class="table leaderboard-table title1">
                        <tr class="leaderboard-head"><td><center><b>S.N.</b></center></td><td><center><b>Name</b></center></td><td><center><b>College</b></center></td><td><center><b>Email</b></center></td><td><center><b>Action</b></center></td></tr>';
                    $c = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        $name = $row['name'];
                        $email = $row['email'];
                        $college = $row['college'];
                        echo '<tr><td><center>' . htmlspecialchars((string)$c++, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars($name, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars($college, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars($email, ENT_QUOTES) . '</center></td><td><center>
                        <form method="post" action="update.php?demail=1" style="display:inline" onsubmit="return confirm(\'Delete user and related data?\')">
                            <input type="hidden" name="demail" value="' . htmlspecialchars($email, ENT_QUOTES) . '">
                            <button type="submit" class="btn btn-link" title="Delete User"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
                        </form>
                        </center></td></tr>';
                    }
                    $c = 0;
                    echo '</table></div></div>';
                }
                ?>

                <?php
                if (@$_GET['q'] == 4 && !(@$_GET['step'])) {
                    echo '<div class="row"><span class="title1" style="margin-left:40%;font-size:30px;"><b>Enter Quiz Details</b></span><br /><br />
                        <div class="col-md-3"></div><div class="col-md-6">   
            <form class="form-horizontal title1" name="form" action="update.php?q=addquiz"  method="POST">
                            <fieldset>
                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="name"></label>  
                                    <div class="col-md-12">
                                        <input id="name" name="name" placeholder="Enter Quiz title" class="form-control input-md" type="text">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="total"></label>  
                                    <div class="col-md-12">
                                        <input id="total" name="total" placeholder="Enter total number of questions" class="form-control input-md" type="number">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="right"></label>  
                                    <div class="col-md-12">
                                        <input id="right" name="right" placeholder="Enter marks on right answer" class="form-control input-md" min="0" type="number">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="wrong"></label>  
                                    <div class="col-md-12">
                                        <input id="wrong" name="wrong" placeholder="Enter minus marks on wrong answer without sign" class="form-control input-md" min="0" type="number">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-md-12 control-label" for=""></label>
                                    <div class="col-md-12"> 
                                        <input  type="submit" style="margin-left:45%" class="btn btn-primary" value="Submit" class="btn btn-primary"/>
                                    </div>
                                </div>

                            </fieldset>
                        </form></div>';
                }
                ?>

                <?php
                if (@$_GET['q'] == 4 && (@$_GET['step']) == 2) {
                    echo ' 
                        <div class="row">
                        <span class="title1" style="margin-left:40%;font-size:30px;"><b>Enter Question Details</b></span><br /><br />
                        <div class="col-md-3"></div><div class="col-md-6"><form class="form-horizontal title1" name="form" action="update.php?q=addqns&n=' . @$_GET['n'] . '&eid=' . @$_GET['eid'] . '&ch=4 "  method="POST">
                        <fieldset>
                        
                        ';

                    for ($i = 1; $i <= @$_GET['n']; $i++) {
                        echo '<b>Question number&nbsp;' . $i . '&nbsp;:</><br /><!-- Text input-->
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" for="qns' . $i . ' "></label>  
                                        <div class="col-md-12">
                                            <textarea rows="3" cols="5" name="qns' . $i . '" class="form-control" placeholder="Write question number ' . $i . ' here..."></textarea>  
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" for="' . $i . '1"></label>  
                                        <div class="col-md-12">
                                            <input id="' . $i . '1" name="' . $i . '1" placeholder="Enter option a" class="form-control input-md" type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" for="' . $i . '2"></label>  
                                        <div class="col-md-12">
                                            <input id="' . $i . '2" name="' . $i . '2" placeholder="Enter option b" class="form-control input-md" type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" for="' . $i . '3"></label>  
                                        <div class="col-md-12">
                                            <input id="' . $i . '3" name="' . $i . '3" placeholder="Enter option c" class="form-control input-md" type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" for="' . $i . '4"></label>  
                                        <div class="col-md-12">
                                            <input id="' . $i . '4" name="' . $i . '4" placeholder="Enter option d" class="form-control input-md" type="text">
                                        </div>
                                    </div>
                                    <br />
                                    <b>Correct answer</b>:<br />
                                    <select id="ans' . $i . '" name="ans' . $i . '" placeholder="Choose correct answer " class="form-control input-md" >
                                    <option value="a">Select answer for question ' . $i . '</option>
                                    <option value="a"> option a</option>
                                    <option value="b"> option b</option>
                                    <option value="c"> option c</option>
                                    <option value="d"> option d</option> </select><br /><br />';
                    }
                    echo '<div class="form-group">
                                <label class="col-md-12 control-label" for=""></label>
                                <div class="col-md-12"> 
                                    <input  type="submit" style="margin-left:45%" class="btn btn-primary" value="Submit" class="btn btn-primary"/>
                                </div>
                              </div>

                        </fieldset>
                        </form></div>';
                }
                ?>

                <?php
                if (@$_GET['q'] == 5) {
                    $result = mysqli_query($con, "SELECT * FROM quiz ORDER BY date DESC") or die('Error');
                    echo  '<div class="panel"><div class="table-responsive"><table class="table table-striped title1">
                        <tr><td><center><b>S.N.</b></center></td><td><center><b>Topic</b></center></td><td><center><b>Total question</b></center></td><td><center><b>Marks</b></center></td><td><center><b>Action</b></center></td></tr>';
                    $c = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        $title = $row['title'];
                        $total = $row['total'];
                        $sahi = $row['sahi'];
                        $eid = $row['eid'];
                        echo '<tr><td><center>' . htmlspecialchars((string)$c++, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars($title, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars((string)$total, ENT_QUOTES) . '</center></td><td><center>' . htmlspecialchars((string)($sahi * $total), ENT_QUOTES) . '</center></td>
                            <td><center>
                            <form method="post" action="update.php?q=rmquiz" style="display:inline" onsubmit="return confirm(\'Remove quiz and all related data?\')">
                                <input type="hidden" name="eid" value="' . htmlspecialchars($eid, ENT_QUOTES) . '">
                                <button type="submit" class="pull-right btn btn-danger sub1"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;<span class="title1"><b>Remove</b></span></button>
                            </form>
                            </center></td></tr>';
                    }
                    $c = 0;
                    echo '</table></div></div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>