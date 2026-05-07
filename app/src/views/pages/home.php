<?php
$db = Database::getInstance();
?>
<div class="animate__animated animate__fadeIn">
    <!-- 英雄区域 -->
    <section class="hero-gradient text-white rounded-2xl shadow-xl overflow-hidden py-14 md:py-20 mb-10">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <?php if (\AuthMiddleware::isLoggedIn()): ?>
                <div class="flex justify-center mb-4">
                    <img src="<?php echo Helper::escape($_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'); ?>"
                         alt="头像" class="w-16 h-16 rounded-full border-4 border-white border-opacity-50 shadow-lg">
                </div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    欢迎回来，<?php echo Helper::escape($_SESSION['user_name']); ?>！
                </h1>
                <p class="text-purple-200 mb-6 text-sm">
                    <?php echo date('Y年m月d日 H:i'); ?> &nbsp;|&nbsp;
                    <?php echo $_SESSION['user_role'] === 'admin' ? '👑 管理员账户' : '👤 普通用户'; ?>
                </p>
                <div class="flex justify-center gap-3 flex-wrap">
                    <a href="/profile" class="px-6 py-2.5 bg-white text-purple-600 font-semibold rounded-lg hover:bg-purple-50 transition text-sm shadow-md">
                        个人资料
                    </a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin" class="px-6 py-2.5 bg-purple-500 text-white font-semibold rounded-lg hover:bg-purple-600 transition text-sm shadow-md">
                            后台管理
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <h1 class="text-4xl md:text-5xl font-bold mb-4">欢迎来到会员系统</h1>
                <p class="text-xl text-purple-100 mb-8">专业的会员注册管理平台，安全、便捷、高效</p>
                <div class="flex justify-center gap-4 flex-wrap">
                    <a href="/login" class="px-8 py-3 bg-white text-purple-600 font-semibold rounded-lg hover:bg-purple-50 transition shadow-md">立即登录</a>
                    <a href="/register" class="px-8 py-3 bg-purple-500 text-white font-semibold rounded-lg hover:bg-purple-600 transition shadow-md">免费注册</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 系统公告（已登录用户展示） -->
    <?php
    $homeAnns = $db->fetchAll("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
    ?>
    <?php if (!empty($homeAnns)): ?>
    <section class="mb-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
            </svg>
            系统公告
        </h2>
        <div class="space-y-3">
            <?php foreach ($homeAnns as $ann): ?>
                <?php
                $styles = [
                    'info'    => ['bg'=>'bg-blue-50',   'border'=>'border-blue-200',  'text'=>'text-blue-800',  'icon'=>'ℹ️', 'badge'=>'bg-blue-100 text-blue-700'],
                    'success' => ['bg'=>'bg-green-50',  'border'=>'border-green-200', 'text'=>'text-green-800', 'icon'=>'✅', 'badge'=>'bg-green-100 text-green-700'],
                    'warning' => ['bg'=>'bg-yellow-50', 'border'=>'border-yellow-200','text'=>'text-yellow-800','icon'=>'⚠️', 'badge'=>'bg-yellow-100 text-yellow-700'],
                    'danger'  => ['bg'=>'bg-red-50',    'border'=>'border-red-200',   'text'=>'text-red-800',   'icon'=>'🚨', 'badge'=>'bg-red-100 text-red-700'],
                ];
                $s = $styles[$ann['type']] ?? $styles['info'];
                ?>
                <div class="<?php echo $s['bg']; ?> border <?php echo $s['border']; ?> rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xl flex-shrink-0"><?php echo $s['icon']; ?></span>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold <?php echo $s['text']; ?> text-sm"><?php echo Helper::escape($ann['title']); ?></span>
                                <span class="px-1.5 py-0.5 text-xs rounded <?php echo $s['badge']; ?>">
                                    <?php echo ['info'=>'通知','success'=>'喜讯','warning'=>'警告','danger'=>'紧急'][$ann['type']] ?? '通知'; ?>
                                </span>
                            </div>
                            <?php if ($ann['content']): ?>
                                <p class="<?php echo $s['text']; ?> text-sm opacity-80 mt-1"><?php echo Helper::escape($ann['content']); ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-400 mt-1.5"><?php echo date('Y-m-d H:i', strtotime($ann['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 功能特性 -->
    <section class="grid md:grid-cols-3 gap-6 mb-10">
        <div class="card-hover bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500">
            <div class="text-3xl mb-3">🔐</div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">安全可靠</h3>
            <p class="text-gray-600 text-sm">CSRF 防护 + 登录失败自动锁定，全方位保护账户安全</p>
        </div>
        <div class="card-hover bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500">
            <div class="text-3xl mb-3">⚡</div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">便捷高效</h3>
            <p class="text-gray-600 text-sm">简洁注册流程，支持记住登录状态，随时随地快速访问</p>
        </div>
        <div class="card-hover bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500">
            <div class="text-3xl mb-3">🎯</div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">功能完整</h3>
            <p class="text-gray-600 text-sm">个人资料管理、登录日志记录、批量用户操作、CSV 导出</p>
        </div>
    </section>

    <!-- 管理员快速统计面板 -->
    <?php if (\AuthMiddleware::isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            系统概览
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-5 rounded-xl shadow-lg">
                <p class="text-blue-100 text-xs font-medium uppercase tracking-wide">总用户数</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo $db->fetchColumn("SELECT COUNT(*) FROM users"); ?>
                </p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-5 rounded-xl shadow-lg">
                <p class="text-green-100 text-xs font-medium uppercase tracking-wide">今日登录</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo $db->fetchColumn("SELECT COUNT(*) FROM login_logs WHERE DATE(login_time) = CURDATE()"); ?>
                </p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-5 rounded-xl shadow-lg">
                <p class="text-purple-100 text-xs font-medium uppercase tracking-wide">本周新增</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo $db->fetchColumn("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"); ?>
                </p>
            </div>
            <div class="text-white p-5 rounded-xl shadow-lg" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                <p class="text-xs font-medium uppercase tracking-wide" style="color:rgba(255,255,255,0.8)">活跃公告</p>
                <p class="text-3xl font-bold mt-1 text-white">
                    <?php
                    try {
                        echo $db->fetchColumn("SELECT COUNT(*) FROM announcements WHERE is_active = 1");
                    } catch (Exception $e) {
                        echo 0;
                    }
                    ?>
                </p>
            </div>
        </div>
        <div class="mt-4 text-right">
            <a href="/admin" class="text-sm text-purple-600 hover:text-purple-800 font-medium">查看后台管理 →</a>
        </div>
    </section>
    <?php endif; ?>

</div>
