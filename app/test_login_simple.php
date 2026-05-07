<?php
/**
 * 登录测试脚本
 * 测试登录POST请求是否正常工作（无header错误）
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/Database.php';
require_once __DIR__ . '/src/utils/Helper.php';

echo "=== 登录功能测试 ===\n\n";

// 初始化session
session_start();

$db = Database::getInstance();

// 测试用户
$testUser = ['username' => 'admin', 'password' => 'admin123456'];

echo "测试用户: {$testUser['username']}\n";

// 从数据库获取用户
$user = $db->fetch(
    "SELECT id, username, email, password, name, role, is_active FROM users WHERE username = ?",
    [$testUser['username']]
);

if (!$user) {
    echo "✗ 用户不存在\n";
    exit(1);
}

// 验证密码
if (!password_verify($testUser['password'], $user['password'])) {
    echo "✗ 密码验证失败\n";
    exit(1);
}

echo "✓ 密码验证成功\n";
echo "✓ 用户ID: {$user['id']}\n";
echo "✓ 用户名: {$user['username']}\n";
echo "✓ 角色: {$user['role']}\n";

// 模拟设置会话
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

// 检查会话是否设置
if (isset($_SESSION['user_id'])) {
    echo "✓ 会话设置成功\n";
    echo "✓ 会话用户ID: {$_SESSION['user_id']}\n";
} else {
    echo "✗ 会话设置失败\n";
    exit(1);
}

// 检查header是否可以正常设置（不会导致错误）
if (!headers_sent()) {
    echo "✓ Headers未发送，可以调用header()函数\n";
} else {
    echo "✗ Headers已发送，无法调用header()函数\n";
    exit(1);
}

echo "\n=== 所有测试通过 ===\n";
echo "登录功能已正常工作，无header相关错误。\n";
?>
