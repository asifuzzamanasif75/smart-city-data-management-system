<?php
/**
 * Database Configuration File
 * Update these settings based on your XAMPP/WAMP/LAMP setup
 */

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'smart_city_data_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// API Configuration
define('API_VERSION', '1.0');
define('API_BASE_PATH', '');

// Application Settings
define('APP_NAME', 'Smart City Data Management System');
define('APP_VERSION', '1.0.0');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security Settings
define('PASSWORD_MIN_LENGTH', 5);
define('USERNAME_MIN_LENGTH', 3);
define('HASH_ALGORITHM', PASSWORD_DEFAULT);

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Dhaka');

// CORS Settings
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Database Connection Class
 */
class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            if (DEBUG_MODE) {
                error_log("✅ Database connected successfully");
            }
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log("❌ Database connection failed: " . $e->getMessage());
            }
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Response Helper Functions
 */
function sendJsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

function sendError($message, $httpCode = 400) {
    sendJsonResponse(false, $message, null, $httpCode);
}

function sendSuccess($message, $data = null, $httpCode = 200) {
    sendJsonResponse(true, $message, $data, $httpCode);
}

/**
 * Input Validation Functions
 */
function validateRequired($value, $fieldName) {
    if (empty($value) && $value !== '0') {
        throw new Exception("$fieldName is required");
    }
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
}

function validateMinLength($value, $minLength, $fieldName) {
    if (strlen($value) < $minLength) {
        throw new Exception("$fieldName must be at least $minLength characters long");
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Allowed Tables for API Access
 */
function getAllowedTables() {
    return [
        'traffic',
        'parking',
        'waste',
        'energy',
        'pollution',
        'emergency',
        'authorities',
        'administrators',
        'citizens'
    ];
}

function validateTableName($table) {
    if (!in_array($table, getAllowedTables())) {
        throw new Exception("Invalid table name");
    }
}

/**
 * Logging Function
 */
function logActivity($action, $details = '') {
    if (DEBUG_MODE) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $action";
        if ($details) {
            $logMessage .= " - $details";
        }
        error_log($logMessage);
    }
}

// Log that config was loaded
if (DEBUG_MODE) {
    error_log("✅ Configuration loaded - " . APP_NAME . " v" . APP_VERSION);
}
?>