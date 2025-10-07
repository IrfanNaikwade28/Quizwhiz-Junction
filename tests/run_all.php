<?php
// Automated test runner: discovers and runs each test file in a separate PHP process
// Works from CLI or when opened via a browser (renders plain text)

function findPhpExecutable(): string {
    $candidates = [
        'C:\\xampp\\php\\php.exe',
        PHP_BINARY, // current interpreter if CLI
        'php',
    ];
    foreach ($candidates as $exe) {
        if (!$exe) continue;
        // If path-like, check file exists; 'php' will be tried anyway
        if ($exe === 'php') return $exe;
        if (is_file($exe)) return $exe;
    }
    return 'php';
}

function discoverTests(string $testsDir): array {
    $list = [];
    $dirs = [$testsDir, $testsDir.DIRECTORY_SEPARATOR.'unit'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*.php') as $file) {
            $base = basename($file);
            if (in_array($base, ['run.php','run_all.php'], true)) continue;
            $list[] = $file;
        }
    }
    sort($list, SORT_NATURAL | SORT_FLAG_CASE);
    return $list;
}

function runOne(string $php, string $file): array {
    $cmd = $php.' -d display_errors=1 -d error_reporting=E_ALL -f '.escapeshellarg($file);
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);
    return [$code, implode("\n", $out)];
}

$root = realpath(__DIR__.DIRECTORY_SEPARATOR.'..');
$testsDir = __DIR__;
$php = findPhpExecutable();
$tests = discoverTests($testsDir);

$lines = [];
$lines[] = 'PHP:      '.(string)$php;
$lines[] = 'Workspace: '.(string)$root;
$lines[] = '---';

$pass = 0; $fail = 0; $results = [];
foreach ($tests as $file) {
    [$code, $output] = runOne($php, $file);
    $ok = $code === 0;
    $status = $ok ? 'PASS' : 'FAIL';
    $results[] = [basename($file), $status, $output];
    if ($ok) $pass++; else $fail++;
}

foreach ($results as [$name, $status, $output]) {
    $lines[] = sprintf('%-28s %s', $name.':', $status);
    // show a compact first line of output if any
    if (strlen(trim($output)) > 0) {
        $first = strtok($output, "\n");
        $lines[] = '  > '.$first;
    }
}
$lines[] = '---';
$lines[] = sprintf('Summary: %d passed, %d failed, %d total', $pass, $fail, $pass+$fail);

$body = implode("\n", $lines)."\n";
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}
echo $body;

if (PHP_SAPI === 'cli') {
    exit($fail > 0 ? 1 : 0);
}
