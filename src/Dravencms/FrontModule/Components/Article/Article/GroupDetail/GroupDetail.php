<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Article\Article\GroupDetail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\ArticleTranslationRepository;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Structure\ICmsActionOption;

class GroupDetail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ArticleTranslationRepository */
    private $articleTranslationRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

     /** @var Locale */
     private $currentLocale;

    public function __construct(
        ICmsActionOption $cmsActionOption, 
        ArticleRepository $articleRepository,
        ArticleTranslationRepository $articleTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->articleRepository = $articleRepository;
        $this->articleTranslationRepository = $articleTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    public function render(): void
    {
        $template = $this->template;
        $article = $this->articleRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $articleTranslation = $this->articleTranslationRepository->getTranslation($article, $this->currentLocale);
        $template->article = $article;
        $template->articleTranslation = $articleTranslation;

        $previousArticle = $this->articleRepository->getPreviousArticle($article);
        $template->previousArticle = $previousArticle;
        if ($previousArticle)
        {
            $template->previousArticleTranslation = $this->articleTranslationRepository->getTranslation($previousArticle, $this->currentLocale);
        } else {
            $template->previousArticleTranslation = null;
        }

        $nextArticle = $this->articleRepository->getNextArticle($article);
        $template->nextArticle = $nextArticle;
        if ($nextArticle)
        {
            $template->nextArticleTranslation = $this->articleTranslationRepository->getTranslation($nextArticle, $this->currentLocale);
        } else {
            $template->nextArticleTranslation = null;
        }

        $template->articleTranslations = $this->articleTranslationRepository->search($this->currentLocale, $article->getGroup(), null, [], true, 8, null, $article);
        
        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__.'/groupDetail.latte'));
        $template->render();
    }
}
