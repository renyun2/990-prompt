<?php
// 后台管理页面
AuthMiddleware::requireAdmin();

$db = Database::getInstance();
$search  = $_GET['search'] ?? '';
$tab     = in_array($_GET['tab'] ?? '', ['users', 'announcements']) ? ($_GET['tab'] ?? 'users') : 'users';
// 分页参数使用 "p" 避免与路由参数 "page" 冲突
$curPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($curPage < 1) $curPage = 1;
$limit  = 10;
$offset = ($curPage - 1) * $limit;

// 消息
$successMsg = '';
$errorMsg   = '';
if (isset($_SESSION['admin_success'])) { $successMsg = $_SESSION['admin_success']; unset($_SESSION['admin_success']); }
if (isset($_SESSION['admin_error']))   { $errorMsg   = $_SESSION['admin_error'];   unset($_SESSION['admin_error']); }

// 用户查询
$whereClause = '';
$params = [];
if ($search) {
    $whereClause = "WHERE username LIKE ? OR email LIKE ? OR name LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$totalRecords = $db->fetchColumn("SELECT COUNT(*) FROM users $whereClause", $params);
$totalPages   = (int)ceil($totalRecords / $limit);
$users        = $db->fetchAll("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $params);

// 统计
$totalUsers    = (int)$db->fetchColumn("SELECT COUNT(*) FROM users");
$adminCount    = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$activeCount   = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_active = 1");
$disabledCount = $totalUsers - $activeCount;
$lockedCount   = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE locked_until IS NOT NULL AND locked_until > NOW()");
$todayLogins   = (int)$db->fetchColumn("SELECT COUNT(*) FROM login_logs WHERE DATE(login_time) = CURDATE()");
$newThisWeek   = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

// 公告
$announcements = $db->fetchAll(
    "SELECT a.*, u.username as creator FROM announcements a
     LEFT JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC"
);
?>

<div class="animate__animated animate__fadeIn max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold text-gray-800">后台管理系统</h2>
        <a href="/admin/export?search=<?php echo urlencode($search); ?>"
           class="flex items-center gap-2 px-4 py-2 text-white rounded-lg transition shadow-md text-sm"
           style="background-color:#0d9488;" onmouseover="this.style.backgroundColor='#0f766e'" onmouseout="this.style.backgroundColor='#0d9488'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            导出 CSV
        </a>
    </div>

    <!-- 消息提示 -->
    <?php if ($successMsg): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span><?php echo Helper::escape($successMsg); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 ml-4">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <span><?php echo Helper::escape($errorMsg); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900 ml-4">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-blue-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">总用户</p>
            <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $totalUsers; ?></p>
            <div class="text-blue-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-green-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">活跃</p>
            <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $activeCount; ?></p>
            <div class="text-green-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-purple-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">管理员</p>
            <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $adminCount; ?></p>
            <div class="text-purple-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-red-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">禁用</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $disabledCount; ?></p>
            <div class="text-red-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-orange-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">锁定</p>
            <p class="text-2xl font-bold text-orange-600 mt-1"><?php echo $lockedCount; ?></p>
            <div class="text-orange-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-yellow-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">今日登录</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo $todayLogins; ?></p>
            <div class="text-yellow-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-indigo-500 hover:shadow-lg transition">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">本周新增</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?php echo $newThisWeek; ?></p>
            <div class="text-indigo-400 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg></div>
        </div>
    </div>

    <!-- Tab 导航 -->
    <div class="flex border-b border-gray-200 mb-0">
        <a href="/admin?tab=users"
           class="px-6 py-3 text-sm font-medium border-b-2 transition <?php echo $tab === 'users' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
            用户管理
        </a>
        <a href="/admin?tab=announcements"
           class="px-6 py-3 text-sm font-medium border-b-2 transition <?php echo $tab === 'announcements' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
            系统公告
            <?php $activeAnnCount = count(array_filter($announcements, fn($a) => $a['is_active'])); ?>
            <?php if ($activeAnnCount > 0): ?>
                <span class="ml-1 px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full"><?php echo $activeAnnCount; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- ══ Tab: 用户管理 ══ -->
    <?php if ($tab === 'users'): ?>
    <div>
        <!-- 工具栏 -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-4 border-b border-gray-200 shadow-sm gap-3">
            <form action="/admin" method="GET" class="flex w-full md:w-auto gap-2 items-center">
                <input type="hidden" name="tab" value="users">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="<?php echo Helper::escape($search); ?>"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-sm"
                           placeholder="搜索用户名/邮箱/姓名...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">搜索</button>
                <?php if($search): ?>
                    <a href="/admin?tab=users" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">重置</a>
                <?php endif; ?>
            </form>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openAddUserModal()" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm flex items-center gap-1 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>新增
                </button>
                <button onclick="openLogsModal()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm flex items-center gap-1 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>登录日志
                </button>
            </div>
        </div>

        <!-- 批量操作栏 (默认隐藏) -->
        <div id="batchBar" class="hidden bg-indigo-50 border-b border-indigo-200 px-4 py-3 flex items-center gap-3 flex-wrap">
            <span class="text-sm text-indigo-700 font-medium">已选 <span id="selectedCount">0</span> 个用户</span>
            <form id="batchForm" method="POST" action="/admin/batch" class="flex items-center gap-2 flex-wrap">
                <?php echo Helper::csrfField(); ?>
                <div id="batchIdsContainer"></div>
                <select name="batch_action" class="text-sm border border-indigo-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">-- 选择批量操作 --</option>
                    <option value="enable">批量启用</option>
                    <option value="disable">批量禁用</option>
                    <option value="delete">批量删除</option>
                </select>
                <button type="submit" onclick="return confirmBatch()"
                        class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition">
                    执行
                </button>
            </form>
            <button onclick="clearSelection()" class="text-sm text-gray-500 hover:text-gray-700 underline">取消选择</button>
        </div>

        <!-- 用户列表 -->
        <div class="bg-white rounded-b-lg shadow-lg overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-4 text-left">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded cursor-pointer">
                            </th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">联系方式</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">角色</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">注册时间</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <?php $isLocked = !empty($user['locked_until']) && strtotime($user['locked_until']) > time(); ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-4">
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <input type="checkbox" class="user-checkbox w-4 h-4 text-purple-600 border-gray-300 rounded cursor-pointer"
                                                   value="<?php echo $user['id']; ?>" onchange="updateBatchBar()">
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <img src="<?php echo Helper::escape($user['avatar']); ?>"
                                                     alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                                                <?php if ($isLocked): ?>
                                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center" title="账户已锁定">
                                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo Helper::escape($user['username']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo Helper::escape($user['name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <div><?php echo Helper::escape($user['email'] ?? '-'); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo Helper::escape($user['phone'] ?? '-'); ?></div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo $user['role'] === 'admin' ? '管理员' : '用户'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full w-fit <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $user['is_active'] ? '活跃' : '禁用'; ?>
                                            </span>
                                            <?php if ($isLocked): ?>
                                                <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full w-fit bg-orange-100 text-orange-800">
                                                    已锁定
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-500">
                                        <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium">
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <div class="flex justify-end gap-1.5 flex-wrap">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded text-xs transition">编辑</button>
                                                <button onclick="openResetPasswordModal(<?php echo $user['id']; ?>, '<?php echo Helper::escape($user['username']); ?>')"
                                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded text-xs transition">重置密码</button>
                                                <?php if ($isLocked): ?>
                                                    <button onclick="confirmUnlockUser(<?php echo $user['id']; ?>, '<?php echo Helper::escape($user['username']); ?>')"
                                                            class="text-orange-600 hover:text-orange-900 bg-orange-50 hover:bg-orange-100 px-2.5 py-1 rounded text-xs transition">解锁</button>
                                                <?php endif; ?>
                                                <button onclick="confirmToggleUser(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 'true' : 'false'; ?>, '<?php echo Helper::escape($user['username']); ?>')"
                                                        class="<?php echo $user['is_active'] ? 'text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100' : 'text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100'; ?> px-2.5 py-1 rounded text-xs transition">
                                                    <?php echo $user['is_active'] ? '禁用' : '启用'; ?>
                                                </button>
                                                <button onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo Helper::escape($user['username']); ?>')"
                                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded text-xs transition">删除</button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">当前账户</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">没有找到相关用户</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <div class="text-sm text-gray-700">
                        共 <span class="font-medium"><?php echo $totalRecords; ?></span> 条，第
                        <span class="font-medium"><?php echo $offset + 1; ?></span>–<span class="font-medium"><?php echo min($offset + $limit, $totalRecords); ?></span> 条
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        <?php if ($curPage > 1): ?>
                            <a href="/admin?tab=users&search=<?php echo urlencode($search); ?>&p=<?php echo $curPage - 1; ?>" class="px-3 py-1 rounded border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 text-sm">‹ 上一页</a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $curPage - 2); $i <= min($totalPages, $curPage + 2); $i++): ?>
                            <a href="/admin?tab=users&search=<?php echo urlencode($search); ?>&p=<?php echo $i; ?>"
                               class="px-3 py-1 rounded border text-sm <?php echo $i === $curPage ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($curPage < $totalPages): ?>
                            <a href="/admin?tab=users&search=<?php echo urlencode($search); ?>&p=<?php echo $curPage + 1; ?>" class="px-3 py-1 rounded border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 text-sm">下一页 ›</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══ Tab: 系统公告 ══ -->
    <?php if ($tab === 'announcements'): ?>
    <div class="bg-white rounded-b-lg shadow-lg overflow-hidden mb-8">
        <!-- 新增公告表单 -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">发布新公告</h3>
            <form method="POST" action="/admin/add-announcement" class="space-y-4">
                <?php echo Helper::csrfField(); ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">公告标题 *</label>
                        <input type="text" name="title" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                               placeholder="输入公告标题">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">类型</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                            <option value="info">ℹ️ 普通通知</option>
                            <option value="success">✅ 成功/喜讯</option>
                            <option value="warning">⚠️ 警告提示</option>
                            <option value="danger">🚨 紧急通知</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">公告内容</label>
                    <textarea name="content" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm resize-none"
                              placeholder="输入公告正文（可选）"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm shadow-md">
                        发布公告
                    </button>
                </div>
            </form>
        </div>

        <!-- 公告列表 -->
        <div class="divide-y divide-gray-100">
            <?php if (empty($announcements)): ?>
                <div class="px-6 py-12 text-center text-gray-500">暂无公告</div>
            <?php else: ?>
                <?php foreach ($announcements as $ann): ?>
                    <?php
                    $typeConfig = [
                        'info'    => ['bg' => 'bg-blue-50',   'border' => 'border-blue-300',  'badge' => 'bg-blue-100 text-blue-700',   'icon' => 'ℹ️'],
                        'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-300', 'badge' => 'bg-green-100 text-green-700', 'icon' => '✅'],
                        'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-300','badge' => 'bg-yellow-100 text-yellow-700','icon' => '⚠️'],
                        'danger'  => ['bg' => 'bg-red-50',    'border' => 'border-red-300',   'badge' => 'bg-red-100 text-red-700',     'icon' => '🚨'],
                    ];
                    $cfg = $typeConfig[$ann['type']] ?? $typeConfig['info'];
                    ?>
                    <div class="px-6 py-4 flex items-start justify-between gap-4 <?php echo !$ann['is_active'] ? 'opacity-50' : ''; ?>">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <span class="text-xl leading-none mt-0.5"><?php echo $cfg['icon']; ?></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="font-semibold text-gray-800 text-sm"><?php echo Helper::escape($ann['title']); ?></span>
                                    <span class="px-2 py-0.5 text-xs rounded-full <?php echo $cfg['badge']; ?>">
                                        <?php echo ['info'=>'通知','success'=>'喜讯','warning'=>'警告','danger'=>'紧急'][$ann['type']] ?? '通知'; ?>
                                    </span>
                                    <?php if (!$ann['is_active']): ?>
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">已隐藏</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ann['content']): ?>
                                    <p class="text-sm text-gray-600 truncate max-w-xl"><?php echo Helper::escape($ann['content']); ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?php echo $ann['creator'] ? Helper::escape($ann['creator']) : '未知'; ?> ·
                                    <?php echo date('Y-m-d H:i', strtotime($ann['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="/admin/toggle-announcement/<?php echo $ann['id']; ?>"
                               class="px-2.5 py-1 text-xs rounded <?php echo $ann['is_active'] ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200'; ?> transition">
                                <?php echo $ann['is_active'] ? '隐藏' : '发布'; ?>
                            </a>
                            <a href="/admin/delete-announcement/<?php echo $ann['id']; ?>"
                               onclick="return confirm('确定删除该公告？')"
                               class="px-2.5 py-1 text-xs bg-red-100 text-red-700 hover:bg-red-200 rounded transition">
                                删除
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- 批量操作 JavaScript -->
<script>
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBatchBar();
}

function updateBatchBar() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const bar = document.getElementById('batchBar');
    document.getElementById('selectedCount').textContent = checked.length;
    if (checked.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        const container = document.getElementById('batchIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
        document.getElementById('selectAll').checked = false;
    }
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBatchBar();
}

function confirmBatch() { return false; } // 由事件监听器接管

document.addEventListener('DOMContentLoaded', function() {
    const batchForm = document.getElementById('batchForm');
    if (batchForm) {
        // 移除旧 onclick，由此监听器统一处理
        const submitBtn = batchForm.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.removeAttribute('onclick');

        batchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const action = batchForm.querySelector('[name="batch_action"]').value;
            if (!action) {
                Swal.fire({ icon: 'warning', title: '请选择操作', text: '请先从下拉菜单中选择要执行的批量操作', confirmButtonColor: '#667eea' });
                return;
            }
            const count = document.querySelectorAll('.user-checkbox:checked').length;
            const labels = { enable: '启用', disable: '禁用', delete: '删除' };
            Swal.fire({
                title: `确认批量${labels[action]}`,
                text: action === 'delete'
                    ? `确定要批量删除 ${count} 个用户？此操作不可逆！`
                    : `确定要批量${labels[action]} ${count} 个用户吗？`,
                icon: action === 'delete' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: `确认${labels[action]}`,
                cancelButtonText: '取消',
                confirmButtonColor: action === 'delete' ? '#dc2626' : '#667eea',
                cancelButtonColor: '#6b7280',
            }).then(result => { if (result.isConfirmed) e.target.submit(); });
        });
    }
});
</script>

<!-- 编辑用户模态框 -->
<div id="editModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">编辑用户</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" action="/admin/update-user" class="p-6 space-y-4">
            <?php echo Helper::csrfField(); ?>
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">电子邮箱</label>
                    <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">真实姓名</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">手机号</label>
                    <input type="tel" name="phone" id="edit_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="13800000000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">角色</label>
                    <select name="role" id="edit_role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="form-checkbox h-4 w-4 text-purple-600 rounded">
                        <span class="text-sm text-gray-700">账户激活</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">取消</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition shadow-md text-sm">保存更改</button>
            </div>
        </form>
    </div>
</div>

<!-- 新增用户模态框 -->
<div id="addUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">新增用户</h3>
            <button onclick="closeAddUserModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" action="/admin/add-user" class="p-6 space-y-4">
            <?php echo Helper::csrfField(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">用户名 *</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="3-20字符">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">电子邮箱</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="选填">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">真实姓名 *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">手机号</label>
                    <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="选填">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">密码 *</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="至少8个字符">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">角色</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="add_is_active" checked class="form-checkbox h-4 w-4 text-purple-600 rounded">
                <label for="add_is_active" class="ml-2 text-sm text-gray-700">账户激活</label>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">取消</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md text-sm">创建用户</button>
            </div>
        </form>
    </div>
</div>

<!-- 重置密码模态框 -->
<div id="resetPasswordModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">重置用户密码</h3>
            <button onclick="closeResetPasswordModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="reset_password_form" method="POST" action="/admin/reset-password" class="p-6 space-y-4">
            <?php echo Helper::csrfField(); ?>
            <input type="hidden" name="user_id" id="reset_user_id">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800">正在为用户 <strong id="reset_username_display"></strong> 重置密码</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">新密码 *</label>
                <input type="password" name="new_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="至少8个字符">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">确认密码 *</label>
                <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm" placeholder="再次输入密码">
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md text-sm">重置密码</button>
            </div>
        </form>
    </div>
</div>

<!-- 登录日志模态框 -->
<div id="logsModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl mx-4 overflow-hidden" style="max-height: 90vh;">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">登录日志（最近 200 条）</h3>
            <button onclick="closeLogsModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="overflow-y-auto" style="max-height: calc(90vh - 120px);">
            <table class="w-full">
                <thead class="bg-gray-50 border-b sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">用户名</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP地址</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">浏览器信息</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">登录时间</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody" class="divide-y divide-gray-200">
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">加载中...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeLogsModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">关闭</button>
        </div>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('edit_user_id').value   = user.id;
    document.getElementById('edit_username').value  = user.username;
    document.getElementById('edit_email').value     = user.email || '';
    document.getElementById('edit_name').value      = user.name;
    document.getElementById('edit_phone').value     = user.phone || '';
    document.getElementById('edit_role').value      = user.role;
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.getElementById('addUserModal').classList.add('flex');
}
function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('addUserModal').classList.remove('flex');
}
function openResetPasswordModal(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_username_display').textContent = username;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
    document.getElementById('resetPasswordModal').classList.add('flex');
}
function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
    document.getElementById('resetPasswordModal').classList.remove('flex');
    document.getElementById('reset_password_form').reset();
}
function openLogsModal() {
    loadLoginLogs();
    document.getElementById('logsModal').classList.remove('hidden');
    document.getElementById('logsModal').classList.add('flex');
}
function closeLogsModal() {
    document.getElementById('logsModal').classList.add('hidden');
    document.getElementById('logsModal').classList.remove('flex');
}
function loadLoginLogs() {
    fetch('/admin/logs?ajax=1')
        .then(r => r.json())
        .then(data => {
            let html = '';
            if (data.logs && data.logs.length > 0) {
                data.logs.forEach(log => {
                    html += `<tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-900">${log.username}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">${log.ip_address}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 max-w-xs truncate" title="${log.user_agent}">${log.user_agent}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">${log.login_time}</td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">暂无登录记录</td></tr>';
            }
            document.getElementById('logsTableBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('logsTableBody').innerHTML =
                '<tr><td colspan="4" class="px-6 py-8 text-center text-red-500">加载失败</td></tr>';
        });
}

// ── CSRF POST 操作辅助 ───────────────────────────────
function postCsrfAction(url) {
    const form = document.getElementById('csrfActionForm');
    form.action = url;
    form.submit();
}

function confirmDeleteUser(id, username) {
    Swal.fire({
        title: '确认删除用户',
        html: `确定要彻底删除用户 <strong>${username}</strong> 吗？<br><span class="text-red-600 text-sm">此操作不可逆，登录记录也将同步删除！</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '确认删除',
        cancelButtonText: '取消',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
    }).then(result => { if (result.isConfirmed) postCsrfAction(`/admin/delete/${id}`); });
}

function confirmToggleUser(id, isActive, username) {
    const action = isActive ? '禁用' : '启用';
    Swal.fire({
        title: `确认${action}用户`,
        text: `确定要${action}用户"${username}"吗？`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `确认${action}`,
        cancelButtonText: '取消',
        confirmButtonColor: isActive ? '#d97706' : '#16a34a',
        cancelButtonColor: '#6b7280',
    }).then(result => { if (result.isConfirmed) postCsrfAction(`/admin/toggle/${id}`); });
}

function confirmUnlockUser(id, username) {
    Swal.fire({
        title: '确认解锁账户',
        text: `确定要解锁用户"${username}"的账户吗？`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '确认解锁',
        cancelButtonText: '取消',
        confirmButtonColor: '#ea580c',
        cancelButtonColor: '#6b7280',
    }).then(result => { if (result.isConfirmed) postCsrfAction(`/admin/unlock/${id}`); });
}
</script>
<!-- 全局 CSRF 表单（供 JS POST 操作使用，不可见） -->
<form id="csrfActionForm" method="POST" action="" class="hidden">
    <?php echo Helper::csrfField(); ?>
</form>