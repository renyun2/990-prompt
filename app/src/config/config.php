<?php
/**
 * 数据库配置文件
 */

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'member_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'member_pass123');
define('DB_NAME', getenv('DB_NAME') ?: 'member_system');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// 应用配置
define('APP_NAME', '会员注册管理系统');
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);
define('ADMIN_ROLE', 'admin');
define('USER_ROLE', 'user');

// 注：密码哈希使用 password_hash()（bcrypt 自动加盐），无需额外 SALT 常量
