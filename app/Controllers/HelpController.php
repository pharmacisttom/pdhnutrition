<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class HelpController {
    public function manual(): void {
        AuthMiddleware::check();
        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/help/manual.php';
    }
}
