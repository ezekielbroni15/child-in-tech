<?php
// generate-password.php
// Run this ONCE in your browser to set the admin password
// Then DELETE this file!
// URL: http://localhost/tech/generate-password.php

$password = 'citadmin2026';  // Change this to your desired password
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Admin Password Setup</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong> <code>" . htmlspecialchars($hash) . "</code></p>";
echo "<br><p>Run this SQL in phpMyAdmin to update the admin password:</p>";
echo "<pre>";
echo "UPDATE admin_users SET password_hash = '" . htmlspecialchars($hash) . "' WHERE username = 'admin';";
echo "</pre>";
echo "<p style='color:red'><strong>DELETE this file after you are done!</strong></p>";
?>
