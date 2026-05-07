<?php
/**
 * ProfileController - 负责处理个人资料修改、密码变更的业务逻辑
 */
class ProfileController
{
    private Database $db;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->db = Database::getInstance();
    }

    /**
     * 处理个人信息更新
     */
    public function handleUpdateInfo(): void
    {
        $this->verifyCsrf('/profile', 'update_error');

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $error = $this->validateInfoInput($name, $email, $phone);
        if ($error) {
            $_SESSION['update_error'] = $error;
            Helper::redirect('/profile');
        }

        if (!empty($email)) {
            $existing = $this->db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$email, $_SESSION['user_id']]
            );
            if ($existing) {
                $_SESSION['update_error'] = '邮箱已被其他用户使用';
                Helper::redirect('/profile');
            }
        }

        $this->db->query(
            "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?",
            [$name, $email !== '' ? $email : null, $phone !== '' ? $phone : null, $_SESSION['user_id']]
        );
        $_SESSION['user_name']      = $name;
        $_SESSION['email']          = $email;
        $_SESSION['update_success'] = '个人信息已更新';
        Helper::redirect('/profile');
    }

    /**
     * 处理密码修改
     */
    public function handleChangePassword(): void
    {
        $this->verifyCsrf('/profile', 'password_error');

        $oldPassword     = $_POST['old_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            $_SESSION['password_error'] = '请填写完整的密码信息';
            Helper::redirect('/profile');
        }

        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);

        if (!Helper::verifyPassword($oldPassword, $user['password'])) {
            $_SESSION['password_error'] = '原密码错误';
            Helper::redirect('/profile');
        }

        if (!Helper::validatePassword($newPassword)) {
            $_SESSION['password_error'] = '新密码至少需要8个字符';
            Helper::redirect('/profile');
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_error'] = '两次输入的密码不一致';
            Helper::redirect('/profile');
        }

        $hashedPassword = Helper::hashPassword($newPassword);
        $this->db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $_SESSION['user_id']]);
        $_SESSION['password_success'] = '密码已更新';
        Helper::redirect('/profile');
    }

    /**
     * 处理头像上传
     */
    public function handleUpdateAvatar(): void
    {
        $this->verifyCsrf('/profile', 'update_error');

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['update_error'] = '请选择要上传的图片';
            Helper::redirect('/profile');
        }

        $file = $_FILES['avatar'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['update_error'] = '文件上传失败（错误码：' . $file['error'] . '）';
            Helper::redirect('/profile');
        }

        // 校验文件类型和大小
        $validation = Helper::validateUploadedFile($file, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        if (!$validation['success']) {
            $_SESSION['update_error'] = $validation['message'];
            Helper::redirect('/profile');
        }

        // 验证是否为真实图片（防止伪装扩展名）
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $_SESSION['update_error'] = '请上传有效的图片文件';
            Helper::redirect('/profile');
        }

        // 构建保存路径
        $targetDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $targetDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['update_error'] = '头像保存失败，请重试';
            Helper::redirect('/profile');
        }

        $avatarUrl = '/uploads/avatars/' . $filename;

        // 删除旧头像（仅本地上传文件）
        $oldAvatar = $this->db->fetchColumn(
            "SELECT avatar FROM users WHERE id = ?", [$_SESSION['user_id']]
        );
        if ($oldAvatar && str_starts_with((string)$oldAvatar, '/uploads/')) {
            $oldPath = __DIR__ . '/../../public' . $oldAvatar;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // 更新数据库和 Session
        $this->db->query(
            "UPDATE users SET avatar = ? WHERE id = ?",
            [$avatarUrl, $_SESSION['user_id']]
        );
        $_SESSION['user_avatar']    = $avatarUrl;
        $_SESSION['update_success'] = '头像更新成功';
        Helper::redirect('/profile');
    }

    // ──────────────────────────────
    // 私有辅助方法
    // ──────────────────────────────

    private function validateInfoInput(string $name, string $email, string $phone): string
    {
        if (empty($name)) {
            return '姓名不能为空';
        }
        if (!empty($email) && !Helper::validateEmail($email)) {
            return '邮箱格式不正确';
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
