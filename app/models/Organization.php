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
                'institutional_text' => ''
            ];
        }
        
        return $settings;
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
