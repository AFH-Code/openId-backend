# Utiliser l'image de base Ubuntu 22.04
FROM ubuntu:22.04

#Not Interactive Intallation
ENV DEBIAN_FRONTEND noninteractive
RUN apt update && apt install -y tcl

# Mettre à jour les packages et installer Apache
RUN apt-get update && apt-get install -y apache2

#Configuration Error AH00558: Could not reliably determine the server's fully qualified domain name
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Installer PHP 7.4 et les extensions PHP nécessaires

# Install dependencies
# Install dependencies software-properties-common  to manage multi php version
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl

RUN add-apt-repository ppa:ondrej/php

RUN apt-get update && apt-get install -y php7.4

#Install PHP Extension
RUN apt-get update && apt-get install -y php7.4-mysql \
    php7.4-curl \
    php7.4-fpm \
    php7.4-cli \
    php7.4-common \
    php7.4-curl \
    php7.4-zip \
    php7.4-gd \
    php7.4-xml \
    php7.4-bcmath \
    php7.4-pdo \
    php7.4-intl
#RUN apt-get update && apt-get install php7.4-{cli,common,curl,zip,gd,mysql,xml,mbstring,json,curl, xml, bcmath,pdo, fpm,intl}

# Installer le module PHP pour Apache
RUN apt-get install -y libapache2-mod-php7.4

# Activer le module proxy_fcgi pour transmettre les requêtes à PHP-FPM
RUN a2enmod proxy_fcgi

# Activer la configuration de PHP-FPM pour Apache
RUN a2enconf php7.4-fpm
# correction

# RUN apt-get update && apt-get install -y pdo pdo-mysql

#Install programme sudo
RUN apt-get install sudo

RUN sudo apt-get update

#The system does not support systemd or is not booted with systemd as the init system.
RUN sudo apt-get install -yqq daemonize dbus-user-session fontconfig
RUN sudo daemonize /usr/bin/unshare --fork --pid --mount-proc /lib/systemd/systemd --system-unit=basic.target \
     exec sudo nsenter -t $(pidof systemd) -a su - $LOGNAME

# Redémarrer Apache pour appliquer les modifications
RUN sudo service apache2 restart

#Install Composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

#Install Nano
RUN apt-get update \
    && apt-get install -y nano
    
# Exposer le port 80
EXPOSE 80

# Commande pour démarrer Apache
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

WORKDIR /var/www/