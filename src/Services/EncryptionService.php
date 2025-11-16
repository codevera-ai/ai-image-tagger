<?php

namespace AIImageTagger\Services;

class EncryptionService {

    private string $key;
    private string $cipher = 'aes-256-cbc';

    public function __construct() {
        $this->key = $this->getEncryptionKey();
    }

    public function encrypt(string $data): string {
        if (empty($data)) {
            return '';
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public function decrypt(string $data): string {
        if (empty($data)) {
            return '';
        }
        
        $parts = explode('::', base64_decode($data), 2);

        if (count($parts) !== 2) {
            return '';
        }

        [$encrypted, $iv] = $parts;
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }

    private function getEncryptionKey(): string {
        // Use WordPress salts as encryption key
        if (defined('AUTH_KEY') && defined('SECURE_AUTH_KEY')) {
            return hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
        }

        throw new \Exception('WordPress authentication keys not defined');
    }
}
