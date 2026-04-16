<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ManageUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:manage
                            {action? : Ação a executar: create ou edit}
                            {--user= : ID ou email do utilizador (obrigatório no modo edit)}
                            {--name= : Nome do utilizador}
                            {--email= : Email do utilizador}
                            {--password= : Password do utilizador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar ou editar um utilizador';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        if (!$action) {
            $action = $this->choice('O que pretende fazer?', ['create', 'edit'], 0);
        }

        if (!in_array($action, ['create', 'edit'], true)) {
            $this->error("A ação '{$action}' é inválida. Use 'create' ou 'edit'.");

            return self::FAILURE;
        }

        if ($action === 'create') {
            return $this->handleCreate();
        }

        return $this->handleEdit();
    }

    private function handleCreate(): int
    {
        $name = $this->option('name') ?: $this->ask('Nome do utilizador?');
        $email = $this->option('email') ?: $this->ask('Email do utilizador?');
        $password = $this->option('password') ?: $this->secret('Password do utilizador?');

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Utilizador '{$user->name}' criado com sucesso.");

        return self::SUCCESS;
    }

    private function handleEdit(): int
    {
        $identifier = $this->option('user') ?: $this->ask('Indique o ID ou email do utilizador a editar');
        $user = $this->resolveUser($identifier);

        if (!$user) {
            $this->error('Utilizador não encontrado.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Nome do utilizador?', $user->name);
        $email = $this->option('email') ?: $this->ask('Email do utilizador?', $user->email);

        $password = $this->option('password');
        if (!$password && $this->confirm('Deseja alterar a password?', false)) {
            $password = $this->secret('Nova password do utilizador?');
        }

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->name = $name;
        $user->email = $email;

        if ($password) {
            $user->password = Hash::make($password);
        }

        $user->save();

        $this->info("Utilizador '{$user->name}' atualizado com sucesso.");

        return self::SUCCESS;
    }

    private function resolveUser(?string $identifier): ?User
    {
        if (!$identifier) {
            return null;
        }

        if (is_numeric($identifier)) {
            return User::find((int) $identifier);
        }

        return User::where('email', $identifier)->first();
    }
}