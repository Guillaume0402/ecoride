<?php

use PHPUnit\Framework\TestCase;
use App\Service\UserService;
use App\Entity\UserEntity;

class UserServiceTest extends TestCase
{
    function testValidateReturnsErrorIfPseudoIsTooShort(): void
    {
        $user = new UserEntity();
        $user->setPseudo('ab');
        $user->setEmail('user@email.com');
        $user->setRoleId(1);
        $user->setCredits(10);
        $user->setNote(5);

        $service = new UserService();
        $errors = $service->validate($user);
        $this->assertContains('Le pseudo doit contenir au moins 3 caractères', $errors);
    }

    function testValidateReturnsErrorIfEmailIsInvalid(): void
    {
        $user = new UserEntity();
        $user->setPseudo('TestPseudo');
        $user->setEmail('bidon');
        $user->setRoleId(1);
        $user->setCredits(10);
        $user->setNote(5);

        $service = new UserService();
        $errors = $service->validate($user);
        $this->assertContains('Email invalide', $errors);
    }

    function testAddCreditsIncreasesCredits(): void
    {
        $user = new UserEntity();
        $user->setCredits(100);

        $service = new UserService();
        $service->addCredits($user, 40);

        $this->assertEquals(140, $user->getCredits());
    }

    function testDebitCreditsReducesCreditsIfEnough(): void
    {
        $user = new UserEntity();
        $user->setCredits(100);

        $service = new UserService();
        $result = $service->debitCredits($user, 70);

        $this->assertTrue($result);
        $this->assertEquals(30, $user->getCredits());
    }

    function testDebitCreditsFailsIfNotEnough(): void
    {
        $user = new UserEntity();
        $user->setCredits(20);

        $service = new UserService();
        $result = $service->debitCredits($user, 30);

        $this->assertFalse($result);
        $this->assertEquals(20, $user->getCredits());
    }

    function testUpdateNoteRoundsAndLimitsNote(): void
    {
        $user = new UserEntity();
        $user->setNote(0);

        $service = new UserService();
        $service->updateNote($user, 4.876);
        $this->assertEquals(4.88, $user->getNote());

        $service->updateNote($user, 6); // Out of range
        $this->assertEquals(4.88, $user->getNote());
    }

    function testToArrayIncludesPseudoAndEmail(): void
    {
        $user = new UserEntity();
        $user->setId(1);
        $user->setPseudo('Bob Martin');
        $user->setEmail('bob@email.fr');
        $user->setRoleId(1);
        $user->setCredits(50);
        $user->setNote(5);

        $service = new UserService();
        $arr = $service->toArray($user);

        $this->assertSame('Bob Martin', $arr['pseudo']);
        $this->assertSame('bob@email.fr', $arr['email']);
        $this->assertSame(1, $arr['role_id']);
    }

    public function testDebitCreditsSucceedsWhenExactBalance(): void
    {
        $user = new UserEntity();
        $user->setCredits(50);

        $service = new UserService();
        $result = $service->debitCredits($user, 50);

        $this->assertTrue($result);
        $this->assertEquals(0, $user->getCredits());
    }
}
