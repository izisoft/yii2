<?php
namespace izi\base;

use Yii;
use yii\helpers\StringHelper;

/**
 * Security provides a set of methods to handle common security-related tasks.
 *
 * In particular, Security supports the following features:
 *
 * - Encryption/decryption: [[encryptByKey()]], [[decryptByKey()]], [[encryptByPassword()]] and [[decryptByPassword()]]
 * - Key derivation using standard algorithms: [[pbkdf2()]] and [[hkdf()]]
 * - Data tampering prevention: [[hashData()]] and [[validateData()]]
 * - Password validation: [[generatePasswordHash()]] and [[validatePassword()]]
 *
 * > Note: this class requires 'OpenSSL' PHP extension for random key/string generation on Windows and
 * for encryption/decryption on all platforms. For the highest security level PHP version >= 5.5.0 is recommended.
 *
 * For more details and usage information on Security, see the [guide article on security](guide:security-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tom Worster <fsb@thefsb.org>
 * @author Klimov Paul <klimov.paul@gmail.com>
 * @since 2.0
 */
class Security extends \yii\base\Security
{

    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = StringHelper::byteLength($expected);
        $actualLength = StringHelper::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }
    
    private function generateSaltString($data){
        $data['amount'] = number_format($data['amount'],2);
        return "$^@{$data['sid']}.{$data['branch_id']}@{$data['amount']}.{$data['currency']}@^$";
    }
    public function generateFundBookCheckSum($data){
        return $this->generatePasswordHash($this->generateSaltString($data));
    }
    
    public function validateFundBookCheckSum($data){
        $hash = $this->generateSaltString($data);
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $data['check_sum'], $matches)
            || $matches[1] < 4
            || $matches[1] > 30
            ) {
                return false;
            }
            return Yii::$app->security->validatePassword($hash, $data['check_sum']);
    }
}