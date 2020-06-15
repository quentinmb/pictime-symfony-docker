<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InstallProjectCommand extends Command
{
    protected static $defaultName = 'install-project';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //Migrate all
        $command = $this->getApplication()->find('doctrine:migrations:migrate');
        $returnCode = $command->run($input, $output);


        //Create Author
        $author = new Author();
        $author->setFirstName('John');
        $author->setLastName('Doe');

        //Save the author
        $this->entityManager->persist($author);
        $this->entityManager->flush();


        //Create Book
        $book = new Book();
        $book->setTitle('A book by John Doe');
        $book->setAuthor($author);

        //Save the book
        $this->entityManager->persist($book);
        $this->entityManager->flush();


        $io->success('Install success');

        return 0;
    }
}
