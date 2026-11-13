<?php

error_reporting(E_ALL);

chdir(__DIR__);

$autoloader = __DIR__.'/vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo "Composer autoloader not found: $autoloader".PHP_EOL;
    echo "Please issue 'composer install' and try again.".PHP_EOL;
    exit(1);
}
require $autoloader;

// Start the built-in web server (without an intermediate shell, so that it can be terminated reliably)
// (string command with "exec" instead of an argument array to stay compatible with PHP < 7.4)
$isWindows = (PHP_OS_FAMILY === 'Windows');
$devNull   = $isWindows ? 'NUL' : '/dev/null';
$command   = sprintf(
    '%s%s -S %s -t %s',
    $isWindows ? '' : 'exec ',
    escapeshellarg(PHP_BINARY),
    escapeshellarg(WEB_SERVER_HOST.':'.WEB_SERVER_PORT),
    escapeshellarg(WEB_SERVER_DOCROOT)
);
$process   = proc_open(
    $command,
    [['pipe', 'r'], ['file', $devNull, 'w'], ['file', $devNull, 'w']],
    $pipes,
    null,
    null,
    ['bypass_shell' => true]
);

if (!is_resource($process)) {
    echo 'Failed to start the built-in web server'.PHP_EOL;
    exit(1);
}

// Wait until the web server accepts connections
for ($attempt = 0; $attempt < 50; ++$attempt) {
    $socket = @fsockopen(WEB_SERVER_HOST, (int)WEB_SERVER_PORT);
    if ($socket) {
        fclose($socket);
        break;
    }
    usleep(100000);
}

// Stop the built-in web server on shutdown
register_shutdown_function(static function () use ($process) {
    proc_terminate($process);
    proc_close($process);
});
