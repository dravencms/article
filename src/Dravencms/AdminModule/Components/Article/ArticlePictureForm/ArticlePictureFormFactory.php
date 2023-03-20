<?php declare(strict_types = 1);

/**
 * Copyright (C) 2023 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Article\ArticlePictureForm;


use Dravencms\Model\Article\Entities\ArticlePicture;
use Dravencms\Model\Article\Entities\Article;

interface ArticlePictureFormFactory
{
    /**
     * @param Article $article
     * @param ArticlePicture|null $articlePicture
     * @return ArticlePictureForm
     */
    public function create(Article $article, ArticlePicture $articlePicture = null): ArticlePictureForm;
}