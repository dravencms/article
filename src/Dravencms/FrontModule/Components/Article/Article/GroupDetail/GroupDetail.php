<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Article\Article\GroupDetail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Structure\ICmsActionOption;

class GroupDetail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    public function __construct(ICmsActionOption $cmsActionOption, ArticleRepository $articleRepository)
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->articleRepository = $articleRepository;
    }

    public function render(): void
    {
        $template = $this->template;
        $detail = $this->articleRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));

        $template->articles = $this->articleRepository->search($detail->getGroup(), null, [], true, 8, null, $detail);
        
        $template->article = $detail;
        $template->setFile(__DIR__ . '/groupDetail.latte');
        $template->render();
    }
}
