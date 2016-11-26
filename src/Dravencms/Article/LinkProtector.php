<?php
namespace Dravencms\Article;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailProtector
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class LinkProtector extends \Nette\Object
{
    /** @var string */
    private $html;

    /** @var Encryptor */
    private $encryptor;

    /**
     * LinkProtector constructor.
     * @param $html
     * @throws \Exception
     */
    public function __construct($html)
    {
        $this->encryptor = new Encryptor($this->getEncryptionKey());

        if (!is_string($html)) {
            throw new \Exception('$html must be a text');
        }

        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getProtectedHtml()
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $this->html = mb_convert_encoding($this->html, 'HTML-ENTITIES', "UTF-8");
        @$dom->loadHTML($this->html);

        foreach ($dom->getElementsByTagName('a') as $node) {
            $href = $node->getAttribute("href");
            if (strpos($href, ':') !== false) {
                list($protocol, $raw) = explode(':', $href);
                switch ($protocol) {
                    case 'mailto':
                        $node->nodeValue = str_replace('@', '<span class="fa fa-at"></span>', $node->nodeValue);
                        $encrypted = urlencode(base64_encode($this->encryptor->encrypt($raw)));
                        $node->setAttribute("href", '#' . $encrypted);
                        $node->setAttribute("class", 'mailtoprotected');
                        break;
                }
            }
        }

        $xpath = new \DOMXPath($dom);
        $body = $xpath->query('/html/body');
        return (string) html_entity_decode(strtr($dom->saveHTML($body->item(0)), array('<body>' => '', '</body>' => '')));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getProtectedHtml();
    }

    /**
     * @return mixed
     */
    public function getEncryptionKey()
    {
        return date('Y-m');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function decrypt($data)
    {
        return preg_replace('/[\x00-\x1F\x7F]/', '', $this->encryptor->decrypt(base64_decode($data)));
    }
}