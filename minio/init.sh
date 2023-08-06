#!/bin/bash

# Check variables
: "${MINIO_ACCESS_KEY:?}"
: "${MINIO_SECRET_KEY:?}"
: "${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY:?}"
: "${GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER:?}"

: "${GK_BUCKET_NAME_STATPIC:=statpic}"
: "${GK_BUCKET_NAME_GEOKRETY_AVATARS:=gk-avatars}"
: "${GK_BUCKET_NAME_USERS_AVATARS:=users-avatars}"
: "${GK_BUCKET_NAME_MOVES_PICTURES:=moves-pictures}"

: "${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER:=pictures-processor-downloader}"
: "${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER:=pictures-processor-uploader}"

: "${GK_WEBSITE_HOST:=nginx}"
: "${GK_WEBSITE_PORT:=80}"
: "${GK_PICTURES_DOWNLOADER_HOST:=pictures-downloader}"
: "${GK_PICTURES_DOWNLOADER_PORT:=80}"
: "${GK_PICTURES_UPLOADER_HOST:=pictures-uploader}"
: "${GK_PICTURES_UPLOADER_PORT:=80}"
: "${GK_MINIO_HOST:=minio}"
: "${GK_MINIO_PORT:=9000}"

# Define Minio access
export MC_HOST_minio=http://${MINIO_ACCESS_KEY}:${MINIO_SECRET_KEY}@${GK_MINIO_HOST}:${GK_MINIO_PORT}
echo DEBUG $MC_HOST_minio

# Statpic
mc mb "minio/${GK_BUCKET_NAME_STATPIC}"
mc mb "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}"
mc mb "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}-thumbnails"
mc mb "minio/${GK_BUCKET_NAME_USERS_AVATARS}"
mc mb "minio/${GK_BUCKET_NAME_USERS_AVATARS}-thumbnails"
mc mb "minio/${GK_BUCKET_NAME_MOVES_PICTURES}"
mc mb "minio/${GK_BUCKET_NAME_MOVES_PICTURES}-thumbnails"
mc mb "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}"
mc mb "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}"


mc anonymous set public "minio/${GK_BUCKET_NAME_STATPIC}"
mc anonymous set public "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}"
mc anonymous set public "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}-thumbnails"
mc anonymous set public "minio/${GK_BUCKET_NAME_USERS_AVATARS}"
mc anonymous set public "minio/${GK_BUCKET_NAME_USERS_AVATARS}-thumbnails"
mc anonymous set public "minio/${GK_BUCKET_NAME_MOVES_PICTURES}"
mc anonymous set public "minio/${GK_BUCKET_NAME_MOVES_PICTURES}-thumbnails"

mc admin user add minio "${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY}" "${GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY}"

mc admin policy create minio pictures-processor-downloader_write minio/pictures-processor-downloader_write.json
mc admin policy attach minio pictures-processor-downloader_write --user "${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY}"

MINIO_RESTART=false

echo "Wait for webserver to be up"
for i in $(seq 60); do httping -sGc1 -o 200,302 http://${GK_WEBSITE_HOST}:${GK_WEBSITE_PORT}/health && echo OK && break ; sleep 1 ; done
echo "webserver up"
{ mc admin config set minio notify_webhook:picture-uploaded queue_limit="0" endpoint="http://${GK_WEBSITE_HOST}:${GK_WEBSITE_PORT}/s3/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED}" && MINIO_RESTART=true; }

echo "Wait for pictures-processor-downloader to be up"
for i in $(seq 60); do httping -sc1 -o 200,302 http://${GK_PICTURES_DOWNLOADER_HOST}:${GK_PICTURES_DOWNLOADER_PORT}/file-uploaded && echo OK && break ; sleep 1 ; done
echo "webserver pictures-processor-downloader"
{ mc admin config set minio notify_webhook:pictures-processor-downloader-uploaded queue_limit="0" endpoint="http://${GK_PICTURES_DOWNLOADER_HOST}:${GK_PICTURES_DOWNLOADER_PORT}/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER}" && MINIO_RESTART=true; }

echo "Wait for pictures-processor-uploader to be up"
for i in $(seq 60); do httping -sc1 -o 200,302 http://${GK_PICTURES_UPLOADER_HOST}:${GK_PICTURES_UPLOADER_PORT}/file-uploaded && echo OK && break ; sleep 1 ; done
echo "webserver pictures-processor-uploader"
{ mc admin config set minio notify_webhook:pictures-processor-uploader-uploaded queue_limit="0" endpoint="http://${GK_PICTURES_UPLOADER_HOST}:${GK_PICTURES_UPLOADER_PORT}/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER}" && MINIO_RESTART=true; }

$MINIO_RESTART && mc admin service restart minio

# DEBUG
mc admin config get minio notify_webhook:picture-uploaded
mc admin config get minio notify_webhook:pictures-processor-downloader-uploaded
mc admin config get minio notify_webhook:pictures-processor-uploader-uploaded


[[ $(mc event list "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}" arn:minio:sqs::picture-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}" arn:minio:sqs::picture-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_USERS_AVATARS}" arn:minio:sqs::picture-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_USERS_AVATARS}" arn:minio:sqs::picture-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_MOVES_PICTURES}" arn:minio:sqs::picture-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_MOVES_PICTURES}" arn:minio:sqs::picture-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}" arn:minio:sqs::pictures-processor-downloader-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}" arn:minio:sqs::pictures-processor-downloader-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}" arn:minio:sqs::pictures-processor-uploader-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}" arn:minio:sqs::pictures-processor-uploader-uploaded:webhook --event put
