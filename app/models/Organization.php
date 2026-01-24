<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Organization extends Model
{
    public function getSettings(): array
    {
        $stmt = $this->db->query("SELECT * FROM organization_settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            // Should not happen if migration ran, but fallback
            return [
                'id' => 0,
                'organization_name' => '',
                'cnpj' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'logo' => '',
                'institutional_text' => '',
                'login_title' => 'Bem-vindo ao SGC',
                'login_subtitle' => 'Faça login para continuar',
                'login_primary_color' => '#0d1b2a',
                'login_background_color' => '#0d1b2a',
                'login_background_image' => '',
                'login_icon' => 'fas fa-graduation-cap',
                'login_logo' => ''
            ];
        }
        
        return $settings;
    }

    public function updateLoginSettings(array $data): void
    {
        $current = $this->getSettings();
        $id = $current['id'] ?? 0;

        if ($id > 0) {
            $sql = "UPDATE organization_settings SET 
                    login_title = :login_title,
                    login_subtitle = :login_subtitle,
                    login_primary_color = :login_primary_color,
                    login_background_color = :login_background_color,
                    login_icon = :login_icon";
            
            $params = [
                'login_title' => $data['login_title'],
                'login_subtitle' => $data['login_subtitle'],
                'login_primary_color' => $data['login_primary_color'],
                'login_background_color' => $data['login_background_color'],
                'login_icon' => $data['login_icon'],
                'id' => $id
            ];

            if (isset($data['login_background_image'])) {
                $sql .= ", login_background_image = :login_background_image";
                $params['login_background_image'] = $data['login_background_image'];
            }
            
            if (isset($data['login_logo'])) {
                $sql .= ", login_logo = :login_logo";
                $params['login_logo'] = $data['login_logo'];
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
             // Create initial record if it doesn't exist (though it should usually exist)
             // For brevity, assuming it exists or handled by updateSettings first. 
             // But to be safe, let's just insert with defaults + these values.
             // Actually, the main updateSettings handles insert. Let's assume organization settings are initialized.
             // If not, we can do a partial insert.
             $sql = "INSERT INTO organization_settings (
                login_title, login_subtitle, login_primary_color, login_background_color, login_icon, login_background_image, login_logo
             ) VALUES (
                :login_title, :login_subtitle, :login_primary_color, :login_background_color, :login_icon, :login_background_image, :login_logo
             )";
             $stmt = $this->db->prepare($sql);
             $stmt->execute([
                'login_title' => $data['login_title'],
                'login_subtitle' => $data['login_subtitle'],
                'login_primary_color' => $data['login_primary_color'],
                'login_background_color' => $data['login_background_color'],
                'login_icon' => $data['login_icon'],
                'login_background_image' => $data['login_background_image'] ?? null,
                'login_logo' => $data['login_logo'] ?? null
             ]);
        }
    }

    public function updateSettings(array $data): void
    {
        // Check if record exists
        $current = $this->getSettings();
        
        if (isset($current['id']) && $current['id'] > 0) {
            $sql = "UPDATE organization_settings SET 
                    organization_name = :organization_name,
                    cnpj = :cnpj,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    city = :city,
                    state = :state,
                    zip_code = :zip_code,
                    institutional_text = :institutional_text";
            
            $params = [
                'organization_name' => $data['organization_name'],
                'cnpj' => $data['cnpj'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip_code' => $data['zip_code'],
                'institutional_text' => $data['institutional_text']
            ];

            if (isset($data['logo']) && $data['logo'] !== null) {
                $sql .= ", logo = :logo";
                $params['logo'] = $data['logo'];
            }

            $sql .= " WHERE id = :id";
            $params['id'] = $current['id'];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert if empty (fallback)
            $sql = "INSERT INTO organization_settings (
                    organization_name, cnpj, email, phone, address, city, state, zip_code, institutional_text, logo
                ) VALUES (
                    :organization_name, :cnpj, :email, :phone, :address, :city, :state, :zip_code, :institutional_text, :logo
                )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'organization_name' => $data['organization_name'],
                'cnpj' => $data['cnpj'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip_code' => $data['zip_code'],
                'institutional_text' => $data['institutional_text'],
                'logo' => $data['logo'] ?? null
            ]);
        }
    }
}
