<?php
    include_once 'database.php';
    session_start();
    if(!(isset($_SESSION['email'])))
    {
        header("location:login.php");
    }
    else
    {
        $name = $_SESSION['name'];
        $email = $_SESSION['email'];
        include_once 'database.php';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome | Quizwhiz Junction<</title>
    <link  rel="stylesheet" href="css/bootstrap.min.css"/>
    <link  rel="stylesheet" href="css/bootstrap-theme.min.css"/>    
    <link rel="stylesheet" href="css/welcome.css">
        <!-- <link  rel="stylesheet" href="css/font.css"> -->
    <link rel="stylesheet" href="css/navbar.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
  />
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"  type="text/javascript"></script>
    
</head>
<body>
    <header>
        <div class="logo"><img src="image/Logo.png" alt=""></div>
        <ul>
    <li <?php if(@$_GET['q']==1) echo'class="active"'; ?> ><a href="welcome.php?q=1"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;Home<span class="sr-only">(current)</span></a></li>
        <li <?php if(@$_GET['q']==2) echo'class="active"'; ?>> <a href="welcome.php?q=2"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>&nbsp;History</a></li>
        <li <?php if(@$_GET['q']==3) echo'class="active"'; ?>> <a href="welcome.php?q=3"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;Ranking</a></li>
        <li id="actionBtn"><a href="logout.php?q=welcome.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Log out</a></li>
        </ul>
    <i class="fa-solid fa-bars menu-icon" id="menuIcon" aria-label="Menu" title="Menu"></i>
        <div class="menu-collapse">
            <ul>
                <li <?php if(@$_GET['q']==1) echo'class="active"'; ?> ><a href="welcome.php?q=1"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;Home<span class="sr-only">(current)</span></a></li>
                <li <?php if(@$_GET['q']==2) echo'class="active"'; ?>> <a href="welcome.php?q=2"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>&nbsp;History</a></li>
                <li <?php if(@$_GET['q']==3) echo'class="active"'; ?>> <a href="welcome.php?q=3"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;Ranking</a></li>
                <li id="actionBtn"><a href="logout.php?q=welcome.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Log out</a></li>
            </ul>
        </div>
    </header>
    <br><br>
    <br><br>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php if(@$_GET['q']==1) 
                {
                    $result = mysqli_query($con,"SELECT * FROM quiz ORDER BY date DESC") or die('Error');
                        echo  '<div class="panel"><div class="table-responsive"><table class="table leaderboard-table title1">
                    <tr class="leaderboard-head"><td><center><b>S.N.</b></center></td><td><center><b>Topic</b></center></td><td><center><b>Total question</b></center></td><td><center><b>Marks</center></b></td><td><center><b>Action</b></center></td></tr>';
                    $c=1;
                    while($row = mysqli_fetch_array($result)) {
                        $title = $row['title'];
                        $total = $row['total'];
                        $sahi = $row['sahi'];
                        $eid = $row['eid'];
                    $q12=mysqli_query($con,"SELECT score FROM history WHERE eid='$eid' AND email='$email'" )or die('Error98');
                    $rowcount=mysqli_num_rows($q12);	
                    if($rowcount == 0){
                        echo '<tr><td><center>'.htmlspecialchars((string)$c++, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars($title, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$total, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)($sahi*$total), ENT_QUOTES).'</center></td><td><center><b><a href="welcome.php?q=quiz&step=2&eid='.urlencode($eid).'&n=1&t='.urlencode((string)$total).'" class="btn btn-primary sub1"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>&nbsp;<span class="title1"><b>Start</b></span></a></b></center></td></tr>';
                    }
                    else
                    {
                    echo '<tr><td><center>'.htmlspecialchars((string)$c++, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars($title, ENT_QUOTES).'&nbsp;<span title="This quiz is already solve by you" class="glyphicon glyphicon-ok" aria-hidden="true"></span></center></td><td><center>'.htmlspecialchars((string)$total, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)($sahi*$total), ENT_QUOTES).'</center></td><td><center>
                    <form method="post" action="update.php?q=quizre&step=25" style="display:inline" onsubmit="return confirm(&quot;Restart this quiz? Your current attempt score will be removed from the leaderboard.&quot;)">
                        <input type="hidden" name="eid" value="'.htmlspecialchars($eid, ENT_QUOTES).'">
                        <input type="hidden" name="t" value="'.htmlspecialchars((string)$total, ENT_QUOTES).'">
                        <button type="submit" class="pull-right btn btn-danger sub1"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>&nbsp;<span class="title1"><b>Restart</b></span></button>
                    </form>
                    </center></td></tr>';
                    }
                    }
                    $c=0;
                    echo '</table></div></div>';
                }?>

                <?php
                    if(@$_GET['q']== 'quiz' && @$_GET['step']== 2) 
                    {
                        $eid=@$_GET['eid'];
                        $sn=@$_GET['n'];
                        $total=@$_GET['t'];
                        $q=mysqli_query($con,"SELECT * FROM questions WHERE eid='$eid' AND sn='$sn' " );
                        $progress = 0;
                        if (is_numeric($sn) && is_numeric($total) && (int)$total > 0) {
                            $progress = (int)floor(((int)$sn - 1) * 100 / (int)$total);
                            if ($progress < 0) $progress = 0; if ($progress > 100) $progress = 100;
                        }
                        echo '<div class="panel" style="margin:5%">';
                        echo '<div class="row"><div class="col-md-8">
                                <div class="progress">
                                    <div id="quizProgress" class="progress-bar" role="progressbar" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$progress.'%">'.$progress.'%</div>
                                </div>
                              </div>
                              <div class="col-md-4 text-right">
                                <span class="label label-info" style="font-size:14px">Time left: <span id="timer">30</span>s</span>
                              </div></div><br/>';
                        while($row=mysqli_fetch_array($q) )
                        {
                            $qns=$row['qns'];
                            $qid=$row['qid'];
                            echo '<b>Question &nbsp;'.htmlspecialchars((string)$sn, ENT_QUOTES).'&nbsp;::<br /><br />'.htmlspecialchars($qns, ENT_QUOTES).'</b><br /><br />';
                        }
                        $q=mysqli_query($con,"SELECT * FROM options WHERE qid='$qid' " );
                        echo '<form action="update.php?q=quiz&step=2&eid='.$eid.'&n='.$sn.'&t='.$total.'&qid='.$qid.'" method="POST"  class="form-horizontal">
                        <br />';

                        while($row=mysqli_fetch_array($q) )
                        {
                            $option=$row['option'];
                            $optionid=$row['optionid'];
                            echo'<input type="radio" name="ans" value="'.htmlspecialchars($optionid, ENT_QUOTES).'">&nbsp;'.htmlspecialchars($option, ENT_QUOTES).'<br /><br />';
                        }
                        
                        echo'<br /><button id="submitBtn" type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>&nbsp;Submit</button></form></div>';
                        echo '<script>
                            (function(){
                                var sec = 30;
                                var timerEl = document.getElementById("timer");
                                var submitBtn = document.getElementById("submitBtn");
                                var form = submitBtn ? submitBtn.closest("form") : null;
                                var progressBar = document.getElementById("quizProgress");
                                var interval = setInterval(function(){
                                    sec--; if (sec < 0) sec = 0;
                                    if (timerEl) timerEl.textContent = String(sec);
                                    if (sec === 0) {
                                        clearInterval(interval);
                                        if (form) form.submit();
                                    }
                                }, 1000);
                            })();
                        </script>';
                    }

                    if(@$_GET['q']== 'result' && @$_GET['eid']) 
                    {
                        $eid=@$_GET['eid'];
                        $q=mysqli_query($con,"SELECT * FROM history WHERE eid='$eid' AND email='$email' " )or die('Error157');
                        echo  '<div class="panel leaderboard-panel">
                        <center><h1 class="title">Result</h1><center><br /><table class="table table-striped title1" style="font-size:20px;font-weight:1000;">';

                        while($row=mysqli_fetch_array($q) )
                        {
                            $s=$row['score'];
                            $w=$row['wrong'];
                            $r=$row['sahi'];
                            $qa=$row['level'];
                            echo '<tr><td>Total Questions</td><td>'.htmlspecialchars((string)$qa, ENT_QUOTES).'</td></tr>
                                <tr><td>Right Answer&nbsp;<span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span></td><td>'.htmlspecialchars((string)$r, ENT_QUOTES).'</td></tr> 
                                <tr><td>Wrong Answer&nbsp;<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span></td><td>'.htmlspecialchars((string)$w, ENT_QUOTES).'</td></tr>
                                <tr><td>Score&nbsp;<span class="glyphicon glyphicon-star" aria-hidden="true"></span></td><td>'.htmlspecialchars((string)$s, ENT_QUOTES).'</td></tr>';
                        }
                        $q=mysqli_query($con,"SELECT * FROM `rank` WHERE `email`='$email' " )or die('Error157');
                        while($row=mysqli_fetch_array($q) )
                        {
                            $s=$row['score'];
                            echo '<tr><td>Overall Score&nbsp;<span class="glyphicon glyphicon-stats" aria-hidden="true"></span></td><td>'.htmlspecialchars((string)$s, ENT_QUOTES).'</td></tr>';
                        }
                        echo '</table></div>';
                    }
                ?>

                <?php
                    if(@$_GET['q']== 2) 
                    {
                        $q=mysqli_query($con,"SELECT * FROM history WHERE email='$email' ORDER BY date DESC " )or die('Error197');
                        echo  '<div class="panel title leaderboard-panel">
                        <table class="table leaderboard-table title1" >
                        <tr class="leaderboard-head"><td><center><b>S.N.</b></center></td><td><center><b>Quiz</b></center></td><td><center><b>Question Solved</b></center></td><td><center><b>Right</b></center></td><td><center><b>Wrong<b></center></td><td><center><b>Score</b></center></td>';
                        $c=0;
                        while($row=mysqli_fetch_array($q) )
                        {
                        $eid=$row['eid'];
                        $s=$row['score'];
                        $w=$row['wrong'];
                        $r=$row['sahi'];
                        $qa=$row['level'];
                        $q23=mysqli_query($con,"SELECT title FROM quiz WHERE  eid='$eid' " )or die('Error208');

                        while($row=mysqli_fetch_array($q23) )
                        {  $title=$row['title'];  }
                        $c++;
                        echo '<tr><td><center>'.htmlspecialchars((string)$c, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars($title, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$qa, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$r, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$w, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$s, ENT_QUOTES).'</center></td></tr>';
                        }
                        echo'</table></div>';
                    }

                    if(@$_GET['q']== 3) 
                    {
                        $q=mysqli_query($con,"SELECT r.email, r.score, u.name FROM `rank` r LEFT JOIN `user` u ON u.email = r.email ORDER BY r.score DESC LIMIT 3" )or die('Error223');
                        echo  '<div class="panel title leaderboard-panel"><div class="table-responsive">
                        <table class="table leaderboard-table title1" >
                        <tr class="leaderboard-head"><td><center><b>Rank</b></center></td><td><center><b>Name</b></center></td><td><center><b>Email</b></center></td><td><center><b>Score</b></center></td></tr>';
                        $c=0;
                        while($row=mysqli_fetch_assoc($q) )
                        {
                            $e=$row['email'];
                            $s=$row['score'];
                            $name = $row['name'] ? $row['name'] : $e;
                            $c++;
                            $rowClass = ($c === 1) ? ' class="is-top"' : '';
                            echo '<tr'.$rowClass.'><td><center><b>'.htmlspecialchars((string)$c, ENT_QUOTES).'</b></center></td><td><center>'.htmlspecialchars($name, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars($e, ENT_QUOTES).'</center></td><td><center>'.htmlspecialchars((string)$s, ENT_QUOTES).'</center></td></tr>';
                        }
                        if ($c === 0) {
                            echo '<tr><td colspan="4"><center>No rankings yet. Finish a quiz to appear here.</center></td></tr>';
                        }
                        echo '</table></div></div>';
                    }
                ?>
<script src="js/navbar.js"></script>
</body>
</html>