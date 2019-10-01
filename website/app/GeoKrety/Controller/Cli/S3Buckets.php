<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Service\S3Client;

class S3Buckets {
    public function createStatpic() {
        // This policy sets the bucket to read only
        $policyReadOnly = '{
          "Version": "2012-10-17",
          "Statement": [
            {
              "Action": [
                "s3:GetBucketLocation",
                "s3:ListBucket"
              ],
              "Effect": "Allow",
              "Principal": {
                "AWS": ["*"]
              },
              "Resource": ["arn:aws:s3:::%s"],
              "Sid": ""
            },
            {
              "Action": [
                "s3:GetObject"
              ],
              "Effect": "Allow",
              "Principal": {
                "AWS": ["*"]
              },
              "Resource": ["arn:aws:s3:::%s/*"],
              "Sid": ""
            }
          ]
        }
        ';

        $s3 = S3Client::instance();

        // Create a bucket
        if (!$s3->doesBucketExist(GK_BUCKET_STATPIC_NAME)) {
            $result = $s3->createBucket([
                'Bucket' => GK_BUCKET_STATPIC_NAME,
            ]);
            echo "\e[0;32mStatpic s3 bucket created\e[0m".PHP_EOL;
            // Configure the policy
            $s3->putBucketPolicy([
                'Bucket' => GK_BUCKET_STATPIC_NAME,
                'Policy' => sprintf($policyReadOnly, GK_BUCKET_STATPIC_NAME, GK_BUCKET_STATPIC_NAME),
            ]);
            echo "\e[0;32mStatpic s3 bucket policy: public\e[0m".PHP_EOL;
        }
    }
}
