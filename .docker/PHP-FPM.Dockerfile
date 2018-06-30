FROM php:7.2.7-fpm-stretch

RUN apt-get update -y && apt-get install -y libpng-dev curl libcurl4-openssl-dev libxml2-dev libssl-dev --no-install-recommends

RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl mbstring json phar xml zip

RUN php -r "copy('https://getcomposer.org/download/1.6.5/composer.phar', '/usr/local/bin/composer');"

RUN php -r "if (hash_file('SHA256', '/usr/local/bin/composer') === '67bebe9df9866a795078bb2cf21798d8b0214f2e0b2fd81f2e907a8ef0be3434') { echo 'Composer installed'; } else { echo 'Composer corrupt'; unlink('/usr/local/bin/composer');  return 1; } echo PHP_EOL;"

RUN chmod +x /usr/local/bin/composer