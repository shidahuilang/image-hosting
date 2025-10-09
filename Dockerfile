# 使用官方PHP Apache镜像
FROM php:8.2-apache

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件到容器
COPY . /var/www/html/

# 安装必要的PHP扩展
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 设置文件上传大小限制（修改php.ini）
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size = 100M" >> /usr/local/etc/php/php.ini
RUN echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini
RUN echo "max_input_time = 300" >> /usr/local/etc/php/php.ini

# 设置上传目录权限
RUN mkdir -p uploads && chmod 755 uploads

# 启用Apache重写模块（如果需要）
RUN a2enmod rewrite

# 暴露端口
EXPOSE 80

# 启动Apache服务
CMD ["apache2-foreground"]