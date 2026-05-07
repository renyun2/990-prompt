<?php
// 初始化页面
?>

<div class="min-h-screen bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">系统初始化</h1>
            <p class="text-gray-600">欢迎使用会员管理系统</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
                <strong>首次使用提示：</strong>系统需要初始化数据库。点击下方按钮开始初始化。
            </p>
            <p class="text-xs text-blue-600 mt-2">
                初始化过程将创建必要的数据表并添加测试账户。
            </p>
        </div>

        <div class="space-y-4 mb-6">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold text-gray-700">创建数据表</p>
                    <p class="text-sm text-gray-500">users 和 login_logs 表</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold text-gray-700">初始化测试数据</p>
                    <p class="text-sm text-gray-500">1个管理员账户 + 5个测试用户</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold text-gray-700">系统准备就绪</p>
                    <p class="text-sm text-gray-500">初始化完成后可立即使用</p>
                </div>
            </div>
        </div>

        <button id="initBtn" class="w-full btn-primary text-white font-bold py-3 rounded-lg hover:shadow-lg transition flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span>开始初始化</span>
        </button>

        <div id="progressDiv" class="hidden mt-6">
            <div class="bg-gray-100 rounded-full h-2 mb-2">
                <div id="progressBar" class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="progressText" class="text-sm text-gray-600 text-center">准备初始化...</p>
        </div>

        <div id="resultDiv" class="hidden mt-6 p-4 rounded-lg">
            <p id="resultText" class="text-center"></p>
        </div>

        <p class="text-xs text-gray-500 text-center mt-6">
            测试账户：admin / admin123456 (管理员)<br>
            user1-5 / user123456 (普通用户)
        </p>
    </div>
</div>

<script>
document.getElementById('initBtn').addEventListener('click', async function() {
    const btn = this;
    const progressDiv = document.getElementById('progressDiv');
    const resultDiv = document.getElementById('resultDiv');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    // 隐藏按钮，显示进度
    btn.disabled = true;
    btn.style.display = 'none';
    progressDiv.classList.remove('hidden');

    try {
        // 调用初始化API
        const response = await fetch('/api/init-db', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        // 更新进度条
        progressBar.style.width = '100%';
        progressText.textContent = '初始化完成！';

        if (data.success) {
            // 显示成功消息，3秒后重定向
            resultDiv.classList.remove('hidden');
            resultDiv.className = 'hidden mt-6 p-4 rounded-lg bg-green-100 border border-green-400';
            document.getElementById('resultText').className = 'text-center text-green-700 font-semibold';
            document.getElementById('resultText').textContent = '✓ 数据库初始化成功！系统准备就绪...';
            resultDiv.classList.remove('hidden');

            // 3秒后自动刷新页面
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // 显示错误消息
            resultDiv.classList.remove('hidden');
            resultDiv.className = 'hidden mt-6 p-4 rounded-lg bg-red-100 border border-red-400';
            document.getElementById('resultText').className = 'text-center text-red-700';
            document.getElementById('resultText').textContent = '✗ 初始化失败: ' + (data.message || '未知错误');
            resultDiv.classList.remove('hidden');
            btn.style.display = 'block';
            btn.disabled = false;
            progressDiv.classList.add('hidden');
        }
    } catch (error) {
        // 显示错误
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'hidden mt-6 p-4 rounded-lg bg-red-100 border border-red-400';
        document.getElementById('resultText').className = 'text-center text-red-700';
        document.getElementById('resultText').textContent = '✗ 网络错误: ' + error.message;
        resultDiv.classList.remove('hidden');
        btn.style.display = 'block';
        btn.disabled = false;
        progressDiv.classList.add('hidden');
    }
});
</script>
