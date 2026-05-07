<?php
/**
 * 数据库初始化脚本
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

class DBInitializer {
    private $database;
    private $isCliMode;

    public function __construct() {
        $this->database = Database::getInstance();
        $this->isCliMode = php_sapi_name() === 'cli';
    }

    private function log($message) {
        if ($this->isCliMode) {
            echo $message . PHP_EOL;
        }
    }

    public function init() {
        $this->log("[数据库初始化开始]");
        
        try {
            // 等待数据库连接
            $this->waitForDatabase();
            
            // 创建表
            $this->createTables();
            
            // 初始化数据
            $this->seedData();
            
            $this->log("[数据库初始化完成]");
        } catch (Exception $e) {
            $this->log("[错误]: " . $e->getMessage());
            if ($this->isCliMode) {
                exit(1);
            } else {
                throw $e;
            }
        }
    }

    private function waitForDatabase() {
        $maxAttempts = 30;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                $this->database->query("SELECT 1");
                $this->log("[数据库连接成功]");
                return;
            } catch (Exception $e) {
                $attempt++;
                $this->log("[等待数据库...] 尝试 $attempt/$maxAttempts");
                sleep(1);
            }
        }
        
        throw new Exception("无法连接到数据库");
    }

    private function createTables() {
        $this->log("[创建数据表...]");

        // 创建用户表
        $this->database->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100),
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                avatar VARCHAR(255) DEFAULT 'https://api.dicebear.com/7.x/avataaars/svg?seed=default',
                role ENUM('user', 'admin') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                failed_attempts INT DEFAULT 0,
                locked_until DATETIME NULL,
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->log("  ✓ users 表创建成功");

        // 创建登录日志表
        $this->database->query("
            CREATE TABLE IF NOT EXISTS login_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->log("  ✓ login_logs 表创建成功");

        // 创建系统公告表
        $this->database->query("
            CREATE TABLE IF NOT EXISTS announcements (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(200) NOT NULL,
                content TEXT,
                type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
                is_active TINYINT(1) DEFAULT 1,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->log("  ✓ announcements 表创建成功");
    }

    private function seedData() {
        $this->log("[初始化示例数据...]");

        // 检查是否已存在数据
        $result = $this->database->fetch("SELECT COUNT(*) as count FROM users");
        
        if ($result['count'] > 0) {
            $this->log("  数据库已包含数据，跳过初始化");
            return;
        }

        // 创建管理员账户
        $adminPassword = password_hash('admin123456', PASSWORD_BCRYPT);
        $this->database->query(
            "INSERT INTO users (username, email, password, name, phone, role, is_active) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            ['admin', 'admin@example.com', $adminPassword, '系统管理员', '13800000000', 'admin', 1]
        );
        $this->log("  ✓ 管理员账户创建成功 (admin/admin123456)");

        // 创建测试用户
        $testPassword = password_hash('user123456', PASSWORD_BCRYPT);
        for ($i = 1; $i <= 5; $i++) {
            $this->database->query(
                "INSERT INTO users (username, email, password, name, phone, role, is_active) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    "user$i",
                    "user$i@example.com",
                    $testPassword,
                    "测试用户$i",
                    "1380000000" . sprintf('%d', $i),
                    'user',
                    1
                ]
            );
        }
        $this->log("  ✓ 测试用户创建成功 (user1-5/user123456)");
    }
}

// 仅在CLI环境执行初始化
if (php_sapi_name() === 'cli') {
    $initializer = new DBInitializer();
    $initializer->init();
    touch(__DIR__ . '/.db_initialized');
}
