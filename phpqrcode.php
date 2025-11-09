<?php
// Simple QR code URL generator using external service
function generateQRCode($text, $size = 200) {
    $url = "https://api.qrserver.com/v1/create-qr-code/?size=" . $size . "x" . $size . "&data=" . urlencode($text);
    return $url;
}
?>