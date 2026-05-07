<?php
// 个人资料修改页面
AuthMiddleware::requireLogin();

$db       = Database::getInstance();
$userInfo = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

$loginCount = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM login_logs WHERE user_id = ?", [$_SESSION['user_id']]
);

$update_success  = $_SESSION['update_success']  ?? '';
$update_error    = $_SESSION['update_error']    ?? '';
$password_success = $_SESSION['password_success'] ?? '';
$password_error  = $_SESSION['password_error']  ?? '';

unset(
    $_SESSION['update_success'],  $_SESSION['update_error'],
    $_SESSION['password_success'], $_SESSION['password_error']
);

$isLocked   = !empty($userInfo['locked_until']) && strtotime($userInfo['locked_until']) > time();
$memberDays = (int)ceil((time() - strtotime($userInfo['created_at'])) / 86400);
?>

<div class="max-w-3xl mx-auto animate__animated animate__fadeIn">

    <!-- 顶部资料卡 -->
    <div class="hero-gradient rounded-2xl shadow-xl p-6 mb-6 text-white">
        <div class="flex items-center gap-5">
            <div class="relative flex-shrink-0">
                <img src="<?php echo Helper::escape($userInfo['avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'); ?>"
                     alt="头像" class="w-20 h-20 rounded-full border-4 border-white border-opacity-50 object-cover shadow-lg">
                <?php if ($userInfo['role'] === 'admin'): ?>
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs">👑</span>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-2xl font-bold"><?php echo Helper::escape($userInfo['name']); ?></h2>
                    <?php if ($userInfo['role'] === 'admin'): ?>
                        <span class="px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">管理员</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-white bg-opacity-20 text-white text-xs font-medium rounded-full">普通用户</span>
                    <?php endif; ?>
                    <?php if (!$userInfo['is_active']): ?>
                        <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">已禁用</span>
                    <?php endif; ?>
                </div>
                <p class="text-purple-100 text-sm mt-0.5">@<?php echo Helper::escape($userInfo['username']); ?></p>
                <?php if ($userInfo['email']): ?>
                    <p class="text-purple-200 text-sm mt-0.5"><?php echo Helper::escape($userInfo['email']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 快速统计 -->
        <div class="grid grid-cols-3 gap-3 mt-5 pt-5 border-t border-white border-opacity-20">
            <div class="text-center">
                <div class="text-2xl font-bold"><?php echo $loginCount; ?></div>
                <div class="text-xs text-purple-200 mt-0.5">累计登录次数</div>
            </div>
            <div class="text-center border-x border-white border-opacity-20">
                <div class="text-2xl font-bold"><?php echo $memberDays; ?></div>
                <div class="text-xs text-purple-200 mt-0.5">注册天数</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold">
                    <?php echo $userInfo['last_login'] ? date('m/d', strtotime($userInfo['last_login'])) : '—'; ?>
                </div>
                <div class="text-xs text-purple-200 mt-0.5">上次登录</div>
            </div>
        </div>
    </div>

    <!-- 消息提示 -->
    <?php foreach ([
        [$update_success,   'green'],
        [$update_error,     'red'],
        [$password_success, 'green'],
        [$password_error,   'red'],
    ] as [$msg, $color]): ?>
        <?php if ($msg): ?>
            <div class="mb-4 p-4 bg-<?php echo $color; ?>-100 border border-<?php echo $color; ?>-400 text-<?php echo $color; ?>-700 rounded-lg text-sm flex items-center gap-2">
                <?php echo $color === 'green' ? '✅' : '❌'; ?>
                <?php echo Helper::escape($msg); ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- 锁定提示 -->
    <?php if ($isLocked): ?>
        <div class="mb-4 p-4 bg-orange-100 border border-orange-400 text-orange-700 rounded-lg text-sm flex items-center gap-2">
            🔒 您的账户已被临时锁定（由于多次登录失败）。锁定将于 <?php echo date('H:i', strtotime($userInfo['locked_until'])); ?> 自动解除，或联系管理员手动解锁。
        </div>
    <?php endif; ?>

    <!-- 更换头像 -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-1 h-5 bg-pink-500 rounded-full inline-block"></span>更换头像
        </h3>
        <form method="POST" action="/profile/update-avatar" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-6">
            <?php echo Helper::csrfField(); ?>
            <!-- 当前头像预览 -->
            <div class="flex-shrink-0 text-center">
                <img id="avatarPreview"
                     src="<?php echo Helper::escape($userInfo['avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'); ?>"
                     alt="当前头像" class="w-24 h-24 rounded-full object-cover border-4 border-gray-100 shadow-md">
                <p class="text-xs text-gray-400 mt-2">当前头像</p>
            </div>
            <!-- 上传控件 -->
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-2">选择新头像</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-400 transition cursor-pointer"
                     onclick="document.getElementById('avatarInput').click()">
                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">点击选择图片</p>
                    <p class="text-xs text-gray-400 mt-1">支持 JPG、PNG、GIF、WebP，最大 5MB</p>
                </div>
                <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                       class="hidden" onchange="previewAvatar(this)">
                <p id="avatarFileName" class="text-xs text-gray-500 mt-2 hidden"></p>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="btn-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:shadow-lg transition text-sm">
                        上传头像
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 编辑基本信息 -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-1 h-5 bg-purple-500 rounded-full inline-block"></span>基本信息
        </h3>
        <form method="POST" action="/profile/update-info" class="space-y-5">
            <?php echo Helper::csrfField(); ?>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">用户名</label>
                    <input type="text" value="<?php echo Helper::escape($userInfo['username']); ?>"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm" disabled>
                    <p class="text-xs text-gray-400 mt-1">用户名不可修改</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">邮箱 <span class="text-gray-400 text-xs font-normal">（可选）</span></label>
                    <input type="email" name="email" value="<?php echo Helper::escape($userInfo['email'] ?? ''); ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent text-sm"
                           placeholder="example@domain.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">真实姓名</label>
                    <input type="text" name="name" value="<?php echo Helper::escape($userInfo['name']); ?>" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">手机号</label>
                    <input type="tel" name="phone" value="<?php echo Helper::escape($userInfo['phone'] ?? ''); ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent text-sm"
                           placeholder="13800000000">
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button type="submit" class="btn-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:shadow-lg transition text-sm">
                    保存信息
                </button>
            </div>
        </form>
    </div>

    <!-- 修改密码 -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-1 h-5 bg-blue-500 rounded-full inline-block"></span>修改密码
        </h3>
        <form method="POST" action="/profile/change-password" class="space-y-4">
            <?php echo Helper::csrfField(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">当前密码</label>
                <input type="password" name="old_password"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm"
                       placeholder="输入当前密码">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">新密码</label>
                    <input type="password" name="new_password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm"
                           placeholder="至少8个字符">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">确认新密码</label>
                    <input type="password" name="confirm_password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm"
                           placeholder="确认新密码">
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-lg transition text-sm">
                    修改密码
                </button>
            </div>
        </form>
    </div>

    <!-- 账号详情 + 最近登录 -->
    <div class="grid md:grid-cols-2 gap-5">
        <!-- 账号信息 -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full inline-block"></span>账号详情
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">用户ID</dt>
                    <dd class="font-medium text-gray-800">#<?php echo $userInfo['id']; ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">账户角色</dt>
                    <dd>
                        <?php if ($userInfo['role'] === 'admin'): ?>
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">管理员</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">普通用户</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">账户状态</dt>
                    <dd>
                        <?php if ($userInfo['is_active']): ?>
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">正常</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">已禁用</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">注册时间</dt>
                    <dd class="font-medium text-gray-800 text-right"><?php echo date('Y-m-d H:i', strtotime($userInfo['created_at'])); ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">上次登录</dt>
                    <dd class="font-medium text-gray-800 text-right">
                        <?php echo $userInfo['last_login'] ? date('Y-m-d H:i', strtotime($userInfo['last_login'])) : '暂无'; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">累计登录</dt>
                    <dd class="font-medium text-gray-800"><?php echo $loginCount; ?> 次</dd>
                </div>
            </dl>
        </div>

        <!-- 最近登录 -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-1 h-5 bg-yellow-500 rounded-full inline-block"></span>最近登录记录
            </h3>
            <?php
            $logs = $db->fetchAll(
                "SELECT * FROM login_logs WHERE user_id = ? ORDER BY login_time DESC LIMIT 8",
                [$_SESSION['user_id']]
            );
            ?>
            <?php if (empty($logs)): ?>
                <p class="text-sm text-gray-400">暂无登录记录</p>
            <?php else: ?>
                <div class="space-y-2.5 overflow-y-auto" style="max-height: 220px;">
                    <?php foreach ($logs as $log): ?>
                        <div class="flex items-start justify-between text-xs gap-2">
                            <div>
                                <div class="text-gray-700 font-medium"><?php echo date('m-d H:i', strtotime($log['login_time'])); ?></div>
                                <div class="text-gray-400"><?php echo Helper::escape($log['ip_address']); ?></div>
                            </div>
                            <div class="text-gray-400 truncate text-right max-w-[130px]" title="<?php echo Helper::escape($log['user_agent']); ?>">
                                <?php
                                $ua = $log['user_agent'] ?? '';
                                if (str_contains($ua, 'Mobile')) echo '📱 移动端';
                                elseif (str_contains($ua, 'Chrome')) echo '🌐 Chrome';
                                elseif (str_contains($ua, 'Firefox')) echo '🦊 Firefox';
                                elseif (str_contains($ua, 'Safari')) echo '🧭 Safari';
                                else echo '💻 桌面端';
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
        const nameEl = document.getElementById('avatarFileName');
        nameEl.textContent = '已选择：' + file.name + '（' + (file.size / 1024).toFixed(1) + ' KB）';
        nameEl.classList.remove('hidden');
    }
}
</script>