<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
class Database {
    private $host = "127.0.0.1";
    private $db_name = "smart_city_data_management_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage()
            ]);
        }
        return $this->conn;
    }
}

$database = new Database();
$db = $database->getConnection();

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'login') {
    handleLogin($db);
} elseif ($action === 'register') {
    handleRegister($db);
} elseif ($action === 'forgot-password') {
    handleForgotPassword($db);
} elseif ($action === 'verify-reset-code') {
    handleVerifyResetCode($db);
} elseif ($action === 'reset-password') {
    handleResetPassword($db);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}

function handleLogin($db) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        return;
    }
    
    $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Try password verification
        if (password_verify($password, $user['password'])) {
            // Password is correct
            unset($user['password']); // Don't send password back
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            // Log for debugging
            error_log("Password verification failed for user: $username");
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
}

// Password Reset Functions
function handleForgotPassword($db) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is required'
        ]);
        return;
    }
    
    // Check if email exists
    $query = "SELECT id, username, email FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Email not found'
        ]);
        return;
    }
    
    // Generate 6-digit reset code
    $resetCode = sprintf("%06d", mt_rand(1, 999999));
    $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Store reset code in database
    $updateQuery = "UPDATE users SET reset_code = :code, reset_code_expiry = :expiry WHERE id = :id";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':code', $resetCode);
    $stmt->bindParam(':expiry', $expiryTime);
    $stmt->bindParam(':id', $user['id']);
    
    if ($stmt->execute()) {
        // In production, send email here
        // For now, return code in response (ONLY FOR DEVELOPMENT)
        echo json_encode([
            'success' => true,
            'message' => 'Reset code sent to your email',
            'reset_code' => $resetCode, // Remove in production!
            'email' => $email,
            'expires_in' => '15 minutes'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate reset code'
        ]);
    }
}

function handleVerifyResetCode($db) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    
    if (empty($email) || empty($code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and code are required'
        ]);
        return;
    }
    
    $query = "SELECT id, username, reset_code, reset_code_expiry 
              FROM users 
              WHERE email = :email 
              AND reset_code = :code 
              AND reset_code_expiry > NOW()
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'message' => 'Code verified successfully',
            'username' => $user['username']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset code'
        ]);
    }
}

function handleResetPassword($db) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    
    if (empty($email) || empty($code) || empty($newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    if (strlen($newPassword) < 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 5 characters long'
        ]);
        return;
    }
    
    // Verify code again
    $query = "SELECT id FROM users 
              WHERE email = :email 
              AND reset_code = :code 
              AND reset_code_expiry > NOW()
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset code'
        ]);
        return;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // Update password and clear reset code
    $updateQuery = "UPDATE users 
                    SET password = :password, 
                        reset_code = NULL, 
                        reset_code_expiry = NULL 
                    WHERE id = :id";
    
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':id', $user['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully! You can now login with your new password.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset password'
        ]);
    }
}

function handleRegister($db) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    if (strlen($username) < 3) {
        echo json_encode([
            'success' => false,
            'message' => 'Username must be at least 3 characters long'
        ]);
        return;
    }
    
    if (strlen($password) < 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 5 characters long'
        ]);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        return;
    }
    
    // Check if username already exists
    $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username or email already exists'
        ]);
        return;
    }
    
    // Hash password using bcrypt (same as database demo users)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new user (always as regular user)
    $insertQuery = "INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, 'user', NOW())";
    
    try {
        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now login.',
            'user' => [
                'id' => $db->lastInsertId(),
                'username' => $username,
                'email' => $email,
                'role' => 'user'
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
    }
}
?>