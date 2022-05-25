FROM php:7.2-apache

ENV DEBIAN_FRONTEND=noninteractive

COPY php.ini "$PHP_INI_DIR/php.ini"

RUN apt update && apt -y upgrade
RUN apt install -y python3 python3-pip \
        ffmpeg \
        wget \
        --no-install-recommends

RUN pip3 install --upgrade pip
RUN pip3 install wheel
RUN pip3 install setuptools 
RUN pip3 install certifi
RUN pip3 install jsonrpc_base
RUN pip3 install yapapi
ARG YAGNA_VERSION=v0.9.1
ARG YAGNA_PACKAGE=golem-requestor-linux-${YAGNA_VERSION}
RUN cd / &&\
    wget -q https://github.com/golemfactory/yagna/releases/download/${YAGNA_VERSION}/$YAGNA_PACKAGE.tar.gz && \
    tar -xf $YAGNA_PACKAGE.tar.gz && \
    rm $YAGNA_PACKAGE.tar.gz

ENV PATH=${PATH}:/$YAGNA_PACKAGE


    
EXPOSE 80/tcp

RUN mkdir /var/www/backend

