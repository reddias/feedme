## Feedme project

#### Requirements
`PHP 8.1` `Nginx` `MySQL` `Composer` `Git`

---
#### Deployment script

```bash
composer install
```
 
Copy .env.example to .env and configure your database settings

```bash
php artisan key:generate
php artisan migrate
php artisan jwt:secret
php artisan config:clear
php artisan db:seed
php artisan storage:link
```

---
