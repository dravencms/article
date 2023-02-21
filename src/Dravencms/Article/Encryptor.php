<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Article;


class Encryptor
{
    /** @var string */
    private $encryptionKey;

    /**
     * Encryptor constructor.
     * @param string $encryptionKey
     * @throws \Exception
     */
    public function __construct(string $encryptionKey)
    {
        if (!function_exists('mcrypt_encrypt'))
        {
            throw new \Exception('Mcrypt extension not found, please install it!');
        }
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * @param string $pureString
     * @return string
     */
    public function encrypt(string $pureString): string
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_encrypt(MCRYPT_BLOWFISH, $this->encryptionKey, utf8_encode($pureString), MCRYPT_MODE_ECB, $iv);
    }

    /**
     * @param string $encryptedString
     * @return string
     */
    public function decrypt(string $encryptedString): string
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_decrypt(MCRYPT_BLOWFISH, $this->encryptionKey, $encryptedString, MCRYPT_MODE_ECB, $iv);
    }
}