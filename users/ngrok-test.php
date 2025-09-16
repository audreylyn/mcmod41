<?php
// Simple test file to check if ngrok is working
header('ngrok-skip-browser-warning: true');
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Ngrok Test</title></head><body>";
echo "<h1>Ngrok Connection Test</h1>";
echo "<p>If you can see this, ngrok is working!</p>";
echo "<p>Server: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
