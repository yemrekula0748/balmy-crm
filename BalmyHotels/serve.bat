@echo off
REM PHP artisan serve - 32MB upload limiti ile
php -d upload_max_filesize=32M -d post_max_size=64M -d memory_limit=256M artisan serve
