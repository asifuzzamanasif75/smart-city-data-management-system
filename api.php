<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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
            exit;
        }
        return $this->conn;
    }
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Check for POST actions (authentication)
if ($method === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
    
    // Handle authentication actions
    if (in_array($action, ['login', 'register', 'forgot-password', 'verify-reset-code', 'reset-password'])) {
        switch($action) {
            case 'login':
                handleLogin($db);
                exit;
            case 'register':
                handleRegister($db);
                exit;
            case 'forgot-password':
                handleForgotPassword($db);
                exit;
            case 'verify-reset-code':
                handleVerifyResetCode($db);
                exit;
            case 'reset-password':
                handleResetPassword($db);
                exit;
        }
    }
}

// Handle data CRUD operations
$action = isset($_GET['action']) ? $_GET['action'] : '';
$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

switch($method) {
    case 'GET':
        handleGet($db, $action, $table, $id);
        break;
    case 'POST':
        handlePost($db, $action, $table);
        break;
    case 'PUT':
        handlePut($db, $table, $id);
        break;
    case 'DELETE':
        handleDelete($db, $table, $id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

// ==========================================
// AUTHENTICATION FUNCTIONS
// ==========================================

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
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            
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
    
    // Check if username or email already exists
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
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new user
    $insertQuery = "INSERT INTO users (username, email, password, role, created_at) 
                    VALUES (:username, :email, :password, 'user', NOW())";
    
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

function handleForgotPassword($db) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is required'
        ]);
        return;
    }
    
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
    
    // Store reset code
    $updateQuery = "UPDATE users SET reset_code = :code, reset_code_expiry = :expiry WHERE id = :id";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':code', $resetCode);
    $stmt->bindParam(':expiry', $expiryTime);
    $stmt->bindParam(':id', $user['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Reset code sent to your email',
            'reset_code' => $resetCode,
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
    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
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

// ==========================================
// DATA CRUD FUNCTIONS
// ==========================================

function handleGet($db, $action, $table, $id) {
    if ($action === 'all') {
        getAllData($db);
    } elseif ($table && $id) {
        getSingleRecord($db, $table, $id);
    } elseif ($table) {
        getTableData($db, $table);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
}

function getAllData($db) {
    $tables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    $data = [];
    
    foreach ($tables as $table) {
        try {
            $query = "SELECT * FROM " . $table;
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $data[$table] = [];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

function getTableData($db, $table) {
    $allowedTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        return;
    }
    
    try {
        $query = "SELECT * FROM " . $table;
        $stmt = $db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

function getSingleRecord($db, $table, $id) {
    $allowedTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        return;
    }
    
    $query = "SELECT * FROM " . $table . " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function handlePost($db, $action, $table) {
    $allowedTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    unset($data['id']);
    
    $columns = array_keys($data);
    $values = array_values($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    $query = "INSERT INTO " . $table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true,
            'message' => 'Record added successfully',
            'id' => $db->lastInsertId()
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function handlePut($db, $table, $id) {
    $allowedTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        return;
    }
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    unset($data['id']);
    
    $setParts = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
    
    $values[] = $id;
    
    $query = "UPDATE " . $table . " SET " . implode(', ', $setParts) . " WHERE id = ?";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true,
            'message' => 'Record updated successfully'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function handleDelete($db, $table, $id) {
    $allowedTables = ['traffic', 'parking', 'waste', 'energy', 'pollution', 'emergency', 'authorities', 'administrators', 'citizens'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        return;
    }
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    $query = "DELETE FROM " . $table . " WHERE id = :id";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Record deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Record not found'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?>