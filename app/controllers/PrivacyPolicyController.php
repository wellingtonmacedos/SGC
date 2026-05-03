<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class PrivacyPolicyController extends Controller
{
    public function index(): void
    {
        $this->render('privacy_policy/index', [
            'pageTitle' => 'Política de Privacidade',
            'version' => defined('PRIVACY_POLICY_VERSION') ? PRIVACY_POLICY_VERSION : null,
        ]);
    }
}

