HealthManagerWeb
================

A Symfony project.

* commit styling: https://github.com/slashsBin/styleguide-git-commit-message

### System Requirements and configurations ###

* apache2
* php7.0
* php7.0-mysql
Add at following lines at the end of the file `/etc/mysql/my.cnf`:
```
[mysqld]
sql-mode="STRICT_ALL_TABLES"
```
* php7.0-mbstring
* php7.0-intl (reboot required)
