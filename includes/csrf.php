<?php
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_verify() {
    $submitted = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$submitted || !hash_equals((string)($_SESSION['csrf'] ?? ''), (string)$submitted)) {
        http_response_code(419); exit('Session expired. Please refresh and try again.');
    }
}
