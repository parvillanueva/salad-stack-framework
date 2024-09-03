# SaladStack

## Prerequisite 
The easiest way to install SaladStack is to use a composer that will resolve and install the PHP dependencies required by the Flarum. Hence, just download the composer to set it up.
```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
```
```bash
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```
To confirm Composer on our system use:
```bash
composer -V
```

## Install SaladStack
### Create a directory in your webroot folder: Here it is ‘salad‘.
```bash
mkdir /var/www/html/salad
```

### Now switch to the created directory.
```bash
cd /var/www/html/salad
```
### install SaladStack Framework
```bash
composer create-project salad-stack/framework .
```
### Once the installation is completed, give the directory permission to the Apache user:
```bash
sudo chown -R www-data:www-data /var/www/html/salad/
```
```bash
sudo chmod -R 755 /var/www/html/salad/
```
### Create a Virtual host configuration file.
Most of the time we either use Forums on sub-domain or Sub-folder, hence for that create a virtual host configuration file.
```bash
sudo nano /etc/apache2/sites-available/salad.conf
```
Add the following lines:

```bash
<VirtualHost *:80>
  ServerAdmin admin@example.com
  DocumentRoot /var/www/html/salad/public
  ServerName salad.example.com

  DirectoryIndex index.php

  <Directory /var/www/html/salad/public/>
    Options +FollowSymLinks
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>

  ErrorLog /var/log/apache2/salad-error_log
  CustomLog /var/log/apache2/salad-access_log common
</VirtualHost>
```
Replace the domain name example.com as per your domain, if you have, otherwise you still be able to access the forum using the IP address.

Save the file by pressing Ctlr+O, hit the Enter key, and then exit- Ctrl+X.

### Enable the site
```bash
sudo a2ensite flarum
```
### Now enable the rewrite module for Apache:
```bash
sudo a2enmod rewrite
```
### Restart the service:
```bash
sudo systemctl restart apache2
```
