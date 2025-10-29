<?php
// Reusable, strict image upload helper
// Usage: $photoUrl = handle_image_upload('photo', __DIR__.'/../uploads/costumes', '/uploads/costumes', $error);

function handle_image_upload(string $field, string $targetDirFs, string $publicBaseUrl, ?string &$error): ?string {
    $error = null;

    if (!isset($_FILES[$field]) || empty($_FILES[$field]['name'])) {
        return null; // nothing uploaded (not an error)
    }

    if (!is_dir($targetDirFs)) {
        @mkdir($targetDirFs, 0777, true);
    }

    // Validate upload didn’t fail at PHP level
    if (!isset($_FILES[$field]['error']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again.';
        return null;
    }

    $tmp = $_FILES[$field]['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        $error = 'Invalid upload.';
        return null;
    }

    // Verify MIME using finfo (server-side) + basic getimagesize
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = $finfo ? finfo_file($finfo, $tmp) : '';
    if ($finfo) finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        $error = 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.';
        return null;
    }

    // Extra sanity: ensure it’s an image
    if (@getimagesize($tmp) === false) {
        $error = 'The selected file is not a valid image.';
        return null;
    }

    // Random 32-hex filename with correct extension
    try {
        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    } catch (Throwable $e) {
        $error = 'Could not generate a safe filename. Please try again.';
        return null;
    }

    $destFs = rtrim($targetDirFs, '/').'/'.$name;
    if (!@move_uploaded_file($tmp, $destFs)) {
        $error = 'Failed to save the uploaded image.';
        return null;
    }

    // Return public URL (for DB)
    return rtrim($publicBaseUrl, '/').'/'.$name;
}
