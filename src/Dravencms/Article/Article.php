<?php declare(strict_types = 1);

namespace Dravencms\Article;

use Nette\SmartObject;

/**
 * Class Article
 * @package Dravencms\Article
 */
class Article
{
    const PLUGIN_NAME = 'article';

    use SmartObject;
    public function __construct()
    {
    }
}
