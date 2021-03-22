<?php

namespace GeoKrety\Service;

use Exception;

class File {
    /**
     * Creates a random unique temporary directory, with specified parameters,
     * that does not already exist (like tempnam(), but for dirs).
     *
     * Created dir will begin with the specified prefix, followed by random
     * numbers.
     *
     * @see https://php.net/manual/en/function.tempnam.php
     * @see https://stackoverflow.com/a/30010928/944936
     *
     * @param string|null $dir         Base directory under which to create temp dir.
     *                                 If null, the default system temp dir (sys_get_temp_dir()) will be
     *                                 used.
     * @param string      $prefix      string with which to prefix created dirs
     * @param int         $mode        Octal file permission mask for the newly-created dir.
     *                                 Should begin with a 0.
     * @param int         $maxAttempts maximum attempts before giving up (to prevent
     *                                 endless loops)
     *
     * @return string|bool full path to newly-created dir, or false on failure
     */
    public static function tmpdir($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000) {
        /* Use the system temp dir by default. */
        if (is_null($dir)) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            !mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );

        return $path;
    }

    /**
     * Copies the file from $url to $output, supports both file paths and urls.
     *
     * @see https://stackoverflow.com/a/3406181/944936
     *
     * @param $url string path to source file or URL
     * @param $output string path to output file or stream
     *
     * @throws Exception if something goes wrong
     */
    public static function download(string $url, string $output) {
        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new Exception($message);
            }
        );

        try {
            $readableStream = fopen($url, 'rb');
        } finally {
            restore_error_handler();
        }
        $writableStream = fopen($output, 'wb');
        if (!$writableStream) {
            throw new Exception(sprintf('Failed to create file: %s', $url));
        }
        stream_copy_to_stream($readableStream, $writableStream);
        fclose($writableStream);
    }

    /**
     * Extract a tar file compressed or not.
     *
     * @param string $path The file to extract
     * @param string $dest The destination directory
     *
     * @throws Exception
     */
    public static function extract_tar(string $path, string $dest) {
        $result_code = null;
        $output = null;
        exec(sprintf('tar -C %s -xf %s', $dest, $path), $output, $result_code);
        if ($result_code === 0) {
            return;
        }
        throw new Exception(sprintf('Failed to extract file: %s', $output));
    }

    /**
     * Deletes whole folder tree including files inside.
     *
     * @param $directory string path to delete
     *
     * @return void
     *
     * @throws Exception
     */
    public static function deleteTree(string $directory) {
        if (empty($directory) or !$directory) {
            return;
        }
        $directory = realpath($directory);
        $pathLength = strlen($directory);
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $file_ = realpath("$directory/$file");
            if (strncmp($file_, $directory, $pathLength) !== 0) {
                throw new Exception("Deleting file '$file' would have gone out of base directory ('$directory') => '$file_'.");
            }
            (is_dir($file_)) ? self::deleteTree($file_) : unlink($file_);
        }
        rmdir($directory);
    }

    /**
     * Extract a zip file.
     *
     * @param string $path The file to extract
     * @param string $dest The destination directory
     *
     * @throws Exception
     */
    public static function extract_zip(string $path, string $dest) {
        $result_code = null;
        $output = null;
        exec(sprintf('unzip -d %s %s', $dest, $path), $output, $result_code);
        if ($result_code === 0) {
            return;
        }
        throw new Exception(sprintf('Failed to unzip file: %s', $output));
    }
}
