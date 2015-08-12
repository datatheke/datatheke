FROM debian:jessie

RUN apt-get update \
    && apt-get install -y curl apache2 php5 php5-mongo \
    && rm -r /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && a2enmod rewrite

COPY . /var/www/datatheke

RUN ln -sf /var/www/datatheke/docker/build/vhost.conf /etc/apache2/sites-enabled/000-default.conf \
    && ln -sf /var/www/datatheke/docker/build/run.sh /usr/local/bin/run.sh \
    && chown -R www-data:www-data /var/www/datatheke/app/cache /var/www/datatheke/app/logs

ENV SYMFONY__MONGODB_SERVER "mongodb://mongodb"
ENV SYMFONY__STORAGE_PATH "/var/www/datatheke/web/uploads"

WORKDIR /var/www/datatheke

VOLUME ['/var/www/datatheke/app/cache', '/var/www/datatheke/app/logs', '/var/www/datatheke/web/uploads']

EXPOSE 80

CMD ["/usr/local/bin/run.sh"]
