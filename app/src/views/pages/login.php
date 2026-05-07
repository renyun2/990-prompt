<?php
// 登录页面
$error             = '';
$success           = '';
$remember_username = '';

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if (isset($_SESSION['register_success'])) {
    $success = '注册成功，请登录！';
    unset($_SESSION['register_success']);
}

if (isset($_COOKIE['username'])) {
    $remember_username = $_COOKIE['username'];
}
?>

<div class="animate__animated animate__fadeIn flex items-center justify-center py-4">
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden flex min-h-[560px]">

        <!-- 左侧品牌区 -->
        <div class="hidden lg:flex lg:w-2/5 hero-gradient flex-col justify-center items-center p-12 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <path fill="white" d="M44.9,-62.3C56.5,-52.6,62.8,-36.5,67.1,-19.9C71.4,-3.3,73.7,13.8,67.6,27.5C61.4,41.3,46.8,51.7,31.4,58.5C16,65.4,-0.2,68.7,-16.5,65.5C-32.8,62.3,-49.2,52.6,-58.6,38.8C-68,25,-70.4,7.1,-67.1,-9.2C-63.8,-25.5,-54.8,-40.2,-42.5,-50.2C-30.2,-60.1,-15.1,-65.3,1.5,-67.2C18.2,-69,33.2,-72,44.9,-62.3Z" transform="translate(100 100)" />
                </svg>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-3">欢迎回来</h1>
                <p class="text-purple-100 text-base mb-10">登录以继续使用会员管理系统</p>
                <div class="space-y-4 text-left">
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="text-sm">bcrypt 加密保护账户安全</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="text-sm">CSRF 防护机制</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-sm">完整的登录日志记录</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧表单区 -->
        <div class="flex-1 flex flex-col justify-center px-8 py-10 lg:px-12">
            <div class="max-w-md w-full mx-auto">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">会员登录</h2>
                <p class="text-gray-500 mb-8">请输入您的账户信息</p>

                <?php if ($success): ?>
                    <div class="mb-5 p-4 bg-green-50 border border-green-300 text-green-700 rounded-xl flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span><?php echo Helper::escape($success); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-5 p-4 bg-red-50 border border-red-300 text-red-700 rounded-xl flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span><?php echo Helper::escape($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login" class="space-y-5">
                    <?php echo Helper::csrfField(); ?>

                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-1.5">用户名</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="请输入用户名"
                                   value="<?php echo Helper::escape($remember_username); ?>" />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">密码</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="请输入密码" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" id="remember"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded"
                                   <?php echo !empty($remember_username) ? 'checked' : ''; ?> />
                            <span class="text-sm text-gray-600">记住我</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full btn-primary text-white font-bold py-3 rounded-xl hover:shadow-lg transition text-base">
                        登录
                    </button>
                </form>

                <p class="text-center text-gray-500 mt-8 text-sm">
                    还没有账户？
                    <a href="/register" class="text-purple-600 hover:text-purple-700 font-semibold">立即注册</a>
                </p>
            </div>
        </div>

    </div>
</div>
