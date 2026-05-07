<?php
/**
 * 主入口文件 - index.php
 * 职责：启动会话、加载依赖、解析 URI 路由并分发到对应 Controller
 * 所有页面 URL 均为干净路径，不使用 ?page= 查询字符串方式
 */

session_start();

// ── 依赖加载 ──────────────────────────────────────────
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/config/DBInitializer.php';
require_once __DIR__ . '/../src/utils/Helper.php';
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/ProfileController.php';
require_once __DIR__ . '/../src/controllers/AdminController.php';

Helper::generateCSRFToken();

// ── 解析 URI 路径 ─────────────────────────────────────
$uriPath  = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriPath  = $uriPath === '' ? '/' : $uriPath;
$segments = $uriPath === '/' ? [] : array_values(array_filter(explode('/', ltrim($uriPath, '/'))));
$method   = $_SERVER['REQUEST_METHOD'];

$s0 = $segments[0] ?? '';   // 一级段：login / register / admin / profile / ...
$s1 = $segments[1] ?? '';   // 二级段：update-user / delete / toggle / ...
$s2 = $segments[2] ?? '';   // 三级段：user_id
$s3 = $segments[3] ?? '';   // 四级段：role value

// ── API：数据库初始化 ──────────────────────────────────
if ($s0 === 'api' && $s1 === 'init-db') {
    header('Content-Type: application/json');
    try {
        (new DBInitializer())->init();
        echo json_encode(['success' => true, 'message' => '数据库初始化成功']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── 数据库初始化检查 ───────────────────────────────────
$needsInit = false;
try {
    Database::getInstance()->fetch("SELECT COUNT(*) FROM users LIMIT 1");
} catch (Exception $e) {
    $needsInit = true;
}

// ── 退出登录 ───────────────────────────────────────────
if ($s0 === 'logout') {
    AuthMiddleware::logout();
}

// ══════════════════════════════════════════════════════
// 路由分发 - POST 请求
// ══════════════════════════════════════════════════════
if ($method === 'POST') {
    switch ($s0) {
        case 'login':
            (new AuthController())->handleLogin();
            break;

        case 'register':
            (new AuthController())->handleRegister();
            break;

        case 'profile':
            $ctrl = new ProfileController();
            if ($s1 === 'update-info') {
                $ctrl->handleUpdateInfo();
            } elseif ($s1 === 'change-password') {
                $ctrl->handleChangePassword();
            } elseif ($s1 === 'update-avatar') {
                $ctrl->handleUpdateAvatar();
            }
            break;

        case 'admin':
            $ctrl = new AdminController();
            if ($s1 === 'add-user') {
                $ctrl->handleAddUser();
            } elseif ($s1 === 'update-user') {
                $ctrl->handleUpdateUser();
            } elseif ($s1 === 'reset-password') {
                $ctrl->handleResetPassword();
            } elseif ($s1 === 'batch') {
                $ctrl->handleBatch();
            } elseif ($s1 === 'add-announcement') {
                $ctrl->handleAddAnnouncement();
            } elseif ($s1 === 'delete' && $s2 !== '') {
                $ctrl->handleDelete(intval($s2));
            } elseif ($s1 === 'toggle' && $s2 !== '') {
                $ctrl->handleToggle(intval($s2));
            } elseif ($s1 === 'role' && $s2 !== '' && $s3 !== '') {
                $ctrl->handleRole(intval($s2), $s3);
            } elseif ($s1 === 'unlock' && $s2 !== '') {
                $ctrl->handleUnlock(intval($s2));
            }
            break;
    }
}

// ══════════════════════════════════════════════════════
// 路由分发 - GET 请求的操作型路由（执行后重定向，不渲染视图）
// ══════════════════════════════════════════════════════
if ($method === 'GET' && $s0 === 'admin') {
    // AJAX：登录日志
    if ($s1 === 'logs' && isset($_GET['ajax'])) {
        (new AdminController())->handleGetLogs();
    }
    // 导出 CSV：/admin/export
    if ($s1 === 'export') {
        (new AdminController())->handleExport();
    }
    // 以下操作已迁移至 POST 路由（CSRF 防护），此 GET 块仅保留只读操作
    // 删除公告：/admin/delete-announcement/{id}
    if ($s1 === 'delete-announcement' && $s2 !== '') {
        (new AdminController())->handleDeleteAnnouncement(intval($s2));
    }
    // 切换公告状态：/admin/toggle-announcement/{id}
    if ($s1 === 'toggle-announcement' && $s2 !== '') {
        (new AdminController())->handleToggleAnnouncement(intval($s2));
    }
}

// ══════════════════════════════════════════════════════
// 确定要渲染的视图页面
// ══════════════════════════════════════════════════════
if ($needsInit) {
    $page = 'init';
} else {
    $page = match ($s0) {
        '', 'home'   => 'home',
        'login'      => 'login',
        'register'   => 'register',
        'profile'    => 'profile',
        'admin'      => 'admin',
        default      => 'home',
    };
}

// ── 渲染视图 ───────────────────────────────────────────
require_once __DIR__ . '/../src/views/layout.php';
