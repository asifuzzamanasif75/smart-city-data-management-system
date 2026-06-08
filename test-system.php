<?php
/**
 * Complete System Test & Debug Tool
 * Run this to diagnose and fix issues
 * Access: test-system.php through your local web server
 */

header('Content-Type: text/html; charset=UTF-8');

// Database configuration
$host = "127.0.0.1";
$db_name = "smart_city_data_management_system";
$username = "root";
$password = "";

$allTestsPassed = true;

echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f8fafc; }
    h1 { color: #1e293b; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
    h2 { color: #475569; margin-top: 30px; }
    .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #10b981; font-weight: bold; }
    .error { color: #ef4444; font-weight: bold; }
    .warning { color: #f59e0b; font-weight: bold; }
    .info { color: #3b82f6; font-weight: bold; }
    pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    th { background: #f1f5f9; font-weight: 600; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 5px; }
    .btn:hover { background: #5568d3; }
</style>";

echo "<h1>🔧 Smart City Data Management System - System Test</h1>";

// Test 1: Database Connection
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Connection</h2>";

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name,
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Database connection successful!</p>";
    echo "<p>Database: <strong>$db_name</strong></p>";
} catch(PDOException $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
    exit;
}

// Test 2: Check Tables
echo "</div><div class='test-section'>";
echo "<h2>Test 2: Database Tables</h2>";

$requiredTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens', 'users'];
$tablesOk = true;

foreach ($requiredTables as $table) {
    $query = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($query);
    if ($result->rowCount() > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' missing</p>";
        $tablesOk = false;
        $allTestsPassed = false;
    }
}

// Test 3: Check Users Table
echo "</div><div class='test-section'>";
echo "<h2>Test 3: Users & Passwords</h2>";

$query = "SELECT id, username, email, role, password FROM users";
$stmt = $conn->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Found <strong>" . count($users) . "</strong> users:</p>";
echo "<table>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password Hash</th><th>Test Login</th></tr>";

$testPasswords = [
    'admin' => 'admin123',
    'user' => 'user123'
];

foreach ($users as $user) {
    $hashOk = (strlen($user['password']) > 50 && strpos($user['password'], '$2y$') === 0);
    $hashStatus = $hashOk ? "<span class='success'>✅ Valid</span>" : "<span class='error'>❌ Invalid</span>";
    
    $passwordTest = '';
    if (isset($testPasswords[$user['username']])) {
        $testPass = $testPasswords[$user['username']];
        if (password_verify($testPass, $user['password'])) {
            $passwordTest = "<span class='success'>✅ '$testPass' works</span>";
        } else {
            $passwordTest = "<span class='error'>❌ '$testPass' fails</span>";
            $allTestsPassed = false;
        }
    }
    
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td><strong>{$user['username']}</strong></td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "<td>" . substr($user['password'], 0, 30) . "... $hashStatus</td>";
    echo "<td>$passwordTest</td>";
    echo "</tr>";
}
echo "</table>";

// Test 4: Test Password Creation
echo "</div><div class='test-section'>";
echo "<h2>Test 4: Password Hashing Test</h2>";

$testPassword = 'test123';
$hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT);
$verifyResult = password_verify($testPassword, $hashedPassword);

echo "<p>Test password: <code>$testPassword</code></p>";
echo "<p>Generated hash: <code>" . substr($hashedPassword, 0, 50) . "...</code></p>";
echo "<p>Verification: " . ($verifyResult ? "<span class='success'>✅ SUCCESS</span>" : "<span class='error'>❌ FAILED</span>") . "</p>";

// Test 5: Check Data Records
echo "</div><div class='test-section'>";
echo "<h2>Test 5: Data Records Count</h2>";

$dataTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
echo "<table>";
echo "<tr><th>Table</th><th>Records</th><th>Status</th></tr>";

foreach ($dataTables as $table) {
    $query = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($query);
    $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    $status = $count > 0 ? "<span class='success'>✅ OK</span>" : "<span class='warning'>⚠️ Empty</span>";
    echo "<tr><td><strong>$table</strong></td><td>$count</td><td>$status</td></tr>";
}
echo "</table>";

// Test 6: Test API Endpoints
echo "</div><div class='test-section'>";
echo "<h2>Test 6: API Files Check</h2>";

$apiFiles = [
    'api.php' => 'Main API',
    'auth.php' => 'Authentication',
    'api-handler.js' => 'Frontend Handler',
    'script.js' => 'Main Script',
    'config.php' => 'Configuration'
];

foreach ($apiFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file ($description) - Found</p>";
    } else {
        echo "<p class='error'>❌ $file ($description) - Missing</p>";
        $allTestsPassed = false;
    }
}

// Test 7: Sample Login Test
echo "</div><div class='test-section'>";
echo "<h2>Test 7: Direct Login Test</h2>";

$testLoginUsername = 'admin';
$testLoginPassword = 'admin123';

$query = "SELECT * FROM users WHERE username = :username LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $testLoginUsername);
$stmt->execute();
$testUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($testUser) {
    echo "<p>Testing login for: <strong>$testLoginUsername</strong></p>";
    
    if (password_verify($testLoginPassword, $testUser['password'])) {
        echo "<p class='success'>✅ Login test PASSED!</p>";
        echo "<p>Username: <code>$testLoginUsername</code> / Password: <code>$testLoginPassword</code> works correctly</p>";
    } else {
        echo "<p class='error'>❌ Login test FAILED!</p>";
        echo "<p>Password verification failed. You need to run create-demo-users.php</p>";
        $allTestsPassed = false;
    }
} else {
    echo "<p class='error'>❌ User '$testLoginUsername' not found in database</p>";
    $allTestsPassed = false;
}

// Test 8: Check Sample Data IDs
echo "</div><div class='test-section'>";
echo "<h2>Test 8: Sample Records & IDs</h2>";

$query = "SELECT id, location FROM traffic LIMIT 3";
$stmt = $conn->query($query);
$trafficRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Sample Traffic Records:</p>";
echo "<table>";
echo "<tr><th>ID</th><th>Location</th><th>ID Type</th></tr>";
foreach ($trafficRecords as $record) {
    $idType = is_numeric($record['id']) ? "<span class='success'>✅ Numeric</span>" : "<span class='error'>❌ Not Numeric</span>";
    echo "<tr><td>{$record['id']}</td><td>{$record['location']}</td><td>$idType</td></tr>";
}
echo "</table>";

// Final Summary
echo "</div><div class='test-section'>";
echo "<h2>📊 Test Summary</h2>";

if ($allTestsPassed) {
    echo "<p class='success' style='font-size: 1.5em;'>🎉 ALL TESTS PASSED!</p>";
    echo "<p>Your system is working correctly.</p>";
} else {
    echo "<p class='error' style='font-size: 1.5em;'>⚠️ SOME TESTS FAILED</p>";
    echo "<p>Please fix the issues above.</p>";
}

// Action Buttons
echo "<hr>";
echo "<h3>🔧 Quick Actions:</h3>";
echo "<a href='create-demo-users.php' class='btn'>🔑 Fix Demo Users</a>";
echo "<a href='login.html' class='btn'>🚀 Go to Login</a>";
echo "<a href='index.html' class='btn'>🏠 Go to Dashboard</a>";
echo "<a href='test-system.php' class='btn' onclick='location.reload()'>🔄 Re-run Tests</a>";

// Recommended Actions
echo "<hr>";
echo "<h3>📋 Recommended Actions:</h3>";
echo "<ul>";

if (!$allTestsPassed) {
    echo "<li class='error'>Run <strong>create-demo-users.php</strong> to fix user passwords</li>";
}

echo "<li>Make sure XAMPP Apache and MySQL are running</li>";
echo "<li>Clear browser cache and try logging in</li>";
echo "<li>Check browser console (F12) for JavaScript errors</li>";
echo "</ul>";

// Database Info
echo "<hr>";
echo "<h3>📝 Database Connection Info:</h3>";
echo "<pre>";
echo "Host: $host\n";
echo "Database: $db_name\n";
echo "Username: $username\n";
echo "Password: " . ($password === '' ? '(empty)' : $password) . "\n";
echo "</pre>";

echo "</div>";

// Cleanup
echo "<p style='text-align: center; margin-top: 30px; color: #64748b;'>";
echo "⚠️ Remember to delete test-system.php after fixing issues for security!";
echo "</p>";

$conn = null;
?>