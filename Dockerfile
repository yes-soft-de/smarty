FROM node as builder
RUN git clone https://github.com/yes-soft-de/smarty.git
RUN cd smarty
WORKDIR site
FROM wordpress:5.4.2
COPY --from=builder ./smarty/site/uploads /var/www/html/wp-content/uploads
COPY --from=builder ./smarty/site/theme /var/www/html/wp-content/themes
COPY --from=builder ./smarty/site/plugins /var/www/html/wp-content/plugins
RUN /bin/bash -c 'ls -la /var/www/html/wp-content/themes; chmod -R 777 /var/www/html/wp-content/themes; ls -la /var/www/html/wp-content/themes'


