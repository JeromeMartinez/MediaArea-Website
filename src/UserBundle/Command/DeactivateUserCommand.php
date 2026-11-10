<?php

namespace UserBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Derived from the command of FOSUserBundle (MIT license), which keeps its former name as an alias.
 */
#[AsCommand(name: 'mediaarea:user:deactivate', description: 'Deactivate a user', aliases: ['fos:user:deactivate'])]
class DeactivateUserCommand extends ActivateUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setHelp(
            <<<'EOT'
The <info>%command.name%</info> command deactivates a user (will not be able to log in)

  <info>php %command.full_name% matthieu</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        $this->userManipulator->deactivate($username);

        $output->writeln(sprintf('User "%s" has been deactivated.', $username));

        return Command::SUCCESS;
    }
}
