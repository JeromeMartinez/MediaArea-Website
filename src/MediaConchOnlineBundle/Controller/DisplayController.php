<?php

namespace MediaConchOnlineBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use MediaConchOnlineBundle\Entity\DisplayFile;
use MediaConchOnlineBundle\Form\Type\DisplayImportFormType;
use UserBundle\Lib\Quotas\Quotas;
use Doctrine\Persistence\ManagerRegistry;
use Vich\UploaderBundle\Handler\DownloadHandler;

#[Route('/MediaConchOnline/display/')]
class DisplayController extends BaseController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('', name: 'mco_display')]
    #[Template('@MediaConchOnline/Display/display.html.twig')]
    public function displayAction(Request $request, Quotas $quotas)
    {
        $displayList = $this->doctrine
            ->getRepository(\MediaConchOnlineBundle\Entity\DisplayFile::class)
            ->findByUser($this->getUser());

        $displaySystemList = $this->doctrine
            ->getRepository(\MediaConchOnlineBundle\Entity\DisplayFile::class)
            ->findByUser(null);

        if ($quotas->hasPolicyCreationRights()) {
            $display = new DisplayFile();
            $importDisplayForm = $this->createForm(DisplayImportFormType::class, $display);
            $importDisplayForm->handleRequest($request);
            if ($importDisplayForm->isSubmitted() && $importDisplayForm->isValid()) {
                $em = $this->doctrine->getManager();

                // Set user at the creation of the policy
                if (null === $display->getUser()) {
                    $display->setUser($this->getUser());
                }

                $em->persist($display);
                $em->flush();

                $this->addFlashBag('success', 'Display successfully added');

                return $this->redirectToRoute('mco_display');
            }
        }

        return [
            'importDisplayForm' => isset($importDisplayForm) ? $importDisplayForm->createView() : false,
             'displayList' => $displayList,
             'displaySystemList' => $displaySystemList,
        ];
    }

    #[Route('delete/{id}', name: 'mco_display_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function displayDeleteAction($id)
    {
        $policy = $this->doctrine
            ->getRepository(\MediaConchOnlineBundle\Entity\DisplayFile::class)
            ->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$policy) {
            $this->addFlashBag('danger', 'Display not found');
        } else {
            $em = $this->doctrine->getManager();
            $em->remove($policy);
            $em->flush();

            $this->addFlashBag('success', 'Display successfully removed');
        }

        return $this->redirectToRoute('mco_display');
    }

    #[Route('export/{id}', name: 'mco_display_export', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function displayExportAction($id, DownloadHandler $handler)
    {
        $policy = $this->doctrine
            ->getRepository(\MediaConchOnlineBundle\Entity\DisplayFile::class)
            ->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$policy) {
            $this->addFlashBag('danger', 'Display not found');

            return $this->redirectToRoute('mco_display');
        }

        return $handler->downloadObject($policy, 'displayFile');
    }

    #[Route('system/export/{id}', name: 'mco_display_systemexport', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function displaySystemExportAction($id, DownloadHandler $handler)
    {
        $policy = $this->doctrine
            ->getRepository(\MediaConchOnlineBundle\Entity\DisplayFile::class)
            ->findOneBy(['id' => $id, 'user' => null]);

        if (!$policy) {
            $this->addFlashBag('danger', 'Display not found');

            return $this->redirectToRoute('mco_display');
        }

        return $handler->downloadObject($policy, 'displayFile');
    }
}
