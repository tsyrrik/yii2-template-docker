#!/bin/sh
set -eu

log() {
  printf '[mysql-backup] %s\n' "$*"
}

require_env() {
  var_name=$1
  eval "value=\${$var_name-}"
  if [ -z "${value}" ]; then
    log "Missing required env: ${var_name}"
    exit 1
  fi
}

is_true() {
  case "${1:-}" in
    1|true|TRUE|yes|YES|on|ON) return 0 ;;
    *) return 1 ;;
  esac
}

write_rclone_config() {
  config_path=$1
  force_path_style=${MYSQL_BACKUP_S3_PATH_STYLE:-1}

  cat > "${config_path}" <<EOF
[backup-remote]
type = s3
provider = Other
access_key_id = ${MYSQL_BACKUP_S3_ACCESS_KEY}
secret_access_key = ${MYSQL_BACKUP_S3_SECRET_KEY}
region = ${MYSQL_BACKUP_S3_REGION}
endpoint = ${MYSQL_BACKUP_S3_ENDPOINT}
acl = private
force_path_style = ${force_path_style}
no_check_bucket = true
EOF
}

upload_backup() {
  dump_path=$1
  checksum_path=$2
  bucket=$3
  remote_dir=$4

  require_env MYSQL_BACKUP_S3_ENDPOINT
  require_env MYSQL_BACKUP_S3_REGION
  require_env MYSQL_BACKUP_S3_BUCKET
  require_env MYSQL_BACKUP_S3_ACCESS_KEY
  require_env MYSQL_BACKUP_S3_SECRET_KEY

  config_path=$(mktemp /tmp/mysql-backup-rclone.XXXXXX)
  trap 'rm -f "${config_path}"' EXIT INT TERM
  write_rclone_config "${config_path}"

  remote_dump="backup-remote:${bucket}/${remote_dir}/$(basename "${dump_path}")"
  remote_checksum="backup-remote:${bucket}/${remote_dir}/$(basename "${checksum_path}")"

  log "Uploading dump to ${bucket}/${remote_dir}"
  rclone copyto --config "${config_path}" "${dump_path}" "${remote_dump}"
  rclone copyto --config "${config_path}" "${checksum_path}" "${remote_checksum}"

  s3_keep_days=${MYSQL_BACKUP_S3_KEEP_DAYS:-${MYSQL_BACKUP_KEEP_DAYS:-14}}
  if [ "${s3_keep_days}" -gt 0 ] 2>/dev/null; then
    rclone delete \
      --config "${config_path}" \
      --min-age "${s3_keep_days}d" \
      "backup-remote:${bucket}/${remote_dir}" \
      --include "*.sql.gz" \
      --include "*.sha256"
    rclone rmdirs --config "${config_path}" "backup-remote:${bucket}/${remote_dir}" --leave-root || true
  fi

  rm -f "${config_path}"
  trap - EXIT INT TERM
}

require_env DB_NAME
require_env DB_USER
require_env DB_PASSWORD

MYSQL_HOST=${MYSQL_HOST:-db}
MYSQL_PORT=${MYSQL_PORT:-3306}
MYSQL_BACKUP_DIR=${MYSQL_BACKUP_DIR:-/backups}
MYSQL_BACKUP_KEEP_DAYS=${MYSQL_BACKUP_KEEP_DAYS:-14}
MYSQL_BACKUP_PREFIX=${MYSQL_BACKUP_PREFIX:-mysql}
MYSQL_BACKUP_S3_PREFIX=${MYSQL_BACKUP_S3_PREFIX:-${MYSQL_BACKUP_PREFIX%/}}
MYSQL_BACKUP_NAME=${MYSQL_BACKUP_NAME:-${DB_NAME}}

backup_dir="${MYSQL_BACKUP_DIR%/}/${MYSQL_BACKUP_PREFIX%/}/${MYSQL_BACKUP_NAME}"
mkdir -p "${backup_dir}"
umask 077

timestamp=$(date -u +"%Y%m%dT%H%M%SZ")
dump_filename="${MYSQL_BACKUP_NAME}_${timestamp}.sql.gz"
checksum_filename="${dump_filename}.sha256"
temp_dump="${backup_dir}/${dump_filename}.part"
final_dump="${backup_dir}/${dump_filename}"
final_checksum="${backup_dir}/${checksum_filename}"

trap 'rm -f "${temp_dump}"' EXIT INT TERM

log "Starting backup for database ${DB_NAME}"
mysqldump \
  --host="${MYSQL_HOST}" \
  --port="${MYSQL_PORT}" \
  --user="${DB_USER}" \
  --password="${DB_PASSWORD}" \
  --single-transaction \
  --routines \
  --triggers \
  "${DB_NAME}" | gzip > "${temp_dump}"

mv "${temp_dump}" "${final_dump}"
sha256sum "${final_dump}" > "${final_checksum}"

if is_true "${MYSQL_BACKUP_S3_ENABLED:-0}"; then
  upload_backup \
    "${final_dump}" \
    "${final_checksum}" \
    "${MYSQL_BACKUP_S3_BUCKET}" \
    "${MYSQL_BACKUP_S3_PREFIX%/}/${MYSQL_BACKUP_NAME}"
fi

if [ "${MYSQL_BACKUP_KEEP_DAYS}" -gt 0 ] 2>/dev/null; then
  find "${backup_dir}" -type f -name '*.sql.gz' -mtime +"${MYSQL_BACKUP_KEEP_DAYS}" -delete
  find "${backup_dir}" -type f -name '*.sha256' -mtime +"${MYSQL_BACKUP_KEEP_DAYS}" -delete
fi

trap - EXIT INT TERM
log "Backup completed: ${final_dump}"
