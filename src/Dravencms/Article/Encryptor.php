<?php
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
    public function __construct($encryptionKey)
    {
        if (!function_exists('mcrypt_encrypt'))
        {
            throw new \Exception('Mcrypt extension not found, please install it!');
        }
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * @param $pureString
     * @return mixed
     */
    public function encrypt($pureString)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_encrypt(MCRYPT_BLOWFISH, $this->encryptionKey, utf8_encode($pureString), MCRYPT_MODE_ECB, $iv);
    }

    /**
     * @param $encryptedString
     * @return mixed
     */
    public function decrypt($encryptedString)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_decrypt(MCRYPT_BLOWFISH, $this->encryptionKey, $encryptedString, MCRYPT_MODE_ECB, $iv);
    }
}