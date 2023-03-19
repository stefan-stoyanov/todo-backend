FROM ubuntu:22.04

RUN apt update && apt -y upgrade \
    && DEBIAN_FRONTEND=noninteractive TZ=Etc/UTC apt install -y tzdata \
    && apt update && apt -y upgrade && apt install -y sudo curl unzip p7zip-full git-lfs \
    && apt -y install php php-pgsql php-curl php-xml php-zip

RUN curl -sS https://get.symfony.com/cli/installer | bash && mv ~/.symfony5/bin/symfony /usr/local/bin/symfony

RUN useradd -m phpuser && passwd -d phpuser && usermod -aG sudo -s /bin/bash phpuser

WORKDIR /var/www
USER phpuser
COPY --chown=phpuser . /var/www
RUN symfony composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist
RUN symfony console cache:clear --env=prod --no-debug
EXPOSE 8000
CMD ["sh", "-c", "APP_ENV=prod symfony server:start --port=8000"]
