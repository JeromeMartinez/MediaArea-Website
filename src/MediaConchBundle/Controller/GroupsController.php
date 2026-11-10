<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaConchBundle\Lib\Checks\Groups;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[Route('/Groups')]
class GroupsController extends AbstractController
{
    #[Route('', name: 'groups_home')]
    #[Template('@MediaConch/Groups/groups.html.twig')]
    public function groupsAction(Groups $groups)
    {
        header('X-Robots-Tag: none');

        $items = $groups->listGroups();
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $items ]);
    }
    
    #[Route('/{groupId}', name: 'mc_groups1')]
    #[Template('@MediaConch/Groups/group1.html.twig')]
    public function group1Action(Groups $groups, $groupId)
    {
        $items = $groups->listGroupInfo($groupId);
        if (!$items) {
            throw $this->createNotFoundException('This group is not found in our database');
        }
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $items ]);
    }
}
