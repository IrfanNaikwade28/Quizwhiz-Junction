<?php
require_once __DIR__ . '/config.php';

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        regenerate_csrf_token();
    }
    return $_SESSION['csrf_token'];
}

function regenerate_csrf_token(): void {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function h(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function format_time(int $seconds): string {
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    return sprintf('%02dm %02ds', $m, $s);
}

// Ranking helper
function compute_user_rank(int $userId): ?array {
    $sql = "SELECT u.id, u.name, u.points, COALESCE(SUM(a.score),0) AS total_score, COALESCE(SUM(a.total_time),0) AS total_time
            FROM users u LEFT JOIN attempts a ON u.id = a.user_id GROUP BY u.id";
    // Exclude admins from ranking
    $sql = "SELECT u.id, u.name, u.points, COALESCE(SUM(a.score),0) AS total_score, COALESCE(SUM(a.total_time),0) AS total_time
            FROM users u LEFT JOIN attempts a ON u.id = a.user_id WHERE u.is_admin=0 GROUP BY u.id";
    $rows = db()->query($sql)->fetchAll();
    foreach ($rows as &$r) {
        $r['rank_score'] = $r['total_score'];
        $r['rank_time'] = $r['total_time'];
    }
    // Sort: score desc, time asc
    usort($rows, function($a,$b){
        if ($a['rank_score'] === $b['rank_score']) {
            return $a['rank_time'] <=> $b['rank_time'];
        }
        return $b['rank_score'] <=> $a['rank_score'];
    });
    $rank = 1;
    foreach ($rows as &$r) {
        $r['rank'] = $rank++;
        if ($r['id'] == $userId) return $r;
    }
    return null;
}

function grant_invite_points(int $inviterId, int $newUserId): void {
    // Award points only once per invited user when they complete first attempt with score>0
    $check = db()->prepare('SELECT invited_by FROM users WHERE id=?');
    $check->execute([$newUserId]);
    $invitedBy = $check->fetchColumn();
    if ($invitedBy && (int)$invitedBy === $inviterId) {
        // Check if already rewarded
        $rewarded = db()->prepare('SELECT COUNT(*) FROM attempts WHERE user_id=?');
        $rewarded->execute([$newUserId]);
        $countAttempts = (int)$rewarded->fetchColumn();
        if ($countAttempts === 1) { // first attempt just recorded
            db()->prepare('UPDATE users SET points = points + 25 WHERE id=?')->execute([$inviterId]);
        }
    }
}

function compute_local_rank(int $userId): array {
    // Local rank among 20 users with most recent activity (latest attempt), avoids LIMIT in IN-subquery (MariaDB restriction)
    $sql = "SELECT u.id, u.name, COALESCE(SUM(a.score),0) AS total_score, COALESCE(SUM(a.total_time),0) AS total_time
            FROM users u
            JOIN (
                SELECT user_id, MAX(id) AS last_attempt
                FROM attempts
                GROUP BY user_id
                ORDER BY last_attempt DESC
                LIMIT 20
            ) lu ON lu.user_id = u.id
            LEFT JOIN attempts a ON a.user_id = u.id
        WHERE u.is_admin=0
        GROUP BY u.id, u.name";
    $stmt = db()->query($sql);
    $rows = $stmt ? $stmt->fetchAll() : [];
    foreach ($rows as &$r) { $r['rank_score']=$r['total_score']; $r['rank_time']=$r['total_time']; }
    usort($rows, function($a,$b){ return $a['rank_score']===$b['rank_score'] ? $a['rank_time'] <=> $b['rank_time'] : $b['rank_score'] <=> $a['rank_score']; });
    $rank=1; $found=null; foreach($rows as &$r){ $r['rank']=$rank++; if($r['id']==$userId) $found=$r; }
    return ['me'=>$found,'list'=>$rows];
}

// ----------------------------
// Role & Capability Management
// ----------------------------
// Central definition of capabilities. This allows future expansion (e.g., moderator, editor).
// Capability keys should be verbs or permission tokens consumed by UI or controllers.
function role_capabilities(): array {
    return [
        'user' => [
            // Final specification: Users perform gameplay; Admins manage system only (no attempts, invisible in rankings)
            'quiz.attempt',
            'ranking.view',
            'user.view', // own / basic user visibility
            'history.view',
            'invite.generate'
        ],
        'admin' => [
            // Admin: full capabilities including CRUD on users and quizzes
            // Management only; intentionally no 'quiz.attempt' and no 'ranking.view' to keep leaderboards user-focused
            'quiz.create','quiz.update','quiz.delete',
            'question.create','question.update','question.delete',
            'option.create','option.delete',
            'user.manage',
            'ranking.manage'
        ]
    ];
}

function user_role(?array $user): string {
    if (!$user) return 'guest';
    return ($user['is_admin'] ?? 0) ? 'admin' : 'user';
}

function user_has_capability(string $cap, ?array $user): bool {
    if (!$user) return false;
    $caps = role_capabilities();
    $role = user_role($user);
    return isset($caps[$role]) && in_array($cap, $caps[$role], true);
}

// Convenience wrappers for common checks
function can_manage_quizzes(?array $user): bool { return user_has_capability('quiz.create', $user); }
function can_manage_users(?array $user): bool { return user_has_capability('user.manage', $user); }
function can_view_users(?array $user): bool { return user_has_capability('user.view', $user); }
function can_view_ranking(?array $user): bool { return user_has_capability('ranking.view', $user); }
function can_attempt_quiz(?array $user): bool { return user_has_capability('quiz.attempt', $user); }


?>
