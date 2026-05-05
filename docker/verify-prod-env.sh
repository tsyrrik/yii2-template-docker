#!/bin/sh
set -eu

mode=${1:-prod}
failed=0

require_secret() {
  var_name=$1
  insecure_default=$2
  eval "value=\${$var_name-}"

  if [ -z "${value}" ]; then
    echo "Missing required production env: ${var_name}" >&2
    failed=1
    return
  fi

  case "${value}" in
    "${insecure_default}"|change-me-in-.env.local)
      echo "Refusing production deploy: ${var_name} still uses a committed placeholder." >&2
      failed=1
      ;;
  esac
}

is_true() {
  case "${1:-}" in
    1|true|TRUE|yes|YES|on|ON) return 0 ;;
    *) return 1 ;;
  esac
}

require_if_enabled() {
  enabled_var=$1
  required_var=$2
  eval "enabled_value=\${$enabled_var-}"
  if is_true "${enabled_value}"; then
    require_secret "${required_var}" ""
  fi
}

case "${mode}" in
  prod)
    require_secret DB_PASSWORD app
    require_secret RABBITMQ_PASSWORD app
    require_secret APP_SECRET app
    require_if_enabled MYSQL_BACKUP_S3_ENABLED MYSQL_BACKUP_S3_ENDPOINT
    require_if_enabled MYSQL_BACKUP_S3_ENABLED MYSQL_BACKUP_S3_REGION
    require_if_enabled MYSQL_BACKUP_S3_ENABLED MYSQL_BACKUP_S3_BUCKET
    require_if_enabled MYSQL_BACKUP_S3_ENABLED MYSQL_BACKUP_S3_ACCESS_KEY
    require_if_enabled MYSQL_BACKUP_S3_ENABLED MYSQL_BACKUP_S3_SECRET_KEY
    ;;
  monitoring)
    require_secret APP_GRAFANA_ADMIN_PASSWORD admin
    ;;
  *)
    echo "Unknown verification mode: ${mode}" >&2
    exit 1
    ;;
esac

if [ "${failed}" -ne 0 ]; then
  case "${mode}" in
    prod)
      echo "Override secrets in .env.local and rerun make up-prod." >&2
      ;;
    monitoring)
      echo "Override secrets in .env.local and rerun make up-monitoring." >&2
      ;;
  esac
  exit 1
fi
