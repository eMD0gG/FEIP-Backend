<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Создает пользователя с правами доступа к админ панели',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Эта команда создает нового пользователя с ролью ROLE_ADMIN')
            ->addArgument('name', InputArgument::REQUIRED, 'Имя администратора')
            ->addArgument('number', InputArgument::REQUIRED, 'Номер телефона (уникальный)')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Пароль пользователя')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Перезаписать существующего пользователя')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Показать всех администраторов')
            ->addOption('make-admin', 'm', InputOption::VALUE_REQUIRED, 'Сделать существующего пользователя администратором по ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listAdmins($io);
        }

        if ($adminId = $input->getOption('make-admin')) {
            return $this->makeExistingUserAdmin($io, (int)$adminId);
        }

        return $this->createAdminUser($input, $io);
    }

    private function createAdminUser(InputInterface $input, SymfonyStyle $io): int
    {
        $io->title('Создание пользователя с правами администратора');

        $name = $input->getArgument('name');
        $number = $input->getArgument('number');

        $password = $input->getOption('password');
        if (!$password) {
            $password = $io->askHidden('Введите пароль для администратора');
            
            if (empty($password)) {
                $io->error('Пароль не может быть пустым');
                return Command::FAILURE;
            }
        }

        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['number' => $number]);

        if ($existingUser) {
            if ($input->getOption('force')) {
                $io->note('Обновление существующего пользователя...');
                
                $existingUser->setName($name);
                $existingUser->setRoles(['ROLE_ADMIN']);
                $existingUser->setPassword($this->passwordHasher->hashPassword($existingUser, $password));
                
                $this->entityManager->flush();
                
                $io->success([
                    '✅ Пользователь обновлен с правами администратора',
                    sprintf('ID: %d', $existingUser->getId()),
                    sprintf('Имя: %s', $existingUser->getName()),
                    sprintf('Номер: %s', $existingUser->getUserIdentifier()),
                    sprintf('Роли: %s', implode(', ', $existingUser->getRoles())),
                ]);
            } else {
                $io->error(sprintf('Пользователь с номером %s уже существует', $number));
                $io->note('Используйте опцию --force для перезаписи.');
                return Command::FAILURE;
            }
        } else {
            $user = new User();
            $user->setName($name);
            $user->setNumber($number);
            $user->setRoles(['ROLE_ADMIN']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $io->success([
                'Администратор успешно создан',
                sprintf('ID: %d', $user->getId()),
                sprintf('Имя: %s', $user->getName()),
                sprintf('Номер: %s', $user->getUserIdentifier()),
                sprintf('Роли: %s', implode(', ', $user->getRoles())),
            ]);
        }

        $io->note([
            'Данные для входа в админ-панель:',
            sprintf('Номер телефона: %s', $number),
            sprintf('Пароль: %s', $input->getOption('password') ? '[указанный]' : '[введенный]'),
            'URL: /admin',
        ]);

        return Command::SUCCESS;
    }

    private function listAdmins(SymfonyStyle $io): int
    {
        $io->title('Список администраторов');

        $adminUsers = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($adminUsers)) {
            $io->warning('Администраторы не найдены');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($adminUsers as $user) {
            $rows[] = [
                $user->getId(),
                $user->getName(),
                $user->getNumber(),
                implode(', ', $user->getRoles()),
                $user->getBookingRequests()->count(),
            ];
        }

        $io->table(
            ['ID', 'Имя', 'Номер', 'Роли', 'Бронирований'],
            $rows
        );

        return Command::SUCCESS;
    }

    private function makeExistingUserAdmin(SymfonyStyle $io, int $userId): int
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $io->error(sprintf('Пользователь с ID %d не найден', $userId));
            return Command::FAILURE;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $io->warning(sprintf('Пользователь %s (ID: %d) уже является администратором', 
                $user->getName(), $user->getId()));
            return Command::SUCCESS;
        }

        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_unique($roles));
        
        $this->entityManager->flush();

        $io->success([
            'Пользователь теперь администратор',
            sprintf('ID: %d', $user->getId()),
            sprintf('Имя: %s', $user->getName()),
            sprintf('Номер: %s', $user->getUserIdentifier()),
            sprintf('Роли: %s', implode(', ', $user->getRoles())),
        ]);

        return Command::SUCCESS;
    }
}