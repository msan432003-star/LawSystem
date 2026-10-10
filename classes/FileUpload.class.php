<?php
/**
 * classes/FileUpload.class.php
 * Safe handling of uploaded case documents.
 *  - checks upload errors, size, extension AND real MIME type
 *  - stores the file under a random name (original name is never used on disk)
 *
 * Usage:
 *   $up = new FileUpload(UPLOAD_DIR . 'case_files/');
 *   $path = $up->save($_FILES['document']);          // returns 'stored_name.pdf' or null
 *   if ($path === null) { $error = $up->error(); }
 *   else { $db->insert('case_file', ['case_id' => $id, 'file_title' => $title, 'file_path' => $path, ...]); }
 *
 * Form must use:  <form method="post" enctype="multipart/form-data">
 */
class FileUpload
{
    private string $directory;
    private int $maxBytes;
    private array $allowed;      // extension => allowed MIME types
    private string $error = '';
    private string $originalName = '';

    public function __construct(string $directory, int $maxMegabytes = 5, ?array $allowed = null)
    {
        $this->directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        $this->maxBytes  = $maxMegabytes * 1024 * 1024;
        $this->allowed   = $allowed ?? [
            'pdf'  => ['application/pdf'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'doc'  => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        ];
    }

    /**
     * Validate and store one uploaded file.
     * Returns the stored file name (save this in the database), or null on failure.
     */
    public function save(?array $file): ?string
    {
        $this->error = '';

        if ($file === null || !isset($file['error'])) {
            return $this->fail('No file was uploaded.');
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->fail('Please choose a file to upload.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->fail($this->uploadErrorMessage($file['error']));
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return $this->fail('Invalid upload.');
        }
        if ($file['size'] > $this->maxBytes) {
            return $this->fail('File is too large. Maximum size is ' . ($this->maxBytes / 1024 / 1024) . ' MB.');
        }

        // Extension check
        $this->originalName = basename($file['name']);
        $ext = strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
        if (!isset($this->allowed[$ext])) {
            return $this->fail('File type not allowed. Allowed: ' . implode(', ', array_keys($this->allowed)) . '.');
        }

        // Real MIME check (does not trust the browser-supplied type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowed[$ext], true)) {
            return $this->fail('File content does not match its extension.');
        }

        // Make sure the folder exists
        if (!is_dir($this->directory) && !mkdir($this->directory, 0755, true)) {
            return $this->fail('Upload folder could not be created.');
        }

        // Random stored name, original name is discarded
        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $this->directory . $storedName)) {
            return $this->fail('Could not save the uploaded file.');
        }

        return $storedName;
    }

    /** Delete a previously stored file (pass the stored name from the database). */
    public function delete(string $storedName): bool
    {
        $path = $this->directory . basename($storedName);   // basename blocks ../ tricks
        return is_file($path) && unlink($path);
    }

    /** Full path of a stored file, or null if it does not exist (use for downloads). */
    public function path(string $storedName): ?string
    {
        $path = $this->directory . basename($storedName);
        return is_file($path) ? $path : null;
    }

    public function error(): string
    {
        return $this->error;
    }

    /** Original file name the user uploaded (handy for display or the file_title field). */
    public function originalName(): string
    {
        return $this->originalName;
    }

    // ---------- Internals ----------

    private function fail(string $message): ?string
    {
        $this->error = $message;
        return null;
    }

    private function uploadErrorMessage(int $code): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is larger than the server allows.';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded. Please try again.';
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                return 'Server error while uploading. Please try again later.';
            default:
                return 'Upload failed. Please try again.';
        }
    }
}