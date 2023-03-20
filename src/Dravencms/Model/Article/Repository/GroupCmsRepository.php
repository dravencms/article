<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Group;
use Nette;
use Dravencms\Structure\CmsActionOption;
use Dravencms\Structure\ICmsActionOption;
use Dravencms\Structure\ICmsComponentRepository;

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
    public function getActionOptions(string $componentAction)
    {
        switch ($componentAction) {
            case 'Detail':
            case 'SimpleDetail':
                $return = [];
                /** @var Group $group */
                foreach ($this->groupRepository->getAll() AS $group) {
                    $return[] = new CmsActionOption($group->getIdentifier(), ['id' => $group->getId()]);
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
    public function getActionOption(string $componentAction, array $parameters): ?CmsActionOption
    {

        $found = $this->groupRepository->getOneByParameters($parameters);
        
        if ($found) {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}