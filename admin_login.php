<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';
// Admin login uses shared functions from auth.php (included via header)
if (!isset($_SESSION['admin'])) { $_SESSION['admin'] = null; }

$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ $error='Invalid token.'; }
  else {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if(admin_login_account($u,$p)) { redirect('admin/index.php'); }
    else { $error='Invalid credentials.'; }
  }
}
?>
<div class="max-w-xl mx-auto">
  <div class="bg-white/5 border border-white/10 rounded-2xl p-8 shadow-soft">
    <h1 class="text-2xl font-bold mb-6">Admin Login</h1>
    <?php if($error): ?><div class="mb-4 text-sm text-red-300"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
      <div>
        <label class="block text-sm mb-1">Username</label>
        <input name="username" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <div>
        <label class="block text-sm mb-1">Password</label>
        <input name="password" type="password" required class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 focus:outline-none focus:ring-2 focus:ring-plum" />
      </div>
      <button class="w-full py-3 rounded-xl bg-violet hover:bg-plum font-semibold">Login</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>