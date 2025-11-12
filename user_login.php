<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';
if(current_user()){ header('Location: ' . url('dashboard.php')); exit; }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')) $error='Invalid token.'; else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if(!login($email,$password)) $error='Invalid credentials.';
    else redirect('dashboard.php');
  }
}
?>
<div class="max-w-xl mx-auto">
  <div class="bg-white/5 border border-white/10 rounded-2xl p-8 shadow-soft">
    <h1 class="text-2xl font-bold mb-6">User Login</h1>
    <?php if($error): ?><div class="mb-4 text-sm text-red-300"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input name="email" type="email" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <div>
        <label class="block text-sm mb-1">Password</label>
        <input name="password" type="password" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <button class="w-full py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Login</button>
    </form>
    <p class="mt-4 text-sm text-white/70">No account? <a class="text-plum" href="<?= url('register.php') ?>">Register</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>