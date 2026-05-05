-- Monitoring user for Prometheus mysqld-exporter (dev only; prod uses env-configurable password)
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY 'exporter';
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';
FLUSH PRIVILEGES;
