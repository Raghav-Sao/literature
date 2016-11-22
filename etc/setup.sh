#!/bin/bash

cd ~
sudo apt-get update -y
sudo apt-get install build-essential zip unzip -y

sudo apt-get install php7.0 php7.0-fpm php7.0-curl php7.0-xml -y

sudo apt-get install nginx -y

sudo apt-get install redis-server -y


git clone git@github.com:Raghav-Sao/literature.git
cd literature
./composer.phar install --no-interaction

cp etc/nginx.conf /etc/nginx/sites-available/literatre
sudo ln -s /etc/nginx/sites-available/literature /etc/nginx/sites-enabled/
sudo service nginx reload
