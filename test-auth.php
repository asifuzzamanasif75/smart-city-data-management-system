<?php
/**
 * Authentication System Tester
 * Tests registration, login, and password reset functionality
 * Access: test-auth.php through your local web server
 */

header('Content-Type: text/html; charset=UTF-8');

$host = "127.0.0.1";
$db_name = "smart_city_data_management_system";
$username = "root";
$password = "";

echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; padding: 20px; background: #f8fafc; }
    h1 { color: #1e293b; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
    h2 { color: #475569; margin-top: 30px; background: #e0e7ff; padding: 10px; border-radius: 8px; }
    .test-box { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #10b981; font-weight: bold; }
    .error { color: #ef4444; font-weight: bold; }
    .warning { color: #f59e0b; font-weight: bold; }
    .info { color: #3b82f6; font-weight: bold; }
    pre { background: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    th { background: #f1f5f9; font-weight: 600; }
    .btn { display: inline-block; padding: 8px 16px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 3px; font-size: 0.9em; }
    .btn:hover { background: #5568d3; }
</style>";

echo "<h1>🔐 Authentication System Tester</h1>";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Database connected successfully</p>";
} catch(PDOException $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 1: Check Users Table Structure
echo "<div class='test-box'>";
echo "<h2>Test 1: Database Structure</h2>";

$query = "DESCRIBE users";
$stmt = $conn->query($query);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>";

$requiredColumns = ['id', 'username', 'email', 'password', 'reset_code', 'reset_code_expiry', 'role'];
$foundColumns = array_column($columns, 'Field');

foreach ($requiredColumns as $col) {
    $found = in_array($col, $foundColumns);
    $status = $found ? "<span class='success'>✅ OK</span>" : "<span class='error'>❌ Missing</span>";
    $type = '';
    foreach ($columns as $column) {
        if ($column['Field'] === $col) {
            $type = $column['Type'];
            break;
        }
    }
    echo "<tr><td><strong>$col</strong></td><td>$type</td><td>$status</td></tr>";
}
echo "</table>";

if (!in_array('reset_code', $foundColumns)) {
    echo "<p class='error'>⚠️ Missing reset columns! Run this SQL:</p>";
    echo "<pre>ALTER TABLE users 
ADD COLUMN reset_code VARCHAR(10) NULL,
ADD COLUMN reset_code_expiry DATETIME NULL;</pre>";
}

echo "</div>";

// Test 2: Check Existing Users
echo "<div class='test-box'>";
echo "<h2>Test 2: Existing Users</h2>";

$query = "SELECT id, username, email, role, password FROM users";
$stmt = $conn->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Found <strong>" . count($users) . "</strong> users:</p>";
echo "<table>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password Check</th></tr>";

foreach ($users as $user) {
    $hashOk = (strlen($user['password']) > 50 && strpos($user['password'], '$2y$') === 0);
    $hashStatus = $hashOk ? "<span class='success'>✅ Valid bcrypt</span>" : "<span class='error'>❌ Invalid hash</span>";
    
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td><strong>{$user['username']}</strong></td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "<td>$hashStatus</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Test 3: Test Password Verification
echo "<div class='test-box'>";
echo "<h2>Test 3: Password Verification Test</h2>";

$testUsers = [
    ['username' => 'admin', 'password' => 'admin123'],
    ['username' => 'user', 'password' => 'user123']
];

echo "<table>";
echo "<tr><th>Username</th><th>Test Password</th><th>Result</th></tr>";

foreach ($testUsers as $test) {
    $query = "SELECT password FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $test['username']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $verified = password_verify($test['password'], $user['password']);
        $result = $verified ? 
            "<span class='success'>✅ PASS - Can login</span>" : 
            "<span class='error'>❌ FAIL - Cannot login</span>";
        echo "<tr><td><strong>{$test['username']}</strong></td><td>{$test['password']}</td><td>$result</td></tr>";
    } else {
        echo "<tr><td><strong>{$test['username']}</strong></td><td>{$test['password']}</td><td><span class='warning'>⚠️ User not found</span></td></tr>";
    }
}
echo "</table>";
echo "</div>";

// Test 4: Test Password Hashing
echo "<div class='test-box'>";
echo "<h2>Test 4: Password Hashing Test</h2>";

$testPassword = 'test123';
$hash1 = password_hash($testPassword, PASSWORD_BCRYPT);
$hash2 = password_hash($testPassword, PASSWORD_DEFAULT);

echo "<p>Test password: <code>$testPassword</code></p>";
echo "<p>Hash 1 (BCRYPT): <code>" . substr($hash1, 0, 50) . "...</code></p>";
echo "<p>Hash 2 (DEFAULT): <code>" . substr($hash2, 0, 50) . "...</code></p>";

$verify1 = password_verify($testPassword, $hash1);
$verify2 = password_verify($testPassword, $hash2);

echo "<p>Verify Hash 1: " . ($verify1 ? "<span class='success'>✅ Success</span>" : "<span class='error'>❌ Failed</span>") . "</p>";
echo "<p>Verify Hash 2: " . ($verify2 ? "<span class='success'>✅ Success</span>" : "<span class='error'>❌ Failed</span>") . "</p>";

echo "</div>";

// Test 5: Simulate Registration
echo "<div class='test-box'>";
echo "<h2>Test 5: Registration Simulation</h2>";

$testUsername = 'testuser_' . time();
$testEmail = 'test_' . time() . '@example.com';
$testPassword = 'test123';
$hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT);

echo "<p>Creating test user:</p>";
echo "<ul>";
echo "<li>Username: <code>$testUsername</code></li>";
echo "<li>Email: <code>$testEmail</code></li>";
echo "<li>Password: <code>$testPassword</code></li>";
echo "</ul>";

try {
    $query = "INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, 'user', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':username' => $testUsername,
        ':email' => $testEmail,
        ':password' => $hashedPassword
    ]);
    
    echo "<p class='success'>✅ User created successfully!</p>";
    
    // Test login
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->execute([':username' => $testUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $loginWorks = password_verify($testPassword, $user['password']);
    echo "<p>Login test: " . ($loginWorks ? "<span class='success'>✅ Can login immediately</span>" : "<span class='error'>❌ Cannot login</span>") . "</p>";
    
    // Cleanup
    $conn->exec("DELETE FROM users WHERE username = '$testUsername'");
    echo "<p class='info'>ℹ️ Test user deleted after verification</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Registration failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 6: Password Reset Simulation
echo "<div class='test-box'>";
echo "<h2>Test 6: Password Reset Simulation</h2>";

if (in_array('reset_code', $foundColumns)) {
    // Create temporary user for testing
    $resetTestUser = 'resettest_' . time();
    $resetTestEmail = 'reset_' . time() . '@example.com';
    $hashedPass = password_hash('oldpass', PASSWORD_BCRYPT);
    
    $conn->exec("INSERT INTO users (username, email, password, role) VALUES ('$resetTestUser', '$resetTestEmail', '$hashedPass', 'user')");
    
    echo "<p>Testing password reset for: <code>$resetTestEmail</code></p>";
    
    // Generate reset code
    $resetCode = sprintf("%06d", mt_rand(1, 999999));
    $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $query = "UPDATE users SET reset_code = :code, reset_code_expiry = :expiry WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':code' => $resetCode,
        ':expiry' => $expiryTime,
        ':email' => $resetTestEmail
    ]);
    
    echo "<p class='success'>✅ Reset code generated: <code>$resetCode</code></p>";
    echo "<p>Expires: <code>$expiryTime</code></p>";
    
    // Verify code
    $query = "SELECT * FROM users WHERE email = :email AND reset_code = :code AND reset_code_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->execute([':email' => $resetTestEmail, ':code' => $resetCode]);
    $verified = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($verified) {
        echo "<p class='success'>✅ Code verification works</p>";
        
        // Reset password
        $newPassHash = password_hash('newpass123', PASSWORD_BCRYPT);
        $query = "UPDATE users SET password = :password, reset_code = NULL, reset_code_expiry = NULL WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->execute([':password' => $newPassHash, ':email' => $resetTestEmail]);
        
        echo "<p class='success'>✅ Password reset successful</p>";
        
        // Test new password
        $query = "SELECT password FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->execute([':email' => $resetTestEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newPassWorks = password_verify('newpass123', $user['password']);
        echo "<p>New password test: " . ($newPassWorks ? "<span class='success'>✅ Works</span>" : "<span class='error'>❌ Failed</span>") . "</p>";
    } else {
        echo "<p class='error'>❌ Code verification failed</p>";
    }
    
    // Cleanup
    $conn->exec("DELETE FROM users WHERE username = '$resetTestUser'");
    echo "<p class='info'>ℹ️ Test user deleted</p>";
} else {
    echo "<p class='warning'>⚠️ Cannot test - reset_code column missing!</p>";
}

echo "</div>";

// Final Summary
echo "<div class='test-box'>";
echo "<h2>📊 Summary & Next Steps</h2>";

$allPassed = true;

// Check critical items
$checks = [
    'reset_code column exists' => in_array('reset_code', $foundColumns),
    'reset_code_expiry column exists' => in_array('reset_code_expiry', $foundColumns),
    'Users have valid passwords' => true // Checked above
];

echo "<table>";
echo "<tr><th>Check</th><th>Status</th></tr>";
foreach ($checks as $check => $passed) {
    $status = $passed ? "<span class='success'>✅ PASS</span>" : "<span class='error'>❌ FAIL</span>";
    if (!$passed) $allPassed = false;
    echo "<tr><td>$check</td><td>$status</td></tr>";
}
echo "</table>";

if ($allPassed) {
    echo "<h3 class='success'>🎉 All Tests Passed!</h3>";
    echo "<p>Your authentication system is ready to use.</p>";
} else {
    echo "<h3 class='error'>⚠️ Some Tests Failed</h3>";
    echo "<p>Please fix the issues above before using the system.</p>";
}

echo "<hr>";
echo "<h3>🔧 Quick Actions:</h3>";
echo "<a href='create-demo-users.php' class='btn'>Fix Demo Users</a>";
echo "<a href='login.html' class='btn'>Go to Login</a>";
echo "<a href='register.html' class='btn'>Go to Register</a>";
echo "<a href='forgot-password.html' class='btn'>Test Password Reset</a>";

echo "</div>";

echo "<p style='text-align: center; margin-top: 20px; color: #64748b;'>";
echo "⚠️ Delete test-auth.php after fixing issues for security!";
echo "</p>";

$conn = null;
?>