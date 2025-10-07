<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/lib/Init.php';
require_once __DIR__.'/lib/Auth.php';
require_once __DIR__.'/lib/Helpers.php';
                                
Init::startSession();
if (isset($_SESSION['email'])) {
	Init::destroy();
	Init::startSession();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
		$email = trim((string)($_POST['email'] ?? ''));
		$password = (string)($_POST['password'] ?? '');
		if ($email === '' || $password === '') {
			$error = 'Email and password are required.';
		} else {
			if (Auth::loginAdmin($email, $password)) {
				header('Location: dashboard.php?q=1');
				exit;
			} else {
				$error = 'Invalid admin credentials.';
			}
		}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>Admin Login | Online Quiz System</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="scripts/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="scripts/ionicons/css/ionicons.min.css">
		<link rel="stylesheet" href="css/form.css">
        
	</head>

	<body>
		<section class="login first grey">
			<div class="container">
				<div class="box-wrapper">				
					<div class="box box-border">
						<div class="box-body">
						<center><h5>Login to</h5><h4>Admin Page</h4></center><br>
							<form method="post" action="admin.php" enctype="multipart/form-data">
								<?php if (!empty($error)): ?>
									<div class="alert alert-danger" role="alert"><?php echo Helpers::e($error); ?></div>
								<?php endif; ?>
								<div class="form-group">
									<label>Enter Your Email Id:</label>
									<input type="email" name="email" class="form-control" required>
								</div>
								<div class="form-group">
									<label class="fw">Enter Your Password:
										<a href="javascript:void(0)" class="pull-right">Forgot Password?</a>
									</label>
									<input type="password" name="password" class="form-control" required>
								</div> 
                                
								<div class="form-group text-right">
									<button class="btn btn-primary btn-block" name="submit">Login</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>

		<script src="js/jquery.js"></script>
		<script src="scripts/bootstrap/bootstrap.min.js"></script>
	</body>
</html>