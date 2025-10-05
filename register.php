<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/lib/Init.php';
require_once __DIR__.'/lib/Auth.php';
require_once __DIR__.'/lib/Helpers.php';

Init::startSession();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
		$name = trim((string)($_POST['name'] ?? ''));
		$email = trim((string)($_POST['email'] ?? ''));
		$password = (string)($_POST['password'] ?? '');
		$college = trim((string)($_POST['college'] ?? ''));
		if ($name === '' || $email === '' || $password === '' || $college === '') {
			$error = 'All fields are required.';
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = 'Please enter a valid email address.';
		} else {
			try {
				$user = Auth::registerUser($name, $email, $password, $college);
				if ($user) {
					// Auto-login
					Auth::loginUser($email, $password);
					header('Location: welcome.php?q=1');
					exit;
				} else {
					$error = 'Email already registered or invalid input.';
				}
			} catch (Throwable $e) {
				$error = 'Registration failed. Please try again.';
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
		<title>Register | Online Quiz System</title>
		<link rel="stylesheet" href="scripts/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="scripts/ionicons/css/ionicons.min.css">
		<link rel="stylesheet" href="css/form.css">
        <style type="text/css">
            body{
				background: url('image/bgImg.jpg');
    			background-repeat: no-repeat;
				background-position: center;
				background-size:cover;
                }
          </style>
	</head>

	<body>
		<section class="login first grey">
			<div class="container">
				<div class="box-wrapper">				
					<div class="box box-border">
						<div class="box-body">
							<center> <h5 style="font-family: Noto Sans;">Register to </h5><h4 style="font-family: Noto Sans;">Online Quiz System</h4></center><br>
							<form method="post" action="register.php" enctype="multipart/form-data">
								<?php if (!empty($error)): ?>
									<div class="alert alert-danger" role="alert"><?php echo Helpers::e($error); ?></div>
								<?php endif; ?>
                                <div class="form-group">
									<label>Enter Your Username:</label>
									<input type="text" name="name" class="form-control" required />
								</div>
								<div class="form-group">
									<label>Enter Your Email Id:</label>
									<input type="email" name="email" class="form-control" required />
								</div>
								<div class="form-group">
									<label>Enter Your Password:</label>
									<input type="password" name="password" class="form-control" required />
                                </div>
								<div class="form-group">
									<label>Enter Your College Name:</label>
									<input type="text" name="college" class="form-control" required />
								</div>
                                
                                
								<div class="form-group text-right">
									<button class="btn btn-primary btn-block" name="submit">Register</button>
								</div>
								<div class="form-group text-center">
									<span class="text-muted">Already have an account! </span> <a href="login.php">Login </a> Here..
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