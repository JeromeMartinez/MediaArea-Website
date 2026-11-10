<?php

namespace UserBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Lib\UserManipulator;

/**
 * Derived from the command of FOSUserBundle (MIT license), which keeps its former name as an alias.
 */
#[AsCommand(
    name: 'mediaarea:user:promote',
    description: 'Promotes a user by adding a role',
    aliases: ['fos:user:promote'],
)]
class PromoteUserCommand extends RoleCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setHelp(
            <<<'EOT'
The <info>%command.name%</info> command promotes a user by adding a role

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
            $manipulator->promote($username);
            $output->writeln(sprintf('User "%s" has been promoted as a super administrator.', $username));
        } elseif ($manipulator->addRole($username, $role)) {
            $output->writeln(sprintf('Role "%s" has been added to user "%s".', $role, $username));
        } else {
            $output->writeln(sprintf('User "%s" did already have "%s" role.', $username, $role));
        }
    }
}
