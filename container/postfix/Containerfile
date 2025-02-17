FROM alpine:latest

LABEL maintainer="nguyennv@iwayvietnam.com"

RUN apk -U upgrade && \
    apk add --no-cache postfix postfix-pcre postfix-stone postfix-ldap postfix-mysql && \
    apk add --no-cache rsyslog bash && \
    rm -rf /var/cache/apk/*

VOLUME ["/var/spool/postfix", "/var/mail", "/var/log"]

EXPOSE 25 10025

COPY ./entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
