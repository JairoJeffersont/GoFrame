<?php

namespace GoFrame\Core\Helpers;

/**
 * FileUploader Class
 * 
 * The `FileUploader` class is responsible for managing file uploads, including:
 * - File type and size validation.
 * - Storing files in a specific directory.
 * - Deleting files from the server.
 * 
 * It also provides error checking during the upload process and security features such as MIME type validation 
 * and automatic creation of destination directories when needed.
 * 
 * @package GoFrame
 */
class FileUploader {

    /**
     * Uploads a file to a specified directory.
     *
     * This method handles uploading a file sent via a form, checking if the file type is allowed,
     * if the file size is within the specified limit, and if the destination directory exists or can be created.
     * It can also generate a unique file name to avoid conflicts with existing files.
     * 
     * @param string $directory Directory where the file will be stored on the server.
     * 
     * @param array $file File data, usually from the $_FILES array.
     * 
     * @param array $allowedTypes Allowed MIME types for upload. Examples: ['image/jpeg', 'image/png'].
     * 
     * @param int $maxSize Maximum file size in MB. The file cannot exceed this size.
     * 
     * @param bool $uniqueFlag If true, a unique name will be generated for the file to avoid name conflicts.
     * 
     * @return array Returns an associative array with the upload status and a message. On success, it also includes the file path.
     *               - 'status' (string): the operation status, which can be 'success' or different error messages.
     *               - 'message' (string): an explanatory message about the operation status.
     *               - 'file_path' (string, optional): file path on the server, available only when the upload is successful.
     */
    public function uploadFile(string $directory, array $file, array $allowedTypes, int $maxSize, bool $uniqueFlag = true): array {
        // Check if there was an upload error

        // Get the actual file extension and check the MIME type
        $fileMime = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check if the file type is allowed
        if (!in_array($fileMime, $allowedTypes, true)) {
            return ['status' => 'format_not_allowed', 'message' => 'File type not allowed.'];
        }

        // Check if the file size exceeds the limit
        if ($file['size'] > $maxSize * 1024 * 1024) {
            return ['status' => 'max_file_size_exceeded', 'message' => "File exceeds the limit of {$maxSize} MB."];
        }

        // Ensure the directory exists or try to create it
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return ['status' => 'directory_creation_failed', 'message' => 'Failed to create the destination directory.'];
        }

        // Generate a unique name or keep the original name
        $fileName = $uniqueFlag ? uniqid('file_') . '.' . $fileExtension : $file['name'];
        $destination = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Check if the file already exists in the destination directory
        if (file_exists($destination)) {
            return ['status' => 'file_already_exists', 'message' => 'File already exists in the directory.'];
        }

        // Move the file to the destination directory
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['status' => 'move_failed', 'message' => 'Failed to move the file.'];
        }

        // Return success status with the file path
        return ['status' => 'success', 'message' => 'File uploaded successfully.', 'file_path' => str_replace('\\', '/', $destination)];
    }

    /**
     * Deletes a file from the server.
     *
     * This method removes a file from the server based on the provided path. It checks if the file exists 
     * before attempting to delete it and returns the operation status.
     * 
     * @param string $filePath Full path of the file to be deleted, including the directory.
     * 
     * @return array Returns an associative array with the deletion status and an explanatory message.
     *               - 'status' (string): the operation status, which can be 'success' or different error messages.
     *               - 'message' (string): an explanatory message about the operation status.
     */
    public function deleteFile(string $filePath): array {
        // Adjust path to use the correct directory separator
        $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);

        // Check if the file exists in the system
        if (!file_exists($filePath)) {
            return ['status' => 'file_not_found', 'message' => 'File not found.'];
        }

        // Attempt to delete the file
        if (!unlink($filePath)) {
            return ['status' => 'delete_failed', 'message' => 'Failed to delete the file.'];
        }

        // Return success status after deleting the file
        return ['status' => 'success', 'message' => 'File deleted successfully.'];
    }
}