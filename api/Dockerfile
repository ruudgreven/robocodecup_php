FROM ubuntu:14.04
MAINTAINER Ruud Greven <ruud@rgonline.nl>

RUN apt-get update && apt-get -y install supervisor apache2 libapache2-mod-php5 php5 php5-mysql mysql-server mysql-client && apt-get clean

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

RUN /usr/sbin/a2ensite default-ssl
RUN /usr/sbin/a2enmod ssl
RUN /usr/sbin/a2enmod rewrite

# Mount USEB
RUN rm -rf /var/www/html && ln -s /useb/api/www /var/www/html

# Configure apache
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Run and configure database
ADD database.sql /
RUN /etc/init.d/mysql start && echo "CREATE DATABASE useb;" | mysql -u root && mysql -u root useb < /database.sql 

# Configure supervisor
RUN echo "[supervisord]" > /etc/supervisord.conf && \
    echo "nodaemon=true" >> /etc/supervisord.conf && \
    echo "" >> /etc/supervisord.conf && \
    echo "[program:mysqld]" >> /etc/supervisord.conf && \
    echo "command=/usr/bin/mysqld_safe" >> /etc/supervisord.conf && \
    echo "" >> /etc/supervisord.conf && \
    echo "[program:httpd]" >> /etc/supervisord.conf && \
    echo "command=/usr/sbin/apachectl -D FOREGROUND" >> /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord"]
