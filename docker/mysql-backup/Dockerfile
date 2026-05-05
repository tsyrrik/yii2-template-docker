FROM alpine:3.21

RUN apk add --no-cache \
    mysql-client \
    rclone \
    tzdata

COPY docker/mysql-backup/backup.sh /usr/local/bin/mysql-backup.sh
COPY docker/mysql-backup/entrypoint.sh /usr/local/bin/mysql-backup-entrypoint.sh

RUN chmod 700 /usr/local/bin/mysql-backup.sh /usr/local/bin/mysql-backup-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/mysql-backup-entrypoint.sh"]
CMD ["crond"]
