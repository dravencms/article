<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Group;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class GroupCmsRepository implements ICmsComponentRepository
{
    private $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction) {
            case 'Detail':
            case 'SimpleDetail':
                $return = [];
                /** @var Group $group */
                foreach ($this->groupRepository->getAll() AS $group) {
                    $return[] = new CmsActionOption($group->getName(), ['id' => $group->getId()]);
                }
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

        $found = $this->groupRepository->getOneByParameters($parameters);
        
        if ($found) {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}