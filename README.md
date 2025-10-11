# 大灰狼图床系统 4.0

一个简单易用的文件托管和图床系统，支持多种文件格式上传和直链生成。

## 🚀 系统特性

- ✅ **多格式支持**：图片、视频、音频、文档、压缩文件等
- ✅ **文件直链**：上传后立即生成可访问的直链
- ✅ **后台管理**：完整的文件管理界面
- ✅ **重复处理**：智能处理重复文件名
- ✅ **响应式设计**：适配各种设备屏幕
- ✅ **Docker部署**：一键部署，环境隔离

## 📁 支持的文件格式

### 图片格式
JPG, JPEG, PNG, GIF, WEBP, BMP, SVG, ICO, TIFF, TIF

### 视频格式  
MP4, AVI, MOV, WMV, FLV, MKV, WEBM, M4V, 3GP

### 音频格式
MP3, WAV, OGG, M4A, FLAC, AAC

### 文档格式
PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, RTF

### 压缩文件
ZIP, RAR, 7Z, TAR, GZ

### 应用程序
APK, IPA, MRS

### 其他格式
JSON, XML, CSV, HTML, HTM, JS, CSS

## 🛠️ 系统要求

- Docker 和 Docker Compose
- 或 PHP 7.4+ 环境（Apache/Nginx）

## 🐳 Docker 部署（推荐）

### 1. 环境准备
确保系统已安装 Docker 和 Docker Compose：

```bash
# 检查Docker是否安装
docker --version
docker-compose --version
```

### 2. 下载项目
```bash
cd 大灰狼图床系统4.0源码
git clone https://github.com/shidahuilang/image-hosting.git
```

### 3. 启动服务
```bash
# 使用docker-compose一键部署
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 4. 访问系统
- **前端页面**：http://localhost:8080
- **后台管理**：http://localhost:8080/admin.php
- **后台默认密码**：admin123
- **前台默认密码**：123456

### 5. 管理命令
```bash
# 停止服务
docker-compose down

# 重启服务
docker-compose restart

# 更新服务（修改代码后）
docker-compose up -d --build

# 查看容器日志
docker-compose logs image-host
```

### 6. 单独Docker命令（可选）
如果不想使用docker-compose，可以使用单独的Docker命令：

```bash
# 构建镜像
docker build -t image-host .

# 运行容器
docker run -d -p 8080:80 \
  -v $(pwd):/var/www/html \
  -v $(pwd)/uploads:/var/www/html/uploads \
  --name image-host image-host
```

## 📋 传统部署（PHP环境）

### 1. 环境要求
- PHP 7.4+
- Apache/Nginx Web服务器
- 文件上传权限

### 2. 部署步骤
```bash
# 上传文件到Web目录
将项目文件上传到网站根目录，如 /var/www/html/

# 设置上传目录权限
chmod 755 uploads/
chown www-data:www-data uploads/  # Apache用户

# 配置Web服务器
# 确保支持PHP文件解析和重写规则
```

### 3. 访问系统
- 前端页面：http:///localhost/
- 后台管理：http:///localhost/admin.php

## 🔧 系统配置

### 文件大小限制
默认限制为100MB，如需修改：

**Docker部署**：修改 `Dockerfile` 中的配置：
```dockerfile
RUN echo "upload_max_filesize = 200M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size = 200M" >> /usr/local/etc/php/php.ini
```

**PHP部署**：修改 `php.ini`：
```ini
upload_max_filesize = 200M
post_max_size = 200M
max_execution_time = 300
```

### 前端和后台密码修改
```
编辑 `password_config.php` 文件
```

## 📊 系统功能

### 前端功能
- ✅ 拖拽/点击上传文件
- ✅ 多文件同时上传
- ✅ 实时上传进度显示
- ✅ 文件格式验证
- ✅ 文件大小限制
- ✅ 上传成功直链生成
- ✅ 一键复制直链
- ✅ 前端访问密码

### 后台管理功能
- ✅ 文件列表查看
- ✅ 文件搜索筛选
- ✅ 单个文件删除
- ✅ 批量文件删除
- ✅ 文件直链复制
- ✅ 文件预览查看
- ✅ 上传统计信息
- ✅ 空间使用情况

## 🔒 安全说明

### 已实现的安全措施
- ✅ 文件类型白名单验证
- ✅ 文件大小限制
- ✅ 文件名安全处理
- ✅ XSS防护
- ✅ 后台密码保护
- ✅ 会话管理

### 安全建议
1. **修改默认密码**：部署后立即修改后台密码
2. **定期清理文件**：避免存储空间耗尽
3. **限制访问IP**：生产环境建议限制后台访问IP
4. **启用HTTPS**：保护数据传输安全
5. **定期备份**：重要文件定期备份

## 🐛 常见问题

### Q: 上传文件失败怎么办？
A: 检查以下项目：
- 文件大小是否超过限制
- 文件格式是否支持
- 上传目录权限是否正确
- 服务器磁盘空间是否充足

### Q: 后台无法登录？
A: 检查：
- 密码是否正确（默认：admin123）
- 前端访问密码（默认：123456）
- 会话功能是否正常
- 浏览器Cookie设置

### Q: 直链无法访问？
A: 检查：
- 文件是否被删除
- Web服务器配置是否正确
- 文件路径权限

### Q: Docker部署端口冲突？
A: 修改 `docker-compose.yml` 中的端口映射：
```yaml
ports:
  - "8081:80"  # 改为其他端口
```

## 📝 更新日志

### v4.0 (2025-10-10)
- ✅ 新增Docker部署支持
- ✅ 增加多种文件格式支持
- ✅ 优化后台管理界面
- ✅ 修复重复文件名处理
- ✅ 改进时间戳显示（北京时间）
- ✅ 增强安全性措施

### v3.0 (2025-09-01)  
- 初始版本发布
- 基础文件上传功能
- 简单后台管理

## 📞 技术支持

如有问题或建议，请联系开发团队。

## 📄 许可证

本项目仅供学习和内部使用。

---


**注意**：生产环境部署前请务必修改默认密码并进行安全配置。


