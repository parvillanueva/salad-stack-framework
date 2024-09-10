# SaladStack
SaladStack is an open-source PHP framework designed to streamline the process of building dynamic and robust websites. With its user-friendly architecture and modular components, SaladStack provides developers with a flexible toolkit for creating scalable web applications. Its intuitive structure allows for easy customization and rapid development, making it an ideal choice for both beginners and experienced programmers looking to build modern, high-performance websites. Embrace the power of SaladStack to simplify your development workflow and bring your web projects to life with ease.

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

---
# Install SaladStack on Local Machine
### Create a directory in your webroot folder: Here it is ‘salad‘.
```bash
mkdir salad
```

### Now switch to the created directory.
```bash
cd salad
```
### install SaladStack Framework
```bash
composer create-project salad-stack/framework .
```
### Run SaladStack Locally
```bash
cd public && php -S localhost:8000
```

---

# Install SaladStack on Linux
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
