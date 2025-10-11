<?php
// 密码验证API接口 - 前端页面专用
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 引入密码配置
require_once 'password_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';
    
    $config = getPasswordConfig();
    
    // 检查前端密码验证是否启用
    if (!$config['frontend_password_enabled']) {
        // 如果密码验证被禁用，直接返回成功
        echo json_encode(['success' => true, 'message' => '密码验证已禁用']);
    } else {
        // 验证前端密码
        if ($password === $config['frontend_password']) {
            echo json_encode(['success' => true, 'message' => '密码正确']);
        } else {
            echo json_encode(['success' => false, 'message' => '密码错误']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
}
?>