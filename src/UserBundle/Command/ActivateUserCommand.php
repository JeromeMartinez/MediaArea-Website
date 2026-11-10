<?php

namespace UserBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use UserBundle\Lib\UserManipulator;

/**
 * Derived from the command of FOSUserBundle (MIT license), which keeps its former name as an alias.
 */
#[AsCommand(name: 'mediaarea:user:activate', description: 'Activate a user', aliases: ['fos:user:activate'])]
class ActivateUserCommand extends Command
{
    protected $userManipulator;

    public function __construct(UserManipulator $userManipulator)
    {
        parent::__construct();

        $this->userManipulator = $userManipulator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ])
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command activates a user (so they will be able to log in):

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

        $this->userManipulator->activate($username);

        $output->writeln(sprintf('User "%s" has been activated.', $username));

        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $answer = $this->getHelper('question')->ask($input, $output, $question);

            $input->setArgument('username', $answer);
        }
    }
}
