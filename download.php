<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filepath = __DIR__ . '/qrcodes/' . $file;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        echo "File not found: " . htmlspecialchars($file);
    }
} else {
    echo "No file specified.";
}
?>
