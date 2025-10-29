<?php
// =============================================
// backend/upload_media.php
// =============================================
// Handles upload of images and videos, generates thumbnails for videos,
// and optimizes video files for web streaming using FFmpeg faststart.
//
// Requires ffmpeg installed and available in PATH.

function handle_media_upload(
    string $field,
    string $imagesDirFs,
    string $videosDirFs,
    string $thumbDirFs,
    string $imagesBaseUrl,
    string $videosBaseUrl,
    string $thumbBaseUrl,
    ?string &$error
): ?array {
    $error = null;

    if (!isset($_FILES[$field]) || empty($_FILES[$field]['name'])) {
        return null;
    }

    if (!isset($_FILES[$field]['error']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again.';
        return null;
    }

    $tmp = $_FILES[$field]['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        $error = 'Invalid upload.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = $finfo ? finfo_file($finfo, $tmp) : '';
    if ($finfo) finfo_close($finfo);

    $allowed = [
        'image/jpeg' => ['ext' => 'jpg', 'kind' => 'image'],
        'image/png'  => ['ext' => 'png', 'kind' => 'image'],
        'image/gif'  => ['ext' => 'gif', 'kind' => 'image'],
        'image/webp' => ['ext' => 'webp', 'kind' => 'image'],
        'video/mp4'  => ['ext' => 'mp4', 'kind' => 'video'],
        'video/quicktime' => ['ext' => 'mov', 'kind' => 'video'],
        'video/webm' => ['ext' => 'webm', 'kind' => 'video'],
    ];

    if (!isset($allowed[$mime])) {
        $error = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP, MP4, MOV, WEBM.';
        return null;
    }

    $kind = $allowed[$mime]['kind'];
    $ext  = $allowed[$mime]['ext'];

    if ($kind === 'image' && @getimagesize($tmp) === false) {
        $error = 'The selected file is not a valid image.';
        return null;
    }

    $dirFs   = ($kind === 'image') ? rtrim($imagesDirFs, '/') : rtrim($videosDirFs, '/');
    $baseUrl = ($kind === 'image') ? rtrim($imagesBaseUrl, '/') : rtrim($videosBaseUrl, '/');

    if (!is_dir($dirFs)) @mkdir($dirFs, 0777, true);
    if (!is_dir($thumbDirFs)) @mkdir($thumbDirFs, 0777, true);

    try {
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
    } catch (Throwable $e) {
        $error = 'Could not generate a safe filename.';
        return null;
    }

    $destFs = $dirFs . '/' . $name;
    if (!@move_uploaded_file($tmp, $destFs)) {
        $error = 'Failed to save the uploaded file.';
        return null;
    }

    $thumbUrl = null;

    // ===============================
    // VIDEO HANDLING
    // ===============================
    if ($kind === 'video') {

        // Optimize video for fast start
        $optimizedPath = $dirFs . '/optimized_' . $name;
        $faststartCmd = sprintf(
            'ffmpeg -y -i %s -c copy -movflags +faststart %s 2>&1',
            escapeshellarg($destFs),
            escapeshellarg($optimizedPath)
        );

        @exec($faststartCmd, $fastOut, $fastRet);
        if ($fastRet === 0 && file_exists($optimizedPath)) {
            @unlink($destFs); // remove original
            @rename($optimizedPath, $destFs);
        }

        // Generate thumbnail
        $thumbName = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
        $thumbPath = $thumbDirFs . '/' . $thumbName;

        $thumbCmd = sprintf(
            'ffmpeg -y -i %s -ss 00:00:02 -vframes 1 -vf "scale=640:-1" %s 2>&1',
            escapeshellarg($destFs),
            escapeshellarg($thumbPath)
        );

        @exec($thumbCmd, $out, $ret);
        if ($ret === 0 && file_exists($thumbPath)) {
            $thumbUrl = rtrim($thumbBaseUrl, '/') . '/' . $thumbName;
        }
    }

    return [
        'url'           => $baseUrl . '/' . $name,
        'thumbnail_url' => $thumbUrl,
        'type'          => $kind,
    ];
}
?>
