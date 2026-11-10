<?php

namespace PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PaymentBundle\Entity\Invoice;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/ajax/modal/{id}', name: 'invoice_ajax_modal_data', requirements: ['id' => '\d+'])]
    public function modalDataAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return new JsonResponse($this->getInvoiceDatas($id, $user), 200);
    }

    #[Route('/pdf/{id}', name: 'invoice_download_pdf', requirements: ['id' => '\d+'])]
    public function downloadPdfAction($id)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $data = $this->getInvoiceDatas($id, $user);

        // Get html for pdf rendering
        $html = $this->renderView('@Payment/Invoice/pdf.html.twig', ['data' => $data]);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $this->getParameter('kernel.cache_dir'),
        ]);
        $mpdf->WriteHTML($html);

        return new Response($mpdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $data['date'].'-MediaArea-Invoice-'.$id.'.pdf'),
        ]);
    }

    /**
     * Get invoices data for display.
     *
     * @param int  $id   Invoice ID
     * @param User $user
     *
     * @return array Datas
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    protected function getInvoiceDatas($id, $user)
    {
        $invoice = $this->em
            ->getRepository(Invoice::class)
            ->findOneBy(['id' => $id, 'user' => $user]);

        if (!$invoice) {
            throw new NotFoundHttpException();
        }

        function formatAmount($amount, $currency)
        {
            if ('JPY' == $currency) {
                return number_format($amount, 0);
            } else {
                return number_format($amount, 2);
            }
        }

        $total = $invoice->getAmount() + $invoice->getVat();
        if ($total >= 20000) {
            $product = 'Gold Sponsor';
        } elseif ($total >= 8000) {
            $product = 'Silver Sponsor';
        } elseif ($total >= 3000) {
            $product = 'Bronze Sponsor';
        } elseif ($total >= 1000) {
            $product = 'Sponsor';
        } elseif ($total >= 500) {
            $product = 'Supporter++';
        } elseif ($total >= 300) {
            $product = 'Supporter+';
        } elseif ($total >= 100) {
            $product = 'Supporter';
        } elseif ($total >= 30) {
            $product = 'Voter';
        } elseif ($total >= 10) {
            $product = 'Member';
        } else {
            $product = 'Thanks!';
        }

        return [
            'id' => $id,
            'date' => $invoice->getDate()->format('Y-m-d'),
            'amount' => formatAmount($invoice->getAmount(), $invoice->getCurrency()),
            'vat' => formatAmount($invoice->getVat(), $invoice->getCurrency()),
            'total' => formatAmount($total, $invoice->getCurrency()),
            'product' => $product,
            'country' => $invoice->getCountry(),
            'currency' => $invoice->getCurrency(),
        ];
    }
}
