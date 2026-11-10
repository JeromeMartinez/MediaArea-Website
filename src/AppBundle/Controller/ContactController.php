<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/Contact', name: 'ma_contact')]
    #[Template('@App/Contact/index.html.twig')]
    public function indexAction(Request $request, MailerInterface $mailer, LoggerInterface $logger)
    {
        $contact = new Contact();

        if ('GET' === $request->getMethod() && $request->query->get('sponsor') && $request->query->get('amount')) {
            $contact
                ->setSubject(sprintf(
                    'Request quote for %s (%s)',
                    htmlspecialchars($request->query->get('sponsor')),
                    htmlspecialchars($request->query->get('amount'))
                ))
                ->setMessage(sprintf(
                    "I'm interested in becoming a MediaArea sponsor.\n\nPlease send me a quote of %s to become a %s.",
                    htmlspecialchars($request->query->get('amount')),
                    htmlspecialchars($request->query->get('sponsor'))
                ));
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ('POST' === $request->getMethod() && $form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $message = (new Email())
                ->subject($contact->getSubject())
                ->from(new Address($contact->getEmail(), (string) $contact->getName()))
                ->to('info@mediaarea.net')
                ->text(
                    sprintf(
                        "Source: web\n%s\n%s",
                        $contact->getCompany() ? 'Company: '.$contact->getCompany()."\n" : '',
                        $contact->getMessage()
                    )
                );

            try {
                $mailer->send($message);
            } catch (TransportExceptionInterface $e) {
                // As before, a delivery issue does not break the page
                $logger->error('Unable to send the contact email: {error}', [
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }

            $this->addFlash('success', 'Your message has been sent');

            return $this->redirectToRoute('ma_contact');
        }

        return [
            'noAds' => true,
            'form' => $form->createView(),
        ];
    }
}
