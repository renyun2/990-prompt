<?php
// 注册页面
$error = '';

if (isset($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}
?>

<div class="animate__animated animate__fadeIn flex items-center justify-center py-4">
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden flex min-h-[620px]">

        <!-- 左侧品牌区 -->
        <div class="hidden lg:flex lg:w-2/5 hero-gradient flex-col justify-center items-center p-12 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <path fill="white" d="M39.9,-52.4C50.6,-43.3,57.4,-29.6,62.1,-14.3C66.8,1.1,69.3,18.1,63.5,31.6C57.7,45.1,43.5,55.2,28.2,61.3C12.9,67.4,-3.5,69.6,-18.7,65.3C-33.9,61,-47.9,50.3,-56.5,36.2C-65.1,22.1,-68.4,4.6,-64.8,-10.7C-61.2,-25.9,-50.7,-38.9,-38.4,-48.1C-26,-57.3,-13,-62.6,1.3,-64.2C15.5,-65.7,29.3,-61.4,39.9,-52.4Z" transform="translate(100 100)" />
                </svg>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-3">加入我们</h1>
                <p class="text-purple-100 text-base mb-10">创建您的账户，开始体验</p>
                <div class="space-y-4 text-left">
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">免费注册，立即使用</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="text-sm">个人信息安全加密存储</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white bg-opacity-10 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm">邮箱可选，注册更便捷</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧表单区 -->
        <div class="flex-1 flex flex-col justify-center px-8 py-10 lg:px-12 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">会员注册</h2>
                <p class="text-gray-500 mb-7">填写以下信息创建您的账户</p>

                <?php if ($error): ?>
                    <div class="mb-5 p-4 bg-red-50 border border-red-300 text-red-700 rounded-xl flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span><?php echo Helper::escape($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/register" class="space-y-4">
                    <?php echo Helper::csrfField(); ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                用户名 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="username"
                                   value="<?php echo Helper::escape($_POST['username'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="3-20字符" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                真实姓名 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name"
                                   value="<?php echo Helper::escape($_POST['name'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="请输入真实姓名" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            邮箱 <span class="text-gray-400 font-normal text-xs">（可选）</span>
                        </label>
                        <input type="email" name="email"
                               value="<?php echo Helper::escape($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                               placeholder="example@domain.com">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            手机号 <span class="text-gray-400 font-normal text-xs">（可选）</span>
                        </label>
                        <input type="tel" name="phone"
                               value="<?php echo Helper::escape($_POST['phone'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                               placeholder="13800000000">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                密码 <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="至少8个字符" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                确认密码 <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="confirm_password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition bg-gray-50 focus:bg-white"
                                   placeholder="再次输入密码" required>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full btn-primary text-white font-bold py-3 rounded-xl hover:shadow-lg transition text-base mt-2">
                        创建账户
                    </button>
                </form>

                <p class="text-center text-gray-500 mt-6 text-sm">
                    已有账户？
                    <a href="/login" class="text-purple-600 hover:text-purple-700 font-semibold">立即登录</a>
                </p>
            </div>
        </div>

    </div>
</div>
