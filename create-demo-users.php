<?php
/**
 * Create Demo Users Script
 * Run this file once to create properly hashed demo user accounts
 * Access: create-demo-users.php through your local web server
 */

header('Content-Type: text/html; charset=UTF-8');

// Database configuration
$host = "127.0.0.1";
$db_name = "smart_city_data_management_system";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name,
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Creating Demo Users</h2>";
    echo "<hr>";
    
    // Delete existing demo users
    $deleteQuery = "DELETE FROM users WHERE username IN ('admin', 'user', 'testuser')";
    $conn->exec($deleteQuery);
    echo "<p>✅ Cleared existing demo users</p>";
    
    // Create admin user
    $adminUsername = 'admin';
    $adminEmail = 'admin@smartcity.gov';
    $adminPassword = 'admin123';
    $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    $adminQuery = "INSERT INTO users (username, email, password, role, created_at) 
                   VALUES (:username, :email, :password, 'admin', NOW())";
    $stmt = $conn->prepare($adminQuery);
    $stmt->execute([
        ':username' => $adminUsername,
        ':email' => $adminEmail,
        ':password' => $adminHash
    ]);
    
    echo "<p>✅ <strong>Admin user created:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "<li>Role: admin</li>";
    echo "</ul>";
    
    // Create regular user
    $userUsername = 'user';
    $userEmail = 'user@smartcity.gov';
    $userPassword = 'user123';
    $userHash = password_hash($userPassword, PASSWORD_BCRYPT);
    
    $userQuery = "INSERT INTO users (username, email, password, role, created_at) 
                  VALUES (:username, :email, :password, 'user', NOW())";
    $stmt = $conn->prepare($userQuery);
    $stmt->execute([
        ':username' => $userUsername,
        ':email' => $userEmail,
        ':password' => $userHash
    ]);
    
    echo "<p>✅ <strong>Regular user created:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>user</code></li>";
    echo "<li>Password: <code>user123</code></li>";
    echo "<li>Role: user</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>🎉 Success! Demo users created successfully!</h3>";
    echo "<p><strong>You can now login with:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: <code>admin</code> / <code>admin123</code></li>";
    echo "<li>User: <code>user</code> / <code>user123</code></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='login.html' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Go to Login Page</a></p>";
    
    echo "<hr>";
    echo "<p style='color: #ef4444;'><strong>⚠️ IMPORTANT:</strong> Delete this file (create-demo-users.php) after running it for security!</p>";
    
    // Verify passwords work
    echo "<hr>";
    echo "<h3>🔍 Password Verification Test:</h3>";
    
    // Test admin password
    $testQuery = "SELECT password FROM users WHERE username = 'admin'";
    $stmt = $conn->query($testQuery);
    $adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('admin123', $adminRow['password'])) {
        echo "<p>✅ Admin password verification: <strong style='color: #10b981;'>SUCCESS</strong></p>";
    } else {
        echo "<p>❌ Admin password verification: <strong style='color: #ef4444;'>FAILED</strong></p>";
    }
    
    // Test user password
    $testQuery = "SELECT password FROM users WHERE username = 'user'";
    $stmt = $conn->query($testQuery);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('user123', $userRow['password'])) {
        echo "<p>✅ User password verification: <strong style='color: #10b981;'>SUCCESS</strong></p>";
    } else {
        echo "<p>❌ User password verification: <strong style='color: #ef4444;'>FAILED</strong></p>";
    }
    
    echo "<hr>";
    echo "<p><strong>All users registered after this will also work correctly!</strong></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database 'smart_city_data_management_system' exists</li>";
    echo "<li>Database credentials are correct</li>";
    echo "</ul>";
}

echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
    ul { line-height: 1.8; }
    h2, h3 { color: #1e293b; }
</style>";
?>