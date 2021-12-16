FROM php:7.2-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update && apt -y upgrade
RUN apt install -y python3 python3-pip \
        ffmpeg \
        wget \
        --no-install-recommends

ARG YAGNA_PACKAGE=golem-requestor-linux-v0.9.0
RUN cd / &&\
    wget -q https://github.com/golemfactory/yagna/releases/download/v0.9.0/$YAGNA_PACKAGE.tar.gz && \
    tar -xf $YAGNA_PACKAGE.tar.gz && \
    rm $YAGNA_PACKAGE.tar.gz

ENV PATH=${PATH}:/$YAGNA_PACKAGE

    
EXPOSE 80/tcp

RUN mkdir /backend

