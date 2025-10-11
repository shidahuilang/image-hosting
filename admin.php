<?php
// 后台管理页面 - 查看上传的文件
session_start();

// 防止页面缓存，避免重复提交问题
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 简单的密码验证（实际使用中应该使用更安全的验证方式）
$admin_password = 'a4131224'; // 默认密码，可以修改

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // 显示登录表单
        ?>
        <!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>后台管理 - 登录</title>
            <link rel="icon" type="image/x-icon" href="favicon.ico">
            <link rel="shortcut icon" href="favicon.ico">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Segoe UI', sans-serif;
                    background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    width: 100%;
                    max-width: 400px;
                }
                h1 {
                    color: #4361ee;
                    text-align: center;
                    margin-bottom: 30px;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                label {
                    display: block;
                    margin-bottom: 5px;
                    color: #333;
                    font-weight: 500;
                }
                input[type="password"] {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #ddd;
                    border-radius: 6px;
                    font-size: 16px;
                }
                button {
                    width: 100%;
                    padding: 12px;
                    background: #4361ee;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                button:hover {
                    background: #3a0ca3;
                }
                .error {
                    color: #ef476f;
                    text-align: center;
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>后台管理登录</h1>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">密码：</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">登录</button>
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <div class="error">密码错误！</div>
                    <?php endif; ?>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// 获取上传目录中的所有文件
$uploadDir = 'uploads/';
$files = [];

if (is_dir($uploadDir)) {
    $fileList = scandir($uploadDir);
    foreach ($fileList as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $uploadDir . $file;
            if (is_file($filePath)) {
                $files[] = [
                    'name' => $file,
                    'size' => filesize($filePath),
                    'modified' => filemtime($filePath),
                    'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . '/' . $filePath,
                    // 清理URL中的重复斜杠
                    'clean_url' => preg_replace('#(?<!:)/+#', '/', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . '/' . $filePath)
                ];
            }
        }
    }
    
    // 按修改时间倒序排列
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

// 文件大小格式化函数
function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

// 处理删除请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileToDelete = $_POST['delete_file'];
    $filePath = $uploadDir . $fileToDelete;
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }
    // 使用302重定向到GET请求，避免重复提交
    header('HTTP/1.1 302 Found');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 处理批量删除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_files']) && is_array($_POST['delete_files'])) {
    foreach ($_POST['delete_files'] as $fileToDelete) {
        $filePath = $uploadDir . $fileToDelete;
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
    // 使用302重定向到GET请求，避免重复提交
    header('HTTP/1.1 302 Found');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 文件管理</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.8rem;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4361ee;
        }
        .file-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .file-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 4px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .btn-view {
            background: #4361ee;
            color: white;
        }
        .btn-delete {
            background: #ef476f;
            color: white;
        }
        .btn-copy {
            background: #06d6a0;
            color: white;
        }
        .bulk-actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .select-all {
            margin-right: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        /* 默认隐藏移动端布局 */
        .mobile-file-list {
            display: none;
        }
        
        /* 移动端适配 */
        @media (max-width: 768px) {
            .header {
                padding: 15px;
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .header h1 {
                font-size: 1.4rem;
            }
            
            .container {
                padding: 10px;
            }
            
            .stats {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .bulk-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .file-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 600px;
            }
            
            th, td {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
            
            .file-actions {
                flex-direction: column;
                gap: 5px;
                min-width: 120px;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 0.75rem;
                white-space: nowrap;
            }
            
            /* 隐藏部分列以节省空间 */
            th:nth-child(3), td:nth-child(3) {
                display: none;
            }
            
            th:nth-child(4), td:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.2rem;
            }
            
            .container {
                padding: 5px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 10px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            table {
                min-width: 500px;
            }
            
            th, td {
                padding: 4px 6px;
                font-size: 0.75rem;
            }
            
            .file-actions {
                gap: 3px;
            }
            
            .btn {
                padding: 4px 6px;
                font-size: 0.7rem;
            }
            
            .btn i {
                display: none;
            }
            
            /* 进一步简化表格 */
            th:nth-child(1), td:nth-child(1) {
                width: 30px;
            }
            
            th:nth-child(5), td:nth-child(5) {
                width: 100px;
            }
            
            /* 移动端卡片布局 */
            .mobile-file-list {
                display: none;
            }
            
            .file-table {
                display: block;
            }
        }
        
        /* 桌面端默认样式 - 确保在大屏幕上正常显示 */
        @media (min-width: 601px) {
            .file-table {
                display: block !important;
            }
            
            .mobile-file-list {
                display: none !important;
            }
            
            table {
                min-width: auto;
            }
            
            th, td {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .file-actions {
                display: flex;
                gap: 10px;
                flex-direction: row;
            }
            
            .btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            
            /* 恢复桌面端的列显示 */
            th:nth-child(3), td:nth-child(3) {
                display: table-cell !important;
            }
            
            th:nth-child(4), td:nth-child(4) {
                display: table-cell !important;
            }
        }
        
        @media (max-width: 600px) {
            .file-table {
                display: none;
            }
            
            .mobile-file-list {
                display: block;
            }
            
            .mobile-file-item {
                background: white;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            
            .mobile-file-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            
            .mobile-file-name {
                flex: 1;
                margin-right: 10px;
                word-break: break-all;
            }
            
            .mobile-file-checkbox {
                margin-left: 10px;
                transform: scale(1.2);
                cursor: pointer;
            }
            
            .mobile-file-name {
                font-weight: 600;
                color: #333;
                flex: 1;
                margin-right: 10px;
                word-break: break-all;
            }
            
            .mobile-file-checkbox {
                margin-left: 10px;
                pointer-events: auto; /* 确保复选框可以点击 */
                z-index: 10; /* 提高层级确保可点击 */
            }
            
            .mobile-file-item {
                position: relative;
            }
            
            .mobile-file-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                pointer-events: none; /* 防止整个卡片区域干扰点击 */
            }
            
            .mobile-file-info {
                font-size: 0.8rem;
                color: #666;
                margin-bottom: 10px;
            }
            
            .mobile-file-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }
            
            .mobile-file-actions .btn {
                flex: 1;
                min-width: 80px;
                text-align: center;
                justify-content: center;
                pointer-events: auto;
            }
            
            .mobile-file-actions .btn {
                flex: 1;
                min-width: 80px;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cog"></i> 后台文件管理</h1>
        <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($files); ?></div>
                <div>总文件数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatSize(array_sum(array_column($files, 'size'))); ?></div>
                <div>总占用空间</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($files) > 0 ? date('Y-m-d H:i', $files[0]['modified']) : '无'; ?></div>
                <div>最新上传</div>
            </div>
        </div>
        
        <?php if (count($files) > 0): ?>
        <form method="POST" id="bulkForm">
            <div class="bulk-actions">
                <button type="button" class="btn btn-delete" onclick="bulkDelete();">
                    <i class="fas fa-trash"></i> 批量删除选中文件
                </button>
                <label class="select-all">
                    <input type="checkbox" onchange="toggleSelectAll(this)"> 全选
                </label>
            </div>
            
            <!-- 桌面端表格布局 -->
            <div class="file-table">
                <table>
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" onchange="toggleSelectAll(this)"></th>
                            <th>文件名</th>
                            <th width="100">文件大小</th>
                            <th width="180">上传时间</th>
                            <th width="200">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><input type="checkbox" name="delete_files[]" value="<?php echo htmlspecialchars($file['name']); ?>"></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-file" style="color: #666;"></i>
                                    <?php echo htmlspecialchars($file['name']); ?>
                                </div>
                            </td>
                            <td><?php echo formatSize($file['size']); ?></td>
                            <td><?php echo date('Y-m-d H:i', $file['modified']); ?></td>
                            <td>
                                <div class="file-actions">
                                    <a href="<?php echo $file['clean_url']; ?>" target="_blank" class="btn btn-view">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                    <button type="button" class="btn btn-copy" onclick="copyToClipboard('<?php echo addslashes($file['clean_url']); ?>')">
                                        <i class="fas fa-copy"></i> 复制链接
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" class="btn btn-delete" onclick="return confirm('确定要删除这个文件吗？')">
                                            <i class="fas fa-trash"></i> 删除
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 移动端卡片布局 -->
            <div class="mobile-file-list">
                <?php foreach ($files as $file): ?>
                <div class="mobile-file-item">
                    <div class="mobile-file-header">
                        <div class="mobile-file-name">
                            <i class="fas fa-file" style="color: #666; margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($file['name']); ?>
                        </div>
                        <input type="checkbox" name="delete_files[]" value="<?php echo htmlspecialchars($file['name']); ?>" class="mobile-file-checkbox" style="transform: scale(1.2); margin-left: 10px; cursor: pointer;">
                    </div>
                    <div class="mobile-file-info">
                        大小: <?php echo formatSize($file['size']); ?> | 
                        时间: <?php echo date('Y-m-d H:i', $file['modified']); ?>
                    </div>
                    <div class="mobile-file-actions">
                        <a href="<?php echo $file['clean_url']; ?>" target="_blank" class="btn btn-view">
                            <i class="fas fa-eye"></i> 查看
                        </a>
                        <button type="button" class="btn btn-copy" onclick="copyToClipboard('<?php echo addslashes($file['clean_url']); ?>')">
                            <i class="fas fa-copy"></i> 复制链接
                        </button>
                        <form method="POST" style="display: inline; flex: 1;">
                            <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($file['name']); ?>">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('确定要删除这个文件吗？')" style="width: 100%;">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>暂无上传文件</h3>
            <p>还没有任何文件被上传到服务器</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function copyToClipboard(text) {
            // 优先使用现代Clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess();
                }).catch(function(err) {
                    // 如果现代API失败，使用传统方法
                    traditionalCopy(text);
                });
            } else {
                // 如果不支持现代API，使用传统方法
                traditionalCopy(text);
            }
        }
        
        function showCopySuccess() {
            // 创建一个更友好的提示
            const toast = document.createElement('div');
            toast.textContent = '✓ 链接已复制到剪贴板';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #06d6a0;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                z-index: 9999;
                font-size: 14px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 2000);
        }
        
        function traditionalCopy(text) {
            // 使用传统复制方法，但优化避免滚动
            const textArea = document.createElement('textarea');
            textArea.value = text;
            
            // 设置样式避免影响页面
            textArea.style.cssText = `
                position: fixed;
                left: 0;
                top: 0;
                width: 1px;
                height: 1px;
                opacity: 0;
                pointer-events: none;
            `;
            
            document.body.appendChild(textArea);
            
            try {
                // 使用requestAnimationFrame避免滚动
                requestAnimationFrame(() => {
                    textArea.select();
                    textArea.setSelectionRange(0, 99999);
                    
                    try {
                        const successful = document.execCommand('copy');
                        if (successful) {
                            showCopySuccess();
                        } else {
                            showLinkModal(text);
                        }
                    } catch (err) {
                        showLinkModal(text);
                    } finally {
                        document.body.removeChild(textArea);
                    }
                });
            } catch (err) {
                document.body.removeChild(textArea);
                showLinkModal(text);
            }
        }
        
        function showLinkModal(text) {
            // 创建模态框显示链接
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                padding: 20px;
            `;
            
            const content = document.createElement('div');
            content.style.cssText = `
                background: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 90%;
                width: 400px;
                text-align: center;
            `;
            
            content.innerHTML = `
                <h3 style="margin-bottom: 15px; color: #333;">复制文件链接</h3>
                <input type="text" value="${text}" readonly style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin-bottom: 15px;
                    font-size: 14px;
                " onclick="this.select()">
                <div>
                    <button onclick="document.body.removeChild(this.closest('.modal'))" style="
                        background: #4361ee;
                        color: white;
                        border: none;
                        padding: 8px 16px;
                        border-radius: 4px;
                        cursor: pointer;
                    ">关闭</button>
                </div>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    链接已自动选中，请长按选择"复制"或手动复制
                </p>
            `;
            
            modal.className = 'modal';
            modal.appendChild(content);
            document.body.appendChild(modal);
            
            // 点击背景关闭
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        }
        
        function bulkDelete() {
            // 检查是否有选中的文件
            const checkedCheckboxes = document.querySelectorAll('input[name="delete_files[]"]:checked');
            
            if (checkedCheckboxes.length === 0) {
                alert('请先选择要删除的文件！');
                return;
            }
            
            if (confirm(`确定要删除选中的 ${checkedCheckboxes.length} 个文件吗？`)) {
                document.getElementById('bulkForm').submit();
            }
        }
        
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="delete_files[]"]');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            
            // 同步桌面端和移动端的全选状态
            const allSelectCheckboxes = document.querySelectorAll('input[onchange*="toggleSelectAll"]');
            allSelectCheckboxes.forEach(cb => cb.checked = checkbox.checked);
        }
        
        // 防止重复提交 - 清除POST状态
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // 页面加载完成后添加移动端支持
        document.addEventListener('DOMContentLoaded', function() {
            // 为移动端的复选框添加事件监听
            const mobileCheckboxes = document.querySelectorAll('.mobile-file-checkbox');
            mobileCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // 检查是否所有文件都被选中
                    const allCheckboxes = document.querySelectorAll('input[name="delete_files[]"]');
                    const checkedCheckboxes = document.querySelectorAll('input[name="delete_files[]"]:checked');
                    const selectAllCheckboxes = document.querySelectorAll('input[onchange*="toggleSelectAll"]');
                    
                    selectAllCheckboxes.forEach(cb => {
                        cb.checked = allCheckboxes.length === checkedCheckboxes.length;
                    });
                });
            });
        });
    </script>
    
    <?php
    // 处理退出登录
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: admin.php');
        exit;
    }
    ?>
</body>
</html>