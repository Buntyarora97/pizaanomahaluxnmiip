<?php
require_once __DIR__ . '/includes/config.php';
unset($_SESSION['customer']);
flash('You have been logged out.');
redirect('index.php');
