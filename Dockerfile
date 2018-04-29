FROM daocloud.io/php:7.1-cli
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp

ENV SWOOLE_VERSION 2.1.2

RUN { \
    echo deb http://mirrors.aliyun.com/debian/ jessie main non-free contrib; \
    echo deb http://mirrors.aliyun.com/debian/ jessie-proposed-updates main non-free contrib; \
    echo deb-src http://mirrors.aliyun.com/debian/ jessie main non-free contrib; \
    echo deb-src http://mirrors.aliyun.com/debian/ jessie-proposed-updates main non-free contrib; \
    } | tee /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y git curl libssl-dev

RUN git clone https://github.com/redis/hiredis /tmp/hiredis \
    && cd /tmp/hiredis \
    && make \
    && make install \
    && ldconfig

RUN curl -fsSL http://pecl.php.net/get/swoole-$SWOOLE_VERSION.tgz -o swoole.tgz \
    && mkdir /tmp/swoole \
    && tar -zxf swoole.tgz  -C /tmp/swoole \
    && cd /tmp/swoole/swoole-$SWOOLE_VERSION \
    && phpize \
    && ./configure --enable-coroutine --enable-async-redis --enable-openssl \
    && make \
    && make install \
    && docker-php-ext-enable swoole


# docker build -t php-swoole .

# docker run -it --rm --name php-swoole-cli php-swoole
