#!/bin/bash

# Check variables
: "${GK_MINIO_ACCESS_KEY:?}"
: "${GK_MINIO_SECRET_KEY:?}"
: "${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY:?}"
: "${GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER:?}"
: "${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER:?}"

: "${GK_BUCKET_NAME_STATPIC:=statpic}"
: "${GK_BUCKET_NAME_GEOKRETY_AVATARS:=gk-avatars}"
: "${GK_BUCKET_NAME_USERS_AVATARS:=users-avatars}"

: "${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER:=pictures-processor-downloader}"
: "${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER:=pictures-processor-uploader}"


# Define Minio access
export MC_HOST_minio=http://${GK_MINIO_ACCESS_KEY}:${GK_MINIO_SECRET_KEY}@minio:9000


# Statipic
mc mb "minio/${GK_BUCKET_NAME_STATPIC}"
mc mb "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}"
mc mb "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}-thumbnails"
mc mb "minio/${GK_BUCKET_NAME_USERS_AVATARS}"
mc mb "minio/${GK_BUCKET_NAME_USERS_AVATARS}-thumbnails"
mc mb "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}"
mc mb "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}"


mc policy set public "minio/${GK_BUCKET_NAME_STATPIC}"
mc policy set public "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}"
mc policy set public "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}-thumbnails"
mc policy set public "minio/${GK_BUCKET_NAME_USERS_AVATARS}"
mc policy set public "minio/${GK_BUCKET_NAME_USERS_AVATARS}-thumbnails"

mc admin user add minio "${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY}" "${GK_MINIO_PICTURES_PROCESSOR_MINIO_SECRET_KEY}"

mc admin policy add minio pictures-processor-downloader_write minio/pictures-processor-downloader_write.json
mc admin policy set minio pictures-processor-downloader_write user="${GK_MINIO_PICTURES_PROCESSOR_MINIO_ACCESS_KEY}"

MINIO_RESTART=false
mc admin config get minio notify_webhook:picture-uploaded >/dev/null 2>&1 || { mc admin config set minio notify_webhook:picture-uploaded queue_limit="0" endpoint="http://website/s3/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED}" && MINIO_RESTART=true; }
mc admin config get minio notify_webhook:pictures-processor-downloader-uploaded >/dev/null 2>&1 || { mc admin config set minio notify_webhook:pictures-processor-downloader-uploaded queue_limit="0" endpoint="http://pictures-processor-downloader/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_DOWNLOADER}" && MINIO_RESTART=true; }
mc admin config get minio notify_webhook:pictures-processor-uploader-uploaded >/dev/null 2>&1 || { mc admin config set minio notify_webhook:pictures-processor-uploader-uploaded queue_limit="0" endpoint="http://pictures-processor-uploader/file-uploaded" queue_dir="" auth_token="${GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURES_PROCESSOR_UPLOADER}" && MINIO_RESTART=true; }

$MINIO_RESTART && mc admin service restart minio

[[ $(mc event list "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}" arn:minio:sqs::picture-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_GEOKRETY_AVATARS}" arn:minio:sqs::picture-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_USERS_AVATARS}" arn:minio:sqs::picture-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_USERS_AVATARS}" arn:minio:sqs::picture-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}" arn:minio:sqs::pictures-processor-downloader-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER}" arn:minio:sqs::pictures-processor-downloader-uploaded:webhook --event put
[[ $(mc event list "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}" arn:minio:sqs::pictures-processor-uploader-uploaded:webhook | wc -l) -gt 0 ]] || mc event add "minio/${GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER}" arn:minio:sqs::pictures-processor-uploader-uploaded:webhook --event put
