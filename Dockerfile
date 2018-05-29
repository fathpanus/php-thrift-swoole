FROM daocloud.io/library/php:7.1-cli-alpine


ENV TIMEZONE Asia/Shanghai

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories && \
  apk update && \
  apk add --no-cache tzdata curl && \
  cp /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && \
  echo "${TIMEZONE}" > /etc/timezone && \
  apk --update --repository=http://dl-4.alpinelinux.org/alpine/edge/testing add \
  #--virtual .php-deps $PHPIZE_DEPS \
  #libmcrypt-dev \
  hiredis-dev \
  tzdata

ENV SWOOLE_VERSION 2.2.0
ENV REDIS_VERSION 3.1.6
ENV MONGODB_VERSION 1.4.0

RUN curl -fsSL http://pecl.php.net/get/swoole-${SWOOLE_VERSION}.tgz -o swoole.tar.gz \
    && ( \
    mkdir /tmp/swoole \
    && tar -xf swoole.tar.gz -C /tmp/swoole --strip-components=1 \
    && rm -f swoole.tar.gz \
    && docker-php-ext-configure /tmp/swoole --enable-async-redis --enable-coroutine \
    #&& docker-php-ext-install /tmp/swoole \
    #&& rm -rf /tmp/swoole \
    ) \
    && curl -fsSL http://pecl.php.net/get/redis-${REDIS_VERSION}.tgz -o redis.tar.gz \
    && ( \ 
    mkdir /tmp/redis \
    && tar -xf redis.tar.gz -C /tmp/redis --strip-components=1 \
    && rm -f redis.tar.gz \
    #&& docker-php-ext-install /tmp/redis \
    #&& rm -rf /tmp/redis \
    ) \
    #&& curl -fsSL http://pecl.php.net/get/mongodb-${MONGODB_VERSION}.tgz -o mongodb.tar.gz \
    #&& ( \
    #mkdir /tmp/mongodb \
    #&& tar -xf mongodb.tar.gz -C /tmp/mongodb --strip-components=1 \
    #&& rm -f mongodb.tar.gz \
    #&& docker-php-ext-install /tmp/mongodb \
    #&& rm -rf /tmp/mongodb  \
    #) \
    && docker-php-ext-install mysqli pdo pdo_mysql bcmath pcntl mbstring /tmp/redis /tmp/swoole

 RUN rm -rf /var/cache/apk/*  \
    && rm -rf /tmp/*
