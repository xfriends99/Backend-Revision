<?php

namespace App\Services\Cloud;

use Exception;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Illuminate\Support\Facades\Storage;

class AmazonS3Manager
{
    public function upload($key, $bucket, $filepath)
    {
        try {
            // Save file into Amazon S3
            $fd = fopen($filepath, "rb");

            /** @var AwsS3Adapter $adapter */
            $adapter = Storage::disk('s3')->getDriver()->getAdapter();
            $adapter->setBucket($bucket);

            // Store file in Amazon bucket
            Storage::disk('s3')->put($key, $fd);
            fclose($fd);

            // Get object Url
            $client = $adapter->getClient();
            $expiry = "+7 days";
            $command = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
            $request = $client->createPresignedRequest($command, $expiry);
            $url = (string)$request->getUri();

            return $url;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());
            return null;
        }
    }

    public function resourceUrl($bucket, $filename)
    {
        try {
            // Get object Url
            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
            $expiry = "+7 days";

            $command = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $filename
            ]);
            $request = $client->createPresignedRequest($command, $expiry);

            $url = (string)$request->getUri();

            return $url;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return null;
        }

    }
}