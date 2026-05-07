<?php
/**
 * 测试登录功能
 * 从容器内运行: docker compose exec web php test_login.php
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/Database.php';
require_once __DIR__ . '/src/utils/Helper.php';

echo "=== 会员管理系统 - 登录功能测试 ===\n\n";

// 初始化session
session_start();

$db = Database::getInstance();

// 测试用户
$testUsers = [
    ['username' => 'admin', 'password' => 'admin123456', 'role' => 'admin'],
    ['username' => 'user1', 'password' => 'user123456', 'role' => 'user'],
];

foreach ($testUsers as $testUser) {
    echo "测试用户: {$testUser['username']} ({$testUser['role']})\n";
    
    // 从数据库获取用户
    $user = $db->fetch(
        "SELECT id, username, email, password, name, role, is_active FROM users WHERE username = ?",
        [$testUser['username']]
    );
    
    if (!$user) {
        echo "  ✗ 用户不存在\n\n";
        continue;
    }
    
    // 验证密码
    if (password_verify($testUser['password'], $user['password'])) {
        echo "  ✓ 密码验证成功\n";
        echo "  ✓ 用户ID: {$user['id']}\n";
        echo "  ✓ 邮箱: {$user['email']}\n";
        echo "  ✓ 名称: {$user['name']}\n";
        echo "  ✓ 角色: {$user['role']}\n";
        echo "  ✓ 状态: " . ($user['is_active'] ? '激活' : '禁用') . "\n";
    } else {
        echo "  ✗ 密码验证失败\n";
    }
    echo "\n";
}

echo "=== 测试完成 ===\n";
?>
