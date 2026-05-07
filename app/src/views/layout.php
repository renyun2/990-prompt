<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(102,126,234,.4); }
        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: white; transition: width .3s; }
        .nav-link:hover::after { width: 100%; }
        /* Mobile menu */
        #mobileMenu { transition: transform .25s ease, opacity .25s ease; }
        #mobileMenu.menu-closed { transform: translateY(-8px); opacity: 0; pointer-events: none; }
        #mobileMenu.menu-open  { transform: translateY(0); opacity: 1; pointer-events: auto; }
        /* Announcement colors */
        .ann-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
        .ann-success { background: #f0fdf4; border-color: #86efac; color: #166534; }
        .ann-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
        .ann-danger  { background: #fff1f2; border-color: #fca5a5; color: #991b1b; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- 系统公告横幅 -->
<?php if ($page !== 'init'): ?>
    <?php
    try {
        $annDb = Database::getInstance();
        $activeAnns = $annDb->fetchAll(
            "SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3"
        );
    } catch (Exception $e) {
        $activeAnns = [];
    }
    ?>
    <?php foreach ($activeAnns as $ann): ?>
        <?php
        $annClass = ['info'=>'ann-info','success'=>'ann-success','warning'=>'ann-warning','danger'=>'ann-danger'][$ann['type']] ?? 'ann-info';
        $annIcon  = ['info'=>'ℹ️','success'=>'✅','warning'=>'⚠️','danger'=>'🚨'][$ann['type']] ?? 'ℹ️';
        ?>
        <div class="ann-banner border-b px-4 py-2 <?php echo $annClass; ?> flex items-start justify-between gap-3 text-sm" id="ann-<?php echo $ann['id']; ?>">
            <div class="flex items-start gap-2 flex-1">
                <span class="flex-shrink-0"><?php echo $annIcon; ?></span>
                <div>
                    <strong><?php echo Helper::escape($ann['title']); ?></strong>
                    <?php if ($ann['content']): ?>
                        <span class="ml-2 opacity-80"><?php echo Helper::escape($ann['content']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <button onclick="document.getElementById('ann-<?php echo $ann['id']; ?>').remove()" class="flex-shrink-0 opacity-60 hover:opacity-100 text-lg leading-none">&times;</button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 导航栏 -->
<?php if ($page !== 'init'): ?>
<nav class="hero-gradient text-white shadow-lg relative z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="/" class="text-xl font-bold hover:text-purple-100 transition flex-shrink-0"><?php echo APP_NAME; ?></a>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="/" class="nav-link hover:text-purple-100 transition text-sm">首页</a>
                <?php if (\AuthMiddleware::isLoggedIn()): ?>
                    <a href="/profile" class="nav-link hover:text-purple-100 transition text-sm">个人资料</a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin" class="nav-link hover:text-purple-100 transition text-sm">后台管理</a>
                    <?php endif; ?>
                    <div class="flex items-center space-x-2">
                        <img src="<?php echo Helper::escape($_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'); ?>"
                             alt="头像" class="w-8 h-8 rounded-full object-cover border-2 border-white border-opacity-50">
                        <span class="text-sm font-medium"><?php echo Helper::escape($_SESSION['username']); ?></span>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <span class="px-1.5 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded">管理员</span>
                        <?php endif; ?>
                    </div>
                    <a href="/logout" class="px-3 py-1.5 bg-red-500 rounded-lg hover:bg-red-600 transition text-sm">退出</a>
                <?php else: ?>
                    <a href="/login" class="nav-link hover:text-purple-100 transition text-sm">登录</a>
                    <a href="/register" class="px-4 py-1.5 bg-white text-purple-600 rounded-lg font-semibold hover:bg-purple-50 transition text-sm">注册</a>
                <?php endif; ?>
            </div>

            <!-- Mobile hamburger -->
            <button id="hamburger" onclick="toggleMobileMenu()" class="md:hidden flex flex-col justify-center items-center w-8 h-8 gap-1.5 focus:outline-none">
                <span id="hb1" class="block w-6 h-0.5 bg-white transition-all duration-300"></span>
                <span id="hb2" class="block w-6 h-0.5 bg-white transition-all duration-300"></span>
                <span id="hb3" class="block w-6 h-0.5 bg-white transition-all duration-300"></span>
            </button>
        </div>
    </div>

    <!-- Mobile menu dropdown -->
    <div id="mobileMenu" class="menu-closed md:hidden absolute top-16 left-0 right-0 bg-white text-gray-800 shadow-xl z-40 border-t border-purple-100">
        <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">
            <?php if (\AuthMiddleware::isLoggedIn()): ?>
                <div class="flex items-center gap-3 px-3 py-3 border-b border-gray-100 mb-2">
                    <img src="<?php echo Helper::escape($_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'); ?>"
                         alt="头像" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                    <div>
                        <div class="font-semibold text-gray-800"><?php echo Helper::escape($_SESSION['username']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo Helper::escape($_SESSION['email'] ?? ''); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <a href="/" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-lg hover:bg-gray-100 transition text-sm font-medium">🏠 首页</a>
            <?php if (\AuthMiddleware::isLoggedIn()): ?>
                <a href="/profile" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-lg hover:bg-gray-100 transition text-sm font-medium">👤 个人资料</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="/admin" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-lg hover:bg-gray-100 transition text-sm font-medium">⚙️ 后台管理</a>
                <?php endif; ?>
                <a href="/logout" class="block px-3 py-2.5 rounded-lg hover:bg-red-50 text-red-600 transition text-sm font-medium mt-2 border-t border-gray-100 pt-3">🚪 退出登录</a>
            <?php else: ?>
                <a href="/login" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-lg hover:bg-gray-100 transition text-sm font-medium">🔑 登录</a>
                <a href="/register" onclick="closeMobileMenu()" class="block px-3 py-2.5 rounded-lg bg-purple-600 text-white hover:bg-purple-700 transition text-sm font-medium text-center mt-1">✨ 免费注册</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- 主内容区域 -->
<main class="<?php echo ($page === 'init') ? '' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8'; ?> flex-1">
    <?php
    $pages = ['home', 'register', 'login', 'profile', 'admin', 'init'];
    if (in_array($page, $pages)) {
        include __DIR__ . "/pages/{$page}.php";
    } else {
        include __DIR__ . "/pages/home.php";
    }
    ?>
</main>

<!-- 页脚 -->
<?php if ($page !== 'init'): ?>
<footer class="bg-gray-800 text-gray-300 mt-auto py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        <p class="text-xs mt-1 text-gray-500">Built with PHP 8.2 &amp; MySQL 8.0</p>
    </div>
</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
var mobileMenuOpen = false;

function toggleMobileMenu() {
    mobileMenuOpen = !mobileMenuOpen;
    const menu = document.getElementById('mobileMenu');
    const hb1 = document.getElementById('hb1');
    const hb2 = document.getElementById('hb2');
    const hb3 = document.getElementById('hb3');
    if (mobileMenuOpen) {
        menu.classList.remove('menu-closed');
        menu.classList.add('menu-open');
        hb1.style.transform = 'translateY(8px) rotate(45deg)';
        hb2.style.opacity   = '0';
        hb3.style.transform = 'translateY(-8px) rotate(-45deg)';
    } else {
        closeMobileMenu();
    }
}

function closeMobileMenu() {
    mobileMenuOpen = false;
    const menu = document.getElementById('mobileMenu');
    menu.classList.remove('menu-open');
    menu.classList.add('menu-closed');
    document.getElementById('hb1').style.transform = '';
    document.getElementById('hb2').style.opacity   = '1';
    document.getElementById('hb3').style.transform = '';
}

// 点击外部关闭
document.addEventListener('click', function(e) {
    const nav = document.querySelector('nav');
    if (mobileMenuOpen && nav && !nav.contains(e.target)) {
        closeMobileMenu();
    }
});

function showError(message) {
    Swal.fire({ icon: 'error', title: '出错', text: message, confirmButtonColor: '#667eea' });
}

function showSuccess(message, callback = null) {
    Swal.fire({ icon: 'success', title: '成功', text: message, confirmButtonColor: '#667eea' }).then(() => {
        if (callback) callback();
    });
}

function showLoading(message = '加载中...') {
    Swal.fire({ title: message, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
}
</script>

</body>
</html>
