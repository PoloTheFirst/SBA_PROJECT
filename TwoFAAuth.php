<?php
class TwoFAAuth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Generate random secret for 2FA
    public function generateSecret() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }
    
    // Generate backup codes
    public function generateBackupCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5))); // 10-character codes
        }
        return json_encode($codes);
    }
    
    // Verify TOTP code
    public function verifyCode($secret, $code) {
        $timeSlice = floor(time() / 30);
        
        // Check current time and ±1 time window (for clock drift)
        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = $this->getTOTPCode($secret, $timeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }
    
    // Generate TOTP code
    private function getTOTPCode($secret, $timeSlice) {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }
    
    // Base32 decoding
    private function base32Decode($secret) {
        $lut = [
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 
            'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9,
            'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14,
            'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
            'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24,
            'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29,
            '6' => 30, '7' => 31
        ];
        
        $secret = strtoupper($secret);
        $buffer = 0;
        $bufferSize = 0;
        $result = '';
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if (!isset($lut[$char])) continue;
            
            $buffer = $buffer << 5;
            $buffer = $buffer | $lut[$char];
            $bufferSize += 5;
            
            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $result .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }
        return $result;
    }
    
    // Verify backup code
    public function verifyBackupCode($backupCodesJson, $code) {
        $backupCodes = json_decode($backupCodesJson, true);
        if (!$backupCodes) return false;
        
        $index = array_search($code, $backupCodes);
        if ($index !== false) {
            // Remove used backup code
            unset($backupCodes[$index]);
            return json_encode(array_values($backupCodes));
        }
        return false;
    }
}
?>