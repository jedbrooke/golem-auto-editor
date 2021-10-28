FROM php:7.2-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update && apt -y upgrade
RUN apt install -y python3 python3-pip --no-install-recommends
RUN apt install -y ffmpeg --no-install-recommends

EXPOSE 80/tcp

RUN mkdir /backend

