<?php
/**
 * AdminController - 负责处理所有后台管理的业务逻辑
 */
class AdminController
{
    private Database $db;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->db = Database::getInstance();
    }

    /**
     * 获取登录日志（AJAX JSON 响应）
     */
    public function handleGetLogs(): void
    {
        header('Content-Type: application/json');
        $logs = $this->db->fetchAll(
            "SELECT l.*, u.username
             FROM login_logs l
             LEFT JOIN users u ON l.user_id = u.id
             ORDER BY l.login_time DESC
             LIMIT 200"
        );
        echo json_encode(['logs' => $logs]);
        exit;
    }

    /**
     * 处理新增用户
     */
    public function handleAddUser(): void
    {
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $error = $this->validateUserInput($username, $email, $name, $phone, $password);
        if ($error) {
            $_SESSION['admin_error'] = $error;
            Helper::redirect('/admin');
        }

        if ($this->db->fetch("SELECT id FROM users WHERE username = ?", [$username])) {
            $_SESSION['admin_error'] = '用户名已被注册';
            Helper::redirect('/admin');
        }

        if (!empty($email) && $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email])) {
            $_SESSION['admin_error'] = '邮箱已被注册';
            Helper::redirect('/admin');
        }

        $hashedPassword = Helper::hashPassword($password);
        $this->db->query(
            "INSERT INTO users (username, email, password, name, phone, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$username, $email !== '' ? $email : null, $hashedPassword, $name, $phone !== '' ? $phone : null, $role, $isActive]
        );

        $_SESSION['admin_success'] = '用户创建成功';
        Helper::redirect('/admin');
    }

    /**
     * 处理编辑用户信息
     */
    public function handleUpdateUser(): void
    {
        $this->verifyCsrf();

        $userId   = intval($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['admin_error'] = '不能通过此功能修改自己的账户信息';
            Helper::redirect('/admin');
        }

        if (!$this->db->fetch("SELECT id FROM users WHERE id = ?", [$userId])) {
            $_SESSION['admin_error'] = '目标用户不存在';
            Helper::redirect('/admin');
        }

        $error = $this->validateUserInput($username, $email, $name, $phone);
        if ($error) {
            $_SESSION['admin_error'] = $error;
            Helper::redirect('/admin');
        }

        if ($this->db->fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $userId])) {
            $_SESSION['admin_error'] = '该用户名已被其他账户使用';
            Helper::redirect('/admin');
        }

        if (!empty($email) && $this->db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId])) {
            $_SESSION['admin_error'] = '该邮箱已被其他账户使用';
            Helper::redirect('/admin');
        }

        $this->db->query(
            "UPDATE users SET username = ?, email = ?, name = ?, phone = ?, role = ?, is_active = ? WHERE id = ?",
            [$username, $email !== '' ? $email : null, $name, $phone !== '' ? $phone : null, $role, $isActive, $userId]
        );

        $_SESSION['admin_success'] = '用户信息已更新';
        Helper::redirect('/admin');
    }

    /**
     * 处理重置用户密码
     */
    public function handleResetPassword(): void
    {
        $this->verifyCsrf();

        $userId          = intval($_POST['user_id'] ?? 0);
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['admin_error'] = '请通过个人资料页面修改自己的密码';
            Helper::redirect('/admin');
        }

        if (!Helper::validatePassword($newPassword)) {
            $_SESSION['admin_error'] = '新密码至少需要8个字符';
            Helper::redirect('/admin');
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['admin_error'] = '两次输入的密码不一致';
            Helper::redirect('/admin');
        }

        $hashedPassword = Helper::hashPassword($newPassword);
        $this->db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
        $_SESSION['admin_success'] = '密码重置成功';
        Helper::redirect('/admin');
    }

    /**
     * 处理删除用户（POST + CSRF）
     */
    public function handleDelete(int $userId): void
    {
        $this->verifyCsrf();
        if ($userId === (int)$_SESSION['user_id']) {
            Helper::redirect('/admin');
        }

        $this->db->query("DELETE FROM login_logs WHERE user_id = ?", [$userId]);
        $this->db->query("DELETE FROM users WHERE id = ?", [$userId]);
        $_SESSION['admin_success'] = '用户删除成功';
        Helper::redirect('/admin');
    }

    /**
     * 处理启用/禁用用户（POST + CSRF）
     */
    public function handleToggle(int $userId): void
    {
        $this->verifyCsrf();
        if ($userId === (int)$_SESSION['user_id']) {
            Helper::redirect('/admin');
        }

        $user      = $this->db->fetch("SELECT is_active FROM users WHERE id = ?", [$userId]);
        $newStatus = $user['is_active'] ? 0 : 1;
        $this->db->query("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $userId]);
        $_SESSION['admin_success'] = $newStatus ? '用户已启用' : '用户已禁用';
        Helper::redirect('/admin');
    }

    /**
     * 处理修改角色（POST + CSRF）
     */
    public function handleRole(int $userId, string $role): void
    {
        $this->verifyCsrf();
        if ($userId === (int)$_SESSION['user_id']) {
            Helper::redirect('/admin');
        }

        $newRole = in_array($role, ['admin', 'user']) ? $role : 'user';
        $this->db->query("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
        Helper::redirect('/admin');
    }

    // ──────────────────────────────
    // 私有辅助方法
    // ──────────────────────────────

    /**
     * 通用用户字段校验；$password 为空时跳过密码校验（编辑场景）
     */
    private function validateUserInput(
        string $username, string $email,
        string $name, string $phone, string $password = ''
    ): string {
        if (!Helper::validateUsername($username)) {
            return '用户名格式不正确（3-20字符，字母/数字/下划线）';
        }
        if (!empty($email) && !Helper::validateEmail($email)) {
            return '邮箱格式不正确';
        }
        if (empty($name)) {
            return '真实姓名不能为空';
        }
        if (!empty($phone) && !Helper::validatePhone($phone)) {
            return '手机号格式不正确';
        }
        if ($password !== '' && !Helper::validatePassword($password)) {
            return '密码至少需要8个字符';
        }
        return '';
    }

    /**
     * 批量操作用户（启用/禁用/删除）
     */
    public function handleBatch(): void
    {
        $this->verifyCsrf();

        $action = $_POST['batch_action'] ?? '';
        $ids    = $_POST['user_ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            $_SESSION['admin_error'] = '请先勾选要操作的用户';
            Helper::redirect('/admin');
        }

        $currentUserId = (int)$_SESSION['user_id'];
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id !== $currentUserId));

        if (empty($ids)) {
            $_SESSION['admin_error'] = '所选用户中包含当前账户，批量操作不影响自身';
            Helper::redirect('/admin');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'enable':
                $this->db->query("UPDATE users SET is_active = 1 WHERE id IN ($placeholders)", $ids);
                $_SESSION['admin_success'] = '批量启用成功（共 ' . count($ids) . ' 个用户）';
                break;

            case 'disable':
                $this->db->query("UPDATE users SET is_active = 0 WHERE id IN ($placeholders)", $ids);
                $_SESSION['admin_success'] = '批量禁用成功（共 ' . count($ids) . ' 个用户）';
                break;

            case 'delete':
                $this->db->query("DELETE FROM login_logs WHERE user_id IN ($placeholders)", $ids);
                $this->db->query("DELETE FROM users WHERE id IN ($placeholders)", $ids);
                $_SESSION['admin_success'] = '批量删除成功（共 ' . count($ids) . ' 个用户）';
                break;

            default:
                $_SESSION['admin_error'] = '未知批量操作';
        }

        Helper::redirect('/admin');
    }

    /**
     * 导出用户数据为 CSV
     */
    public function handleExport(): void
    {
        $search      = $_GET['search'] ?? '';
        $whereClause = '';
        $params      = [];

        if ($search) {
            $whereClause = "WHERE username LIKE ? OR email LIKE ? OR name LIKE ?";
            $params      = ["%$search%", "%$search%", "%$search%"];
        }

        $users = $this->db->fetchAll(
            "SELECT id, username, email, name, phone, role, is_active, last_login, created_at
             FROM users $whereClause ORDER BY created_at DESC",
            $params
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="users_' . date('Ymd_His') . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM 保证 Excel 正确识别编码
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', '用户名', '邮箱', '姓名', '手机号', '角色', '状态', '最后登录', '注册时间']);

        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['username'],
                $user['email'] ?? '',
                $user['name'],
                $user['phone'] ?? '',
                $user['role'] === 'admin' ? '管理员' : '普通用户',
                $user['is_active'] ? '活跃' : '禁用',
                $user['last_login'] ?? '',
                $user['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * 解锁被锁定的用户（POST + CSRF）
     */
    public function handleUnlock(int $userId): void
    {
        $this->verifyCsrf();
        if ($userId === (int)$_SESSION['user_id']) {
            Helper::redirect('/admin');
        }
        $this->db->query(
            "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?",
            [$userId]
        );
        $_SESSION['admin_success'] = '账户已解锁';
        Helper::redirect('/admin');
    }

    // ──────────────────────────────
    // 公告管理
    // ──────────────────────────────

    public function handleAddAnnouncement(): void
    {
        $this->verifyCsrf();

        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $type    = in_array($_POST['type'] ?? '', ['info', 'success', 'warning', 'danger'])
            ? $_POST['type'] : 'info';

        if (empty($title)) {
            $_SESSION['admin_error'] = '公告标题不能为空';
            Helper::redirect('/admin');
        }

        $this->db->query(
            "INSERT INTO announcements (title, content, type, created_by) VALUES (?, ?, ?, ?)",
            [$title, $content, $type, (int)$_SESSION['user_id']]
        );
        $_SESSION['admin_success'] = '公告发布成功';
        Helper::redirect('/admin');
    }

    public function handleDeleteAnnouncement(int $id): void
    {
        $this->db->query("DELETE FROM announcements WHERE id = ?", [$id]);
        $_SESSION['admin_success'] = '公告已删除';
        Helper::redirect('/admin');
    }

    public function handleToggleAnnouncement(int $id): void
    {
        $ann = $this->db->fetch("SELECT is_active FROM announcements WHERE id = ?", [$id]);
        if ($ann) {
            $newStatus = $ann['is_active'] ? 0 : 1;
            $this->db->query("UPDATE announcements SET is_active = ? WHERE id = ?", [$newStatus, $id]);
            $_SESSION['admin_success'] = $newStatus ? '公告已发布' : '公告已隐藏';
        }
        Helper::redirect('/admin');
    }

    private function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Helper::validateCSRFToken($token)) {
            $_SESSION['admin_error'] = '非法请求（CSRF 校验失败），请刷新页面后重试';
            Helper::redirect('/admin');
        }
    }
}
