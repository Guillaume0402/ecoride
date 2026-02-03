<?php

use PHPUnit\Framework\TestCase;
use App\Security\PasswordPolicy;

class PasswordPolicyTest extends TestCase
{
    function testRejectsWeakPassword(): void
    {
        $weakPwd = 'abc123';
        $errors = PasswordPolicy::validate($weakPwd);
        // Le mot de passe est faible, le tableau d'erreurs n'est pas vide
        $this->assertNotEmpty($errors);
        $this->assertContains('Le mot de passe doit contenir au moins 12 caractères.', $errors);
    }

    function testAcceptsStrongPassword(): void
    {
        $strongPwd = 'Azerty123!@#';
        $errors = PasswordPolicy::validate($strongPwd);
        // Le mot de passe est fort, le tableau d'erreurs est vide
        $this->assertEmpty($errors);
    }

    public function testRejectsPasswordWith11Characters(): void
    {
        // 11 caractères
        $pwd = 'Abcdef123!@';
        $errors = PasswordPolicy::validate($pwd);

        $this->assertNotEmpty($errors);
        $this->assertContains('Le mot de passe doit contenir au moins 12 caractères.', $errors);
    }
}
