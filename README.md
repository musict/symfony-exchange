1. Create the file `.docker/.env.nginx.local` using `.docker/.env.nginx` as template.
2. Use NGINX_BACKEND_DOMAIN to set server name.
3. Start docker container using `.docker/docker-compose.yml`.
4. Inside the PHP container run composer install.