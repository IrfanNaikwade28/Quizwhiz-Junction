<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';
$user = current_user();

// Ensure invite code exists
$codeStmt = db()->prepare('SELECT code FROM invites WHERE user_id=? AND used_by IS NULL ORDER BY id DESC LIMIT 1');
$codeStmt->execute([$user['id']]);
$invite = $codeStmt->fetch();
if (!$invite) {
    $code = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
    db()->prepare('INSERT INTO invites (user_id, code, created_at) VALUES (?, ?, NOW())')->execute([$user['id'], $code]);
    $invite = ['code' => $code];
}
$inviteUrl = (isset($_SERVER['HTTP_HOST']) ? (($_SERVER['HTTPS']??'')==='on'?'https://':'http://') . $_SERVER['HTTP_HOST'] : '') . url('register.php') . '?invite=' . urlencode($invite['code']);
?>
<div class="max-w-3xl mx-auto space-y-6">
  <div class="rounded-2xl p-6 bg-gradient-purple border border-white/10 shadow-soft">
    <h1 class="text-2xl font-bold mb-2">Invite Friends</h1>
    <p class="text-white/90">Share your unique invite link. Earn points when your invite is used to register and complete a quiz!</p>
  </div>
  <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
      <input id="inviteLink" class="flex-1 px-4 py-3 rounded-xl bg-white/10 border border-white/10" value="<?= h($inviteUrl) ?>" readonly />
      <button class="px-6 py-3 rounded-xl bg-violet hover:bg-plum font-semibold" onclick="copyToClipboard(document.getElementById('inviteLink').value, (ok)=>{ this.textContent = ok? 'Copied!':'Copy Failed'; setTimeout(()=>this.textContent='Copy Link',1500); })">Copy Link</button>
    </div>
    <p class="mt-3 text-sm text-white/70">Your code: <span class="font-mono bg-white/10 px-2 py-1 rounded"><?= h($invite['code']) ?></span></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
