<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid session token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = register_user($name, $email, $password);
        // if invite code present, bind invited_by
        if ($result['ok']) {
          $inviteCode = trim($_GET['invite'] ?? '');
          if ($inviteCode) {
            $st = db()->prepare('SELECT i.*, u.id AS inviter_id FROM invites i JOIN users u ON i.user_id=u.id WHERE i.code=? AND i.used_by IS NULL');
            $st->execute([$inviteCode]);
            if ($row = $st->fetch()) {
              // set invited_by and mark invite as used
              $uid = current_user()['id'];
              db()->prepare('UPDATE users SET invited_by=? WHERE id=?')->execute([$row['inviter_id'], $uid]);
              db()->prepare('UPDATE invites SET used_by=?, redeemed_at=NOW() WHERE id=?')->execute([$uid, $row['id']]);
              // immediate small reward to inviter upon signup
              db()->prepare('UPDATE users SET points = points + 10 WHERE id=?')->execute([$row['inviter_id']]);
            }
          }
        }
        if ($result['ok']) {
            redirect('dashboard.php');
        } else {
            $errors = $result['errors'];
        }
    }
}
?>
<div class="max-w-xl mx-auto">
  <div class="bg-white/5 border border-white/10 rounded-2xl p-8 shadow-soft">
    <h1 class="text-2xl font-bold mb-6">Register</h1>
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
    <p class="mt-4 text-sm text-white/70">Already have an account? <a class="text-plum" href="<?= url('login.php') ?>">Login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
