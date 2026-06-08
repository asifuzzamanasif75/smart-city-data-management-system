<?php
/**
 * Email Configuration and Helper Functions
 * For sending password reset codes
 */

// Email Configuration
define('EMAIL_FROM', 'noreply@smartcity.gov');
define('EMAIL_FROM_NAME', 'Smart City Data Management System');
define('EMAIL_ENABLED', false); // Set to true when email is configured

/**
 * Send Email Function
 * Uses PHP's mail() function (requires mail server configuration)
 * 
 * For Gmail SMTP, you'll need PHPMailer library:
 * composer require phpmailer/phpmailer
 */
function sendEmail($to, $subject, $message, $isHTML = true) {
    if (!EMAIL_ENABLED) {
        // Email disabled - log to console instead
        error_log("EMAIL (disabled): To: $to, Subject: $subject, Message: $message");
        return true; // Return true for development
    }
    
    $headers = array();
    $headers[] = 'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM . '>';
    $headers[] = 'Reply-To: ' . EMAIL_FROM;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    if ($isHTML) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
    }
    
    $success = mail($to, $subject, $message, implode("\r\n", $headers));
    
    if ($success) {
        error_log("EMAIL sent successfully to: $to");
    } else {
        error_log("EMAIL failed to send to: $to");
    }
    
    return $success;
}

/**
 * Send Password Reset Code Email
 */
function sendPasswordResetEmail($email, $username, $resetCode) {
    $subject = 'Password Reset Code - Smart City Data Management System';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; }
            .code-box { background: white; border: 3px solid #667eea; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
            .code { font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 10px; font-family: 'Courier New', monospace; }
            .footer { background: #e9ecef; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p>We received a request to reset your password for your Smart City Data Management System account.</p>
                
                <div class='code-box'>
                    <p style='margin: 0; font-size: 14px; color: #666;'>Your Password Reset Code:</p>
                    <div class='code'>" . $resetCode . "</div>
                </div>
                
                <p><strong>This code will expire in 15 minutes.</strong></p>
                
                <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <p>For security reasons, never share this code with anyone.</p>
            </div>
            <div class='footer'>
                <p>Smart City Data Management System - Automated Email</p>
                <p>Do not reply to this email</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message, true);
}

/**
 * Send Welcome Email (After Registration)
 */
function sendWelcomeEmail($email, $username) {
    $subject = 'Welcome to Smart City Data Management System!';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; }
            .footer { background: #e9ecef; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Welcome to Smart City Data Management System!</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p>Thank you for registering with Smart City Data Management System. Your account has been created successfully!</p>
                
                <p><strong>Your Account Details:</strong></p>
                <ul>
                    <li>Username: " . htmlspecialchars($username) . "</li>
                    <li>Email: " . htmlspecialchars($email) . "</li>
                </ul>
                
                <p>You can now login to access the dashboard and manage city services.</p>
                
                <p style='text-align: center;'>
                    <a href='http://localhost:3000/login.html' class='button'>Login Now</a>
                </p>
            </div>
            <div class='footer'>
                <p>Smart City Data Management System - Automated Email</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message, true);
}

// Test email configuration
function testEmailConfiguration() {
    echo "<h3>Email Configuration Test</h3>";
    echo "<p>Email Enabled: " . (EMAIL_ENABLED ? "Yes" : "No (Dev Mode)") . "</p>";
    echo "<p>From: " . EMAIL_FROM_NAME . " &lt;" . EMAIL_FROM . "&gt;</p>";
    
    if (!EMAIL_ENABLED) {
        echo "<p style='color: #f59e0b;'>⚠️ Email is disabled. Codes will be displayed on screen instead.</p>";
        echo "<p>To enable email sending:</p>";
        echo "<ol>";
        echo "<li>Set EMAIL_ENABLED to true</li>";
        echo "<li>Configure your mail server (SMTP)</li>";
        echo "<li>Or install PHPMailer for Gmail SMTP</li>";
        echo "</ol>";
    }
}
?>