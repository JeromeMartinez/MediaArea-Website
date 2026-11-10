<?php

namespace MediaBinBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use MediaBinBundle\Lib\File\File;

class BinSubscriber implements EventSubscriberInterface
{
    protected $binFile;

    public function __construct(File $binFile)
    {
        $this->binFile = $binFile;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'create',
        ];
    }

    public function create(TerminateEvent $event)
    {
        if (!$binHash = $event->getRequest()->attributes->get('mediabin.create')) {
            return;
        }

        $this->binFile->saveToExternal($binHash);
    }
}
