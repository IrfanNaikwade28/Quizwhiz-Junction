<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';
$user = current_user();
$rankInfo = can_view_ranking($user) ? compute_user_rank($user['id']) : null;
$local = can_view_ranking($user) ? compute_local_rank($user['id']) : ['me'=>null,'list'=>[]];
$recent = db()->prepare('SELECT a.id, a.score, a.total_time, q.title, a.created_at FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE a.user_id=? ORDER BY a.id DESC LIMIT 6');
$recent->execute([$user['id']]);
$recentAttempts = $recent->fetchAll();
?>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
  <div class="xl:col-span-2 space-y-8">
    <div class="grid md:grid-cols-3 gap-6">
      <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
        <h2 class="text-sm font-medium mb-2">Global Rank</h2>
        <p class="text-3xl font-bold"><?= $rankInfo ? $rankInfo['rank'] : '—' ?></p>
      </div>
      <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
        <h2 class="text-sm font-medium mb-2">Total Score</h2>
        <p class="text-3xl font-bold"><?= $rankInfo ? $rankInfo['total_score'] : 0 ?></p>
      </div>
      <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
        <h2 class="text-sm font-medium mb-2">Total Time</h2>
        <p class="text-3xl font-bold"><?= $rankInfo ? format_time((int)$rankInfo['total_time']) : '00m 00s' ?></p>
      </div>
    </div>
    <div class="rounded-2xl p-6 bg-card-glow border border-white/10 shadow-soft">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Recent Attempts</h2>
        <a class="text-sm text-plum" href="<?= url('history.php') ?>">View All</a>
      </div>
      <ul class="space-y-3">
        <?php foreach($recentAttempts as $ra): ?>
          <li class="flex items-center justify-between bg-white/5 rounded-xl px-4 py-3">
            <div class="flex flex-col">
              <span class="font-medium"><?= h($ra['title']) ?></span>
              <span class="text-xs text-white/60">Score <?= (int)$ra['score'] ?> • <?= format_time((int)$ra['total_time']) ?></span>
            </div>
            <span class="text-xs text-white/50"><?= h(substr($ra['created_at'],0,16)) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if(empty($recentAttempts)): ?><li class="text-white/60">No attempts yet. Start a quiz!</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="space-y-8">
    <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
      <h2 class="text-xl font-semibold mb-4">Local Rank (Top 20 Active)</h2>
      <?php if($local['me']): ?>
        <div class="text-sm mb-3">You are <span class="font-semibold">#<?= (int)$local['me']['rank'] ?></span> locally.</div>
      <?php elseif(!can_view_ranking($user)): ?>
        <div class="text-xs text-white/50 italic mb-3">Ranking hidden for admin accounts.</div>
      <?php endif; ?>
      <ul class="space-y-2 max-h-64 overflow-auto pr-2">
        <?php foreach($local['list'] as $r): ?>
          <li class="flex items-center justify-between bg-white/5 rounded-xl px-3 py-2 text-sm">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 rounded bg-violet/40 flex items-center justify-center text-xs font-bold">#<?= (int)$r['rank'] ?></span>
              <span class="font-medium <?= $r['id']==$user['id']?'text-plum':'' ?>"><?= h($r['name']) ?></span>
            </div>
            <div class="text-white/60">S <?= (int)$r['total_score'] ?> • <?= format_time((int)$r['total_time']) ?></div>
          </li>
        <?php endforeach; ?>
        <?php if(empty($local['list']) && can_view_ranking($user)): ?>
          <li class="text-white/60">No local ranking yet.</li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
      <h2 class="text-xl font-semibold mb-4">Profile</h2>
      <div class="flex items-center gap-4 mb-4">
        <?php $seed = $user['avatar_seed']; $color = substr(md5($seed),0,6); ?>
        <div class="w-16 h-16 rounded-2xl bg-[#<?= $color ?>]/40 flex items-center justify-center text-xl font-bold shadow-inner-glow"><?= h(strtoupper(substr($user['name'],0,1))) ?></div>
        <div>
          <p class="font-semibold text-lg"><?= h($user['name']) ?></p>
          <p class="text-sm text-white/60">Points: <?= (int)$user['points'] ?></p>
        </div>
      </div>
      <div class="space-y-2">
        <div class="text-sm text-white/70">Badges (coming soon)</div>
        <div class="flex gap-2 text-xs">
          <span class="px-3 py-1 rounded-full bg-violet/30">Novice</span>
          <span class="px-3 py-1 rounded-full bg-plum/30">Early Bird</span>
        </div>
      </div>
    </div>
    <div class="rounded-2xl p-6 bg-white/5 border border-white/10 shadow-soft">
      <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
      <div class="flex flex-col gap-3">
        <a href="<?= url('quizzes.php') ?>" class="px-4 py-3 rounded-xl bg-violet hover:bg-plum font-semibold text-center">Browse Quizzes</a>
        <a href="<?= url('invite.php') ?>" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 font-semibold text-center">Invite Friends</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
