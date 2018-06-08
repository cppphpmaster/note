1.安装编译环境  (apt-get install build-essential   apt-get install libtool)
sudo apt-get install \
build-essential \
gcc \
g++ \
autoconf \
libiconv-hook-dev \
libmcrypt-dev \
libxml2-dev \
libmysqlclient-dev \
libcurl4-openssl-dev \
libjpeg8-dev \
libfreetype6-dev \

2.安装php
cd php-xxx/
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
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-pcre-regex \
--with-iconv \
--with-zlib \
--with-gd \
--with-mhash \
--with-xmlrpc \


--with-curl \
--with-imap-ssl
以上PHP编译选项根据实际情况可调整
sudo make
sudo make install
sudo mkdir /etc/php
sudo cp php.ini-development /etc/php/php.ini
将PHP的可执行目录添加到环境变量中。打开~/.bashrc，在末尾添加如下内容：
export PATH=/usr/local/php/bin:$PATH
export PATH=/usr/local/php/sbin:$PATH
保存后执行：source ~/.bashrc
通过php -v查看php版本，完成。

安装swoole：
cd swoole-src-swoole-1.7.6-stable/
phpize
./configure
sudo make
sudo make install
swoole的./configure有很多额外参数，可以通过./configure --help命令查看,这里均选择默认项
安装完成后，进入/etc/php目录下，打开php.ini文件，在其中加上如下一句：
extension=swoole.so
随后在终端中输入命令php -m查看扩展安装情况。如果在列出的扩展中看到了swoole，则说明安装成功。


安装composer: 
  1. curl -sS https://getcomposer.org/installer | php
  2. 运行php composer.phar检测是否正常工作
  3. 把composer.phar所在目录加入环境变量, windows需要创建对应的批处理文件: echo @php "%~dp0composer.phar" %*>composer.bat
  4. composer -V查看版本
  
  
PHP-FPM: (FastCGI Process Manager：FastCGI进程管理器)是一个PHPFastCGI管理器


查看php.ini所在目录: php -i | grep php.ini
查看php编译参数: php -i | grep configure
查看对应php.ini是否有extension=swoole.so: cat /usr/local/lib/php.ini | grep swoole.so


关闭opcache: 修改php.ini配置项 opcache.enable_cli和opcache.enable
