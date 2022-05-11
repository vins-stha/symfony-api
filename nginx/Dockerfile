FROM nginx:alpine
COPY ./nginx/nginx.conf /etc/nginx/
COPY ./nginx/symfony.conf /etc/nginx/conf.d/
COPY ./nginx/upstream.conf /etc/nginx/conf.d/upstream.conf
WORKDIR /var/www/symfony
COPY . .