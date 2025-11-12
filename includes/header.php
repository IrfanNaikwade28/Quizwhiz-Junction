<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              lavender: '#E6E0FF',
              violet: '#7C3AED',
              plum: '#A78BFA',
              midnight: '#1E1B2E'
            },
            backgroundImage: theme => ({
              'gradient-purple': 'linear-gradient(135deg, #7C3AED 0%, #A78BFA 50%, #E6E0FF 100%)',
              'card-glow': 'radial-gradient(circle at 30% 30%, rgba(255,255,255,0.35), rgba(124,58,237,0.25))'
            }),
            boxShadow: {
              'soft': '0 4px 24px -2px rgba(124,58,237,0.25)',
              'inner-glow': 'inset 0 0 8px rgba(255,255,255,0.4)'
            },
            borderRadius: {
              'xl': '1.25rem'
            }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
      body { font-family: 'Inter', sans-serif; }
      .gradient-border { position: relative; }
  .gradient-border:before { content: ''; position: absolute; inset: 0; padding: 2px; border-radius: inherit; background: linear-gradient(135deg,#7C3AED,#A78BFA,#E6E0FF); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; }
    </style>
</head>
<body class="min-h-screen bg-midnight text-white flex flex-col">
<header class="w-full bg-gradient-purple shadow-soft">
  <div class="max-w-7xl mx-auto px-8 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center font-bold text-lg">QW</div>
      <span class="font-semibold text-lg tracking-wide">Quizwhiz Junction</span>
    </div>
    <nav class="hidden md:flex gap-6 text-sm font-medium">
      <?php $cu = current_user(); $adm = current_admin(); $isAdm = is_admin(); ?>
      <?php if($isAdm): ?>
        <!-- Admin Navbar: management only -->
        <a href="<?= url('admin/users.php') ?>" class="hover:text-lavender transition">View Users</a>
        <a href="<?= url('admin/ranks.php') ?>" class="hover:text-lavender transition">Ranking</a>
        <a href="<?= url('admin/quizzes.php') ?>" class="hover:text-lavender transition">Add Quiz</a>
        <a href="<?= url('admin/remove_quiz.php') ?>" class="hover:text-lavender transition">Remove Quiz</a>
        <span class="px-2 py-1 rounded bg-white/10 text-xs self-center">Admin</span>
        <form method="post" action="<?= url('logout.php') ?>" class="inline">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <button class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl">Logout</button>
        </form>
      <?php elseif($cu): ?>
        <!-- User Navbar: gameplay & profile -->
        <a href="<?= url('index.php') ?>" class="hover:text-lavender transition">Home</a>
        <a href="<?= url('dashboard.php') ?>" class="hover:text-lavender transition">Dashboard</a>
        <a href="<?= url('quizzes.php') ?>" class="hover:text-lavender transition">Quizzes</a>
        <a href="<?= url('history.php') ?>" class="hover:text-lavender transition">History</a>
        <a href="<?= url('invite.php') ?>" class="hover:text-lavender transition">Invite</a>
        <form method="post" action="<?= url('logout.php') ?>" class="inline">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <button class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl">Logout</button>
        </form>
      <?php else: ?>
        <!-- Guest Navbar -->
        <a href="<?= url('index.php') ?>" class="hover:text-lavender transition">Home</a>
        <a href="<?= url('user_login.php') ?>" class="hover:text-lavender transition">User Login</a>
        <a href="<?= url('user_register.php') ?>" class="hover:text-lavender transition">Register</a>
        <a href="<?= url('admin_login.php') ?>" class="hover:text-lavender transition">Admin Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="flex-1 w-full max-w-7xl mx-auto px-8 py-10">
