<?php

namespace MediaConchOnlineBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UserBundle\Entity\User;

class CleanGuestUsersCommand extends Command
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure(): void
    {
        $this
            ->setName('mco:guest:clean')
            ->setDescription('Remove inactive guest users')
            ->setHelp(
                <<<EOT
The <info>mco:guest:clean</info> command remove inactive guest users whom did not log in during the last xx days (31 by default):
  <info>php app/console mco:guest:clean 31</info>
EOT
            )
            ->addArgument('inactive_days', InputArgument::OPTIONAL, 'Number of days of inactivity');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Remove inactive guest users');

        // Days by default if not set
        $days = $input->getArgument('inactive_days');
        if (!$days) {
            $days = 31;
        }

        // Set date for search query
        $date = new \DateTime(date('Y-m-d', strtotime('-'.$days.' days')));

        $query = $this->em->createQueryBuilder()->select('u')
            ->from(User::class, 'u')
            ->where('u.lastLogin < :date')
            ->setParameter('date', $date)
            ->getQuery();

        $deletedUsers = 0;

        foreach ($query->getResult() as $user) {
            if ($user->hasRole('ROLE_GUEST')) {
                $this->em->remove($user);
                ++$deletedUsers;
            }
        }

        // Delete users in DB
        $this->em->flush();

        $io->success($deletedUsers.' deleted users');

        return Command::SUCCESS;
    }
}
