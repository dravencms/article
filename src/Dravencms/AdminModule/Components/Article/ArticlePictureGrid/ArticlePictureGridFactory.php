<?php declare(strict_types = 1);
/**
 * Copyright (C) 2023 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Article\ArticlePictureGrid;
use Dravencms\Model\Article\Entities\Article;

/**
 * Interface ArticlePictureGridFactory
 */
interface ArticlePictureGridFactory
{
    /**
     * @param Article $article
     * @return ArticlePictureGrid
     */
    public function create(Article $article): ArticlePictureGrid;
}