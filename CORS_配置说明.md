# CORS 跨域配置说明

## 📋 已完成的修改

本次更新为图床系统添加了完整的 CORS (跨域资源共享) 支持,解决了前端跨域访问后端 API 的问题。

### 修改的文件

#### 1. `upload.php` - 文件上传接口
**添加的 CORS 响应头:**
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Max-Age: 86400');
```

**功能:**
- 允许所有域名访问上传接口
- 支持 POST、GET、OPTIONS 请求方法
- 允许 Content-Type 和 X-Requested-With 请求头
- 预检请求缓存 24 小时,减少网络开销

#### 2. `verify_password.php` - 密码验证接口
**添加的 CORS 响应头:**
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Max-Age: 86400');
```

**功能:**
- 允许跨域密码验证请求
- 处理浏览器的 OPTIONS 预检请求
- 支持现代浏览器的跨域安全机制

---

## 🔧 CORS 工作原理

### 什么是 CORS?
CORS (Cross-Origin Resource Sharing) 是一种浏览器安全机制,用于控制不同源之间的资源访问。

### 何时会触发跨域?
当满足以下任一条件时,浏览器会认为是跨域请求:
- **协议不同**: `http://` vs `https://`
- **域名不同**: `example.com` vs `api.example.com`
- **端口不同**: `localhost:8080` vs `localhost:3000`

### OPTIONS 预检请求
对于某些跨域请求,浏览器会先发送一个 OPTIONS 请求来"询问"服务器是否允许实际请求。我们的代码已经处理了这种情况:

```php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

---

## 🎯 适用场景

现在您的图床系统可以在以下场景下正常工作:

### ✅ 场景 1: 前后端分离部署
```
前端: https://cdn.example.com
后端: https://api.example.com
```

### ✅ 场景 2: 本地开发
```
前端: http://localhost:3000
后端: http://localhost:8080
```

### ✅ 场景 3: 第三方网站调用
```
第三方网站: https://other-site.com
您的 API: https://your-image-host.com
```

### ✅ 场景 4: 移动应用/桌面应用
- Electron 应用
- React Native 应用
- 微信小程序 (需要在小程序后台配置域名白名单)

---

## 🔒 生产环境安全建议

当前配置使用 `Access-Control-Allow-Origin: *`,允许**所有域名**访问。这在开发环境很方便,但在生产环境存在安全风险。

### 推荐的生产环境配置

#### 方法 1: 指定单个域名
```php
header('Access-Control-Allow-Origin: https://your-frontend.com');
header('Access-Control-Allow-Credentials: true'); // 允许携带 Cookie
```

#### 方法 2: 域名白名单
```php
$allowedOrigins = [
    'https://your-frontend.com',
    'https://www.your-frontend.com',
    'https://admin.your-frontend.com'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: https://your-frontend.com'); // 默认域名
}
```

#### 方法 3: 动态验证来源
```php
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// 只允许您自己的域名
if (preg_match('/^https?:\/\/(.*\.)?your-domain\.com$/', $origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}
```

---

## 🧪 测试 CORS 配置

### 测试方法 1: 浏览器控制台
在任意网页的浏览器控制台中运行:

```javascript
fetch('http://your-image-host.com/upload.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({test: 'data'})
})
.then(response => response.json())
.then(data => console.log('成功:', data))
.catch(error => console.error('失败:', error));
```

### 测试方法 2: cURL 命令
```bash
curl -X OPTIONS http://your-image-host.com/upload.php \
  -H "Origin: http://example.com" \
  -H "Access-Control-Request-Method: POST" \
  -v
```

查看响应头中是否包含:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: POST, GET, OPTIONS
```

### 测试方法 3: 在线工具
使用在线 CORS 测试工具:
- https://www.test-cors.org/
- https://cors-test.codehappy.dev/

---

## 📝 常见问题

### Q1: 为什么还是报跨域错误?
**可能原因:**
1. PHP 文件没有正确保存
2. Web 服务器缓存了旧的响应
3. 浏览器缓存了预检请求结果

**解决方法:**
```bash
# 重启 Web 服务器
docker-compose restart  # Docker 部署
# 或
systemctl restart apache2  # Apache
systemctl restart nginx    # Nginx

# 清除浏览器缓存或使用无痕模式测试
```

### Q2: 为什么 Cookie 无法携带?
**原因:** 使用 `*` 通配符时,浏览器不允许携带凭证(Cookie)。

**解决方法:** 指定具体域名并添加:
```php
header('Access-Control-Allow-Origin: https://your-domain.com');
header('Access-Control-Allow-Credentials: true');
```

前端请求时也要设置:
```javascript
fetch(url, {
    credentials: 'include'  // 携带 Cookie
})
```

### Q3: 如何限制只允许特定 IP 访问?
CORS 是基于域名的,如果要限制 IP,需要在 PHP 中额外检查:

```php
$allowedIPs = ['192.168.1.100', '10.0.0.50'];
$clientIP = $_SERVER['REMOTE_ADDR'];

if (!in_array($clientIP, $allowedIPs)) {
    http_response_code(403);
    echo json_encode(['error' => '访问被拒绝']);
    exit;
}
```

---

## 🚀 后续优化建议

1. **添加请求频率限制**: 防止 API 被滥用
2. **实现 API Token 认证**: 替代简单的密码验证
3. **记录访问日志**: 监控跨域请求来源
4. **使用 HTTPS**: 确保数据传输安全
5. **配置 CSP 头**: 进一步增强安全性

---

## 📚 相关资源

- [MDN - CORS 详解](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CORS)
- [PHP 官方文档 - header()](https://www.php.net/manual/zh/function.header.php)
- [CORS 规范](https://www.w3.org/TR/cors/)

---

**更新时间**: 2026-01-12  
**版本**: 1.0  
**维护者**: 大灰狼图床系统
