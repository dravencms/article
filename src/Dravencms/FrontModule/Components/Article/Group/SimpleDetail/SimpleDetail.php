<?php

namespace Dravencms\FrontModule\Components\Article\Group\SimpleDetail;

use Dravencms\Components\BaseControl;
use Dravencms\Components\BasePaginatorFactory;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\GroupRepository;
use IPub\VisualPaginator\Components\Control;
use Salamek\Cms\ICmsActionOption;

class SimpleDetail extends BaseControl
{
    /** @var ArticleRepository */
    private $articleRepository;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var BasePaginatorFactory */
    private $basePaginatorFactory;

    public function __construct(ICmsActionOption $cmsActionOption, ArticleRepository $articleRepository, GroupRepository $groupRepository, BasePaginatorFactory $basePaginatorFactory)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->articleRepository = $articleRepository;
        $this->groupRepository = $groupRepository;
        $this->basePaginatorFactory = $basePaginatorFactory;
    }


    public function render()
    {
        $template = $this->template;

        $group =  $this->groupRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $all = $this->articleRepository->search($group);

        $visualPaginator = $this['visualPaginator'];

        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = count($all);



        $template->group = $group;
        $template->overview = $this->articleRepository->search($group, null, [], true, $paginator->itemsPerPage, $paginator->offset);

        $template->setFile(__DIR__.'/simpleDetail.latte');
        $template->render();
    }

    /**
     * @return Control
     */
    protected function createComponentVisualPaginator()
    {
        // Init visual paginator
        /*$control = new Control();
        $control->setTemplateFile('bootstrap.latte');*/

        $control = $this->basePaginatorFactory->create();

        $control->onShowPage[] = (function ($component, $page) {
            if ($this->presenter->isAjax()){
                $this->redrawControl('overview');
            }
        });

        return $control;
    }

}
