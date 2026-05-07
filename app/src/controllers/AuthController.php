<?php
/**
 * AuthController - 负责处理登录、注册、退出登录的业务逻辑
 */
class AuthController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 处理登录 POST 请求
     */
    public function handleLogin(): void
    {
        $this->verifyCsrf('/login', 'login_error');

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = '用户名和密码不能为空';
            Helper::redirect('/login');
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE username = ?", [$username]);

        if (!$user) {
            $_SESSION['login_error'] = '用户名不存在';
            Helper::redirect('/login');
        }

        if (!$user['is_active']) {
            $_SESSION['login_error'] = '账户已被禁用，请联系管理员';
            Helper::redirect('/login');
        }

        // 检查账户是否被锁定
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            $_SESSION['login_error'] = "账户已被临时锁定，请 {$remaining} 分钟后再试";
            Helper::redirect('/login');
        }

        if (Helper::verifyPassword($password, $user['password'])) {
            // 登录成功：重置失败计数
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['email']       = $user['email'];
            $_SESSION['user_name']   = $user['name'];
            $_SESSION['user_role']   = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];

            $ip        = Helper::getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $this->db->query(
                "INSERT INTO login_logs (user_id, ip_address, user_agent) VALUES (?, ?, ?)",
                [$user['id'], $ip, $userAgent]
            );
            $this->db->query(
                "UPDATE users SET last_login = NOW(), failed_attempts = 0, locked_until = NULL WHERE id = ?",
                [$user['id']]
            );

            if ($remember) {
                setcookie('username', $username, time() + 30 * 24 * 60 * 60, '/');
            }

            Helper::redirect('/');
        }

        // 登录失败：累加失败次数，达到阈值后锁定
        $maxAttempts  = 5;
        $lockMinutes  = 15;
        $newAttempts  = (int)($user['failed_attempts'] ?? 0) + 1;

        if ($newAttempts >= $maxAttempts) {
            $lockUntil = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
            $this->db->query(
                "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?",
                [$newAttempts, $lockUntil, $user['id']]
            );
            $_SESSION['login_error'] = "密码连续错误 {$maxAttempts} 次，账户已被锁定 {$lockMinutes} 分钟";
        } else {
            $remaining = $maxAttempts - $newAttempts;
            $this->db->query(
                "UPDATE users SET failed_attempts = ? WHERE id = ?",
                [$newAttempts, $user['id']]
            );
            $_SESSION['login_error'] = "密码错误，还可尝试 {$remaining} 次";
        }

        Helper::redirect('/login');
    }

    /**
     * 处理注册 POST 请求
     */
    public function handleRegister(): void
    {
        $this->verifyCsrf('/register', 'register_error');

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        $error = $this->validateRegisterInput($username, $email, $password, $confirm, $name, $phone);
        if ($error) {
            $_SESSION['register_error'] = $error;
            Helper::redirect('/register');
        }

        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            $_SESSION['register_error'] = '用户名已被注册';
            Helper::redirect('/register');
        }

        if (!empty($email)) {
            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $_SESSION['register_error'] = '邮箱已被注册';
                Helper::redirect('/register');
            }
        }

        $hashedPassword = Helper::hashPassword($password);
        $this->db->query(
            "INSERT INTO users (username, email, password, name, phone) VALUES (?, ?, ?, ?, ?)",
            [$username, $email !== '' ? $email : null, $hashedPassword, $name, $phone !== '' ? $phone : null]
        );

        $_SESSION['register_success'] = true;
        Helper::redirect('/login');
    }

    // ──────────────────────────────
    // 私有辅助方法
    // ──────────────────────────────

    private function validateRegisterInput(
        string $username, string $email, string $password,
        string $confirm, string $name, string $phone
    ): string {
        if (empty($username)) {
            return '用户名不能为空';
        }
        if (!Helper::validateUsername($username)) {
            return '用户名格式不正确（3-20字符，字母/数字/下划线）';
        }
        if (!empty($email) && !Helper::validateEmail($email)) {
            return '邮箱格式不正确';
        }
        if (empty($password)) {
            return '密码不能为空';
        }
        if (!Helper::validatePassword($password)) {
            return '密码至少需要8个字符';
        }
        if ($password !== $confirm) {
            return '两次输入的密码不一致';
        }
        if (empty($name)) {
            return '真实姓名不能为空';
        }
        if (!empty($phone) && !Helper::validatePhone($phone)) {
            return '手机号格式不正确';
        }
        return '';
    }

    private function verifyCsrf(string $redirectPath, string $sessionKey): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Helper::validateCSRFToken($token)) {
            $_SESSION[$sessionKey] = '非法请求（CSRF 校验失败），请刷新页面后重试';
            Helper::redirect($redirectPath);
        }
    }
}
