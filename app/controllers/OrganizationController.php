<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $orgModel = new Organization();
        $settings = $orgModel->getSettings();
        
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $data = [
                'organization_name' => $this->getPostString('organization_name'),
                'cnpj' => $this->getPostString('cnpj'),
                'email' => $this->getPostString('email'),
                'phone' => $this->getPostString('phone'),
                'address' => $this->getPostString('address'),
                'city' => $this->getPostString('city'),
                'state' => $this->getPostString('state'),
                'zip_code' => $this->getPostString('zip_code'),
                'institutional_text' => $this->getPostString('institutional_text'),
            ];

            // Validations
            if ($data['organization_name'] === '') {
                $error = 'O nome da instituição é obrigatório.';
            } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido.';
            } elseif ($data['cnpj'] !== '' && !$this->validateCnpj($data['cnpj'])) {
                $error = 'CNPJ inválido.';
            }

            // Logo Upload
            if (!$error && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['logo'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                if (!in_array($mimeType, $allowedTypes)) {
                    $error = 'Formato de imagem inválido. Use JPG, PNG ou SVG.';
                } elseif ($file['size'] > ORGANIZATION_LOGO_MAX_SIZE) {
                    $error = 'O arquivo excede o tamanho máximo permitido.';
                } else {
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = 'logo_' . time() . '.' . $extension;
                    $targetPath = ORGANIZATION_LOGO_PATH . '/' . $fileName;

                    if (!is_dir(ORGANIZATION_LOGO_PATH)) {
                        mkdir(ORGANIZATION_LOGO_PATH, 0755, true);
                    }

                    // Remove old logo if exists
                    if (!empty($settings['logo'])) {
                        $oldPath = ORGANIZATION_LOGO_PATH . '/' . $settings['logo'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $data['logo'] = $fileName;
                    } else {
                        $error = 'Falha ao salvar o logotipo.';
                    }
                }
            }

            if ($error === '') {
                try {
                    $orgModel->updateSettings($data);
                    $success = 'Configurações atualizadas com sucesso!';
                    $settings = $orgModel->getSettings(); // Refresh
                } catch (\Exception $e) {
                    $error = 'Erro ao salvar: ' . $e->getMessage();
                }
            }
        }

        $this->render('admin/organization', [
            'settings' => $settings,
            'error' => $error,
            'success' => $success
        ]);
    }

    private function validateCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Check for repeated digits
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validate check digits
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
