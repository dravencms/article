<?php declare(strict_types = 1);
namespace Dravencms\Article;

use Nette\SmartObject;

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
class LinkProtector
{
    use SmartObject;

    /** @var string */
    private $html;

    /** @var Encryptor */
    private $encryptor;

    /**
     * LinkProtector constructor.
     * @param $html
     * @throws \Exception
     */
    public function __construct(string $html)
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
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getProtectedHtml(): string
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
    public function __toString(): string
    {
        return $this->getProtectedHtml();
    }

    /**
     * @return mixed
     */
    public function getEncryptionKey(): string
    {
        return date('Y-m');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function decrypt(string $data): string
    {
        return preg_replace('/[\x00-\x1F\x7F]/', '', $this->encryptor->decrypt(base64_decode($data)));
    }
}
