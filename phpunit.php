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
$devNull = (PHP_OS_FAMILY === 'Windows') ? 'NUL' : '/dev/null';
$process = proc_open(
    [PHP_BINARY, '-S', WEB_SERVER_HOST . ':' . WEB_SERVER_PORT, '-t', WEB_SERVER_DOCROOT],
    [['pipe', 'r'], ['file', $devNull, 'w'], ['file', $devNull, 'w']],
    $pipes
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
register_shutdown_function(static function () use ($process): void {
    proc_terminate($process);
    proc_close($process);
});
