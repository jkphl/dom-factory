<?php

// Serves an ISO-8859-2 encoded document declaring the wrong charset in-document (the HTTP header takes precedence)
header('Content-Type: text/html; charset=ISO-8859-2');
echo "<!DOCTYPE html>\n<html>\n<head>\n<meta charset=\"utf-8\">\n<title>Test</title>\n</head>\n";
echo "<body>\n<p>\xB9\xE8</p>\n</body>\n</html>\n";
