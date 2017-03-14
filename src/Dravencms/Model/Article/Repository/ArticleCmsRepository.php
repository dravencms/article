<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Article;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class ArticleCmsRepository implements ICmsComponentRepository
{
    private $articleRepository;
    
    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
            case 'OverviewDetail':
                $return = [];
                /** @var Article $article */
                foreach ($this->articleRepository->getActive() AS $article) {
                    $return[] = new CmsActionOption($article->getIdentifier(), ['id' => $article->getId()]);
                }
                break;

            case 'Overview':
            case 'SimpleOverview':
            case 'Navigation':
                return null;
                break;

            default:
                return false;
                break;
        }
        

        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters)
    {
        /** @var Article $found */
        $found = $this->articleRepository->getOneByParameters($parameters + ['isActive' => true]);
        
        if ($found)
        {
            return new CmsActionOption(($found->getLead() ? $found->getLead() . ' ' : '') . $found->getIdentifier(), $parameters);
        }

        return null;
    }
}