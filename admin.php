<?php
// 后台管理页面 - 查看上传的文件
session_start();

// 简单的密码验证（实际使用中应该使用更安全的验证方式）
$admin_password = 'admin123'; // 默认密码，可以修改

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
                    'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . '/' . $filePath
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
if (isset($_POST['delete_file'])) {
    $fileToDelete = $_POST['delete_file'];
    $filePath = $uploadDir . $fileToDelete;
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit;
    }
}

// 处理批量删除
if (isset($_POST['delete_files']) && is_array($_POST['delete_files'])) {
    foreach ($_POST['delete_files'] as $fileToDelete) {
        $filePath = $uploadDir . $fileToDelete;
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 文件管理</title>
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
                <button type="button" class="btn btn-delete" onclick="if(confirm('确定要删除选中的文件吗？')) document.getElementById('bulkForm').submit();">
                    <i class="fas fa-trash"></i> 批量删除选中文件
                </button>
                <label class="select-all">
                    <input type="checkbox" onchange="toggleSelectAll(this)"> 全选
                </label>
            </div>
            
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
                                    <a href="<?php echo $file['url']; ?>" target="_blank" class="btn btn-view">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                    <button type="button" class="btn btn-copy" onclick="copyToClipboard('<?php echo addslashes($file['url']); ?>')">
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
            // 使用现代API，如果失败则使用备用方法
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('链接已复制到剪贴板！');
                }).catch(function(err) {
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }
        
        function fallbackCopyToClipboard(text) {
            // 备用方法：创建临时textarea元素
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    alert('链接已复制到剪贴板！');
                } else {
                    alert('复制失败，请手动复制链接：' + text);
                }
            } catch (err) {
                alert('复制失败，请手动复制链接：' + text);
            }
            
            document.body.removeChild(textArea);
        }
        
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="delete_files[]"]');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }
    </script>
    
    <?php
    // 处理退出登录
    if (isset($_GET['logout'])) {
        session_destroy();
        echo '<script>window.location.href = "admin.php";</script>';
        exit;
    }
    ?>
</body>
</html>