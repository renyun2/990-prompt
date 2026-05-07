<?php
/**
 * 工具类 - 验证、加密等
 */

class Helper {
    /**
     * 验证邮箱格式
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证用户名格式 (3-20字符，只能包含字母、数字、下划线)
     */
    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) === 1;
    }

    /**
     * 验证密码强度 (至少8个字符)
     */
    public static function validatePassword($password) {
        return strlen($password) >= 8;
    }

    /**
     * 验证手机号
     */
    public static function validatePhone($phone) {
        return preg_match('/^1[3-9]\d{9}$/', $phone) === 1 || $phone === '';
    }

    /**
     * 对密码进行哈希加密
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 验证密码
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * 获取客户端IP地址
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
    }

    /**
     * 生成随机字符串
     */
    public static function generateRandomString($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * 转义HTML防止XSS攻击
     */
    public static function escape($string) {
        // 处理NULL值
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 记录日志
     */
    public static function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        error_log($logMessage);
    }

    /**
     * 发送JSON响应
     */
    public static function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * 重定向
     */
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * 生成或获取当前会话的 CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * 验证提交的 CSRF token 是否合法
     */
    public static function validateCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * 输出 CSRF 隐藏字段 HTML
     */
    public static function csrfField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * 获取文件扩展名
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * 验证文件上传
     */
    public static function validateUploadedFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'message' => '没有上传文件'];
        }

        $ext = self::getFileExtension($file['name']);
        if (!in_array($ext, $allowedTypes)) {
            return ['success' => false, 'message' => '不允许的文件类型'];
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            return ['success' => false, 'message' => '文件过大'];
        }

        return ['success' => true];
    }

    /**
     * 保存上传的文件
     */
    public static function saveUploadedFile($file, $targetDir = 'uploads/avatars/') {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = self::getFileExtension($file['name']);
        $filename = date('YmdHis') . '_' . md5($file['name'] . time()) . '.' . $ext;
        $targetPath = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/' . $targetPath;
        }

        return null;
    }
}
