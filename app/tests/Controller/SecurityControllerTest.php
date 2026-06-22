<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testProtectedPageRedirectsAnonymousToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tag');

        self::assertResponseRedirects('/login');
    }
}
