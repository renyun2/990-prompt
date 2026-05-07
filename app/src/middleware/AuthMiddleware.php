<?php
/**
 * 会话认证中间件
 */

class AuthMiddleware {
    /**
     * 检查用户是否登录
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            Helper::redirect('/login');
        }
    }

    /**
     * 检查用户是否为管理员
     */
    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['user_role'] !== ADMIN_ROLE) {
            Helper::redirect('/');
        }
    }

    /**
     * 检查是否已登录
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * 获取当前登录用户信息
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role'],
            'avatar' => $_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'
        ];
    }

    /**
     * 登出
     */
    public static function logout() {
        session_destroy();
        Helper::redirect('/login');
    }
}
