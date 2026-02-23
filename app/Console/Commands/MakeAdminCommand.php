<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {name?} {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um novo utilizador admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('Qual é o nome do utilizador?');
        }

        $email = $this->argument('email');
        if (!$email) {
            $email = $this->ask('Qual é o email?');
        }

        $password = $this->argument('password');
        if (!$password) {
            $password = $this->secret('Qual é a password?');
        }

        // Validate basic inputs to ensure they are not empty
        if (empty($name) || empty($email) || empty($password)) {
            $this->error('Todos os campos (nome, email, password) são obrigatórios.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Já existe um utilizador com este email!');
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Utilizador '{$name}' criado com sucesso! (Admin)");

        return self::SUCCESS;
    }
}
