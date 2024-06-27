# Packages

either pacman -Suy or apt install; <br>
refer to your distro's package manager for more information; sometimes packages
have different names like or have meta packages that include the package you want.

* sqlite3
* lighttpd
* php-fpm php-sqlite3
* (mc, joe and/ or nano) optional

```sh
systemctl status lighttpd.service
systemctl status php8.3-fpm.service
```
(using php-fpm should not install the apache).
Hence that ubuntu like, start the services, arch you do it yourself. <br>
The systemctl command shows you where config files are stored, and more importantly, 
where the php-fpm socket is located.

In /etc/lighttpd/lighttpd.conf add the following lines to the end of the file.

```sh
# php-fpm on ARCH
server.modules += ( "mod_fastcgi" )
index-file.names += ( "index.php" )
fastcgi.server = (
    ".php" => (
      "localhost" => (
        "socket" => "/run/php-fpm/php-fpm.sock",
        "broken-scriptfilename" => "enable"
      ))
)
```
```sh
# php-fpm on UBUNTU
server.modules += ( "mod_fastcgi" )
index-file.names += ( "index.php" )
fastcgi.server = (
    ".php" => (
      "localhost" => (
        "socket" => "/var/run/php/php-fpm.sock",
        "broken-scriptfilename" => "enable"
      ))
)
```


In php.ini (most likely in /etc/php/php.ini or /etc/php/fpm/php.ini)) uncomment the following lines:

```sh
extension=pdo_sqlite
extension=intl
extension=mbstring
```
Restart the services:

```sh
systemctl restart php8.3-fpm.service ; systemctl restart lighttpd.service 
or 
systemctl restart php-fpm.service ; systemctl restart lighttpd.service
```




















