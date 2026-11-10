<?php

namespace UserBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Lib\UserManipulator;

/**
 * Derived from the command of FOSUserBundle (MIT license), which keeps its former name as an alias.
 */
#[AsCommand(
    name: 'mediaarea:user:demote',
    description: 'Demote a user by removing a role',
    aliases: ['fos:user:demote'],
)]
class DemoteUserCommand extends RoleCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setHelp(
            <<<'EOT'
The <info>%command.name%</info> command demotes a user by removing a role

  <info>php %command.full_name% matthieu ROLE_CUSTOM</info>
  <info>php %command.full_name% --super matthieu</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function executeRoleCommand(
        UserManipulator $manipulator,
        OutputInterface $output,
        $username,
        $super,
        $role
    ) {
        if ($super) {
            $manipulator->demote($username);
            $output->writeln(sprintf('User "%s" has been demoted as a simple user.', $username));
        } elseif ($manipulator->removeRole($username, $role)) {
            $output->writeln(sprintf('Role "%s" has been removed from user "%s".', $role, $username));
        } else {
            $output->writeln(sprintf('User "%s" didn\'t have "%s" role.', $username, $role));
        }
    }
}
