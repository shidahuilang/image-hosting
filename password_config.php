<?php
// 密码配置文件
$admin_password = 'admin123'; // 后台管理密码
$frontend_password = '123456'; // 前端页面密码
$frontend_password_enabled = true; // 前端密码验证开关：true-启用，false-禁用

// 返回密码配置
function getPasswordConfig() {
    global $admin_password, $frontend_password, $frontend_password_enabled;
    return [
        'admin_password' => $admin_password,
        'frontend_password' => $frontend_password,
        'frontend_password_enabled' => $frontend_password_enabled
    ];
}
?>