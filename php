安装composer: 
  1. curl -sS https://getcomposer.org/installer | php
  2. 运行php composer.phar检测是否正常工作
  3. 把composer.phar所在目录加入环境变量, windows需要创建对应的批处理文件: echo @php "%~dp0composer.phar" %*>composer.bat
  4. composer -V查看版本
  
安装php:
  ./configure --prefix=/usr/local/php \
--with-config-file-path=/etc/php \
--enable-fpm \
--enable-pcntl \
--enable-mysqlnd \
--enable-opcache \
--enable-sockets \
--enable-sysvmsg \
--enable-sysvsem \
--enable-sysvshm \
--enable-shmop \
--enable-zip \
--enable-soap \
--enable-xml \
--enable-mbstring \
--disable-rpath \
--disable-debug \
--disable-fileinfo \
--with-mysql=mysqlnd \
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-pcre-regex \
--with-iconv \
--with-zlib \
--with-mcrypt \
--with-gd \
--with-openssl \
--with-mhash \
--with-xmlrpc \
--with-curl \
--with-imap-ssl
  
PHP-FPM: (FastCGI Process Manager：FastCGI进程管理器)是一个PHPFastCGI管理器

查看php.ini所在目录: php -i | grep php.ini
查看php编译参数: php -i | grep configure
查看对应php.ini是否有extension=swoole.so: cat /usr/local/lib/php.ini | grep swoole.so

关闭opcache: 修改php.ini配置项 opcache.enable_cli和opcache.enable
