<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Article;
use Nette;
use Dravencms\Structure\CmsActionOption;
use Dravencms\Structure\ICmsActionOption;
use Dravencms\Structure\ICmsComponentRepository;

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
    public function getActionOptions(string $componentAction)
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
    public function getActionOption(string $componentAction, array $parameters): ?CmsActionOption
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