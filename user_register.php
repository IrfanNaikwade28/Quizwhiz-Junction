<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')) $errors[]='Invalid session token.'; else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $result = register_user($name,$email,$password);
    if ($result['ok']) { redirect('dashboard.php'); }
    else { $errors = $result['errors']; }
  }
}
?>
<div class="max-w-xl mx-auto">
  <div class="bg-white/5 border border-white/10 rounded-2xl p-8 shadow-soft">
    <h1 class="text-2xl font-bold mb-6">User Registration</h1>
    <?php if($errors): ?>
      <ul class="mb-4 text-sm text-red-300 space-y-1">
        <?php foreach($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <div>
        <label class="block text-sm mb-1">Name</label>
        <input name="name" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input name="email" type="email" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <div>
        <label class="block text-sm mb-1">Password</label>
        <input name="password" type="password" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <button class="w-full py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Create Account</button>
    </form>
    <p class="mt-4 text-sm text-white/70">Already have an account? <a class="text-plum" href="<?= url('user_login.php') ?>">Login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>