<?php

namespace Dravencms\FrontModule\Components\Article\Article\Detail;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Salamek\Cms\ICmsActionOption;

class Detail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    public function __construct(ICmsActionOption $cmsActionOption, ArticleRepository $articleRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->articleRepository = $articleRepository;
    }
    
    public function render()
    {
        $template = $this->template;
        $detail = $this->articleRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));

        if (!$detail) {
            throw new \Nette\Application\BadRequestException();
        }

        $template->detail = $detail;
        $template->setFile(__DIR__ . '/detail.latte');
        $template->render();
    }
}
