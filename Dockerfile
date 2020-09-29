FROM node as builder
RUN git clone https://github.com/yes-soft-de/smarty.git
RUN cd smarty
WORKDIR site
FROM wordpress:5.4.2
COPY --chown=www-data:www-data --from=builder ./smarty/site/uploads /var/www/html/wp-content/uploads
COPY --chown=www-data:www-data --from=builder ./smarty/site/theme /var/www/html/wp-content/themes
COPY --chown=www-data:www-data --from=builder ./smarty/site/plugins /var/www/html/wp-content/plugins



