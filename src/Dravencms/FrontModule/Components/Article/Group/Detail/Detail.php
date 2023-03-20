<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Article\Group\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BasePaginator\BasePaginatorFactory;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\ArticleTranslationRepository;
use Dravencms\Model\Article\Repository\GroupRepository;
use IPub\VisualPaginator\Components\Control;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Structure\ICmsActionOption;

class Detail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ArticleTranslationRepository */
    private $articleTranslationRepository;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var BasePaginatorFactory */
    private $basePaginatorFactory;

    /** @var Locale */
    private $currentLocale;

    public function __construct(
        ICmsActionOption $cmsActionOption,
        ArticleRepository $articleRepository,
        ArticleTranslationRepository $articleTranslationRepository,
        GroupRepository $groupRepository,
        BasePaginatorFactory $basePaginatorFactory,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->articleRepository = $articleRepository;
        $this->groupRepository = $groupRepository;
        $this->basePaginatorFactory = $basePaginatorFactory;
        $this->articleTranslationRepository = $articleTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    public function render(): void
    {
        $template = $this->template;

        $group =  $this->groupRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $all = $this->articleTranslationRepository->search($this->currentLocale, $group);

        $visualPaginator = $this['visualPaginator'];

        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = count($all);

        $template->overview = $this->articleTranslationRepository->search($this->currentLocale, $group, null, [], true, $paginator->itemsPerPage, $paginator->offset);
        
        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__.'/detail.latte'));
        $template->render();
    }

    /**
     * @return Control
     */
    protected function createComponentVisualPaginator()
    {
        $control = $this->basePaginatorFactory->create();

        $control->onShowPage[] = (function ($component, $page) {
            if ($this->presenter->isAjax()){
                $this->redrawControl('overview');
            }
        });

        return $control;
    }
}
