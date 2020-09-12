FROM node as builder
RUN git clone https://github.com/yes-soft-de/smarty.git
RUN cd smarty
WORKDIR site
FROM wordpress 
COPY --from=builder ./smarty/site/uplouds /var/www/html/wp-content/uplouds
COPY --from=builder ./smarty/site/theme /var/www/html/wp-content/themes
COPY --from=builder ./smarty/site/plugins /var/www/html/wp-content/plugins
