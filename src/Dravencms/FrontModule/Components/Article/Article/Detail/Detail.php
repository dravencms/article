<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Article\Article\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\ArticleTranslationRepository;
use Dravencms\Structure\ICmsActionOption;

class Detail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var ILocale */
    private $currentLocale;

    /** @var ArticleTranslationRepository */
    private $articleTranslationRepository;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param ArticleRepository $articleRepository
     * @param ArticleTranslationRepository $articleTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
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

        if (!$article) {
            throw new \Nette\Application\BadRequestException(sprintf('Article %s not found', $this->cmsActionOption->getParameter('id')));
        }

        $template->article = $article;
        $template->articleTranslation = $this->articleTranslationRepository->getTranslation($article, $this->currentLocale);
        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__ . '/detail.latte'));
        $template->render();
    }
}
