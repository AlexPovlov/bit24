FROM nginx:1.21.6-alpine

ADD docker/conf/nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/bit24