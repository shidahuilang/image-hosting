<?php
// 设置时区为北京时间
date_default_timezone_set('Asia/Shanghai');

// 上传文件处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $uploadDir = 'uploads/';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
    // 获取当前脚本的目录路径
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptPath !== '/' && $scriptPath !== '\\') {
        $baseUrl .= $scriptPath;
    }
    
    // 创建上传目录
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $results = [];
    $hasError = false;
    
    // 允许的文件类型
    $allowedTypes = [
        // 图片格式
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico', 'tiff', 'tif',
        // 视频格式
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v', '3gp',
        // 音频格式
        'mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac',
        // 文档格式
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf',
        // 压缩文件
        'zip', 'rar', '7z', 'tar', 'gz',
        // 其他格式
        'json', 'xml', 'csv', 'html', 'htm', 'js', 'css', 'apk', 'ipa', 'mrs'
    ];
    $maxFileSize = 100 * 1024 * 1024; // 100MB
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
            $hasError = true;
            continue;
        }
        
        $originalName = $_FILES['files']['name'][$key];
        $fileSize = $_FILES['files']['size'][$key];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // 检查文件大小
        if ($fileSize > $maxFileSize) {
            $hasError = true;
            continue;
        }
        
        // 检查文件类型
        if (!in_array($fileExtension, $allowedTypes)) {
            $hasError = true;
            continue;
        }
        
        // 生成文件名：所有文件都使用原文件名，如果存在冲突则添加时间戳
        $newFileName = $originalName;
        // 检查文件名是否已存在，如果存在则添加时间戳避免覆盖
        if (file_exists($uploadDir . $newFileName)) {
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            // 使用北京时间的易读格式（年月日时分秒）
            $beijingTime = date('YmdHis');
            $newFileName = $nameWithoutExt . '_' . $beijingTime . '.' . $fileExtension;
        }
        $targetPath = $uploadDir . $newFileName;
        
        // 移动文件
        if (move_uploaded_file($tmpName, $targetPath)) {
            $fileUrl = $baseUrl . '/' . $uploadDir . $newFileName;
            // 清理URL中的重复斜杠
            $fileUrl = preg_replace('#(?<!:)/+#', '/', $fileUrl);
            $results[] = [
                'name' => $originalName,
                'url' => $fileUrl,
                'size' => $fileSize
            ];
        } else {
            $hasError = true;
        }
    }
    
    // 返回JSON结果
    header('Content-Type: application/json');
    echo json_encode([
        'success' => !$hasError && count($results) > 0,
        'files' => $results,
        'message' => $hasError ? '部分文件上传失败' : '上传成功'
    ]);
    exit;
}

// 如果不是POST请求，返回错误
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'files' => [],
    'message' => '无效的请求方法'
]);
exit;
?>