<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccessControlFunctionalTest extends WebTestCase
{
    public function testAnonymousUserIsRedirectedFromValidatorPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/validator/excuses');

        self::assertResponseRedirects('/login');
    }

    public function testAnonymousUserCannotCreateExcuseThroughApi(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/v1/excuses',
            server: ['CONTENT_TYPE' => 'application/ld+json'],
            content: json_encode([
                'type' => 'classic',
                'title' => 'Tentative anonyme',
                'content' => 'Je ne suis pas authentifie.',
                'urgencyLevel' => 1,
                'categoryId' => 1,
                'contextId' => 1,
                'toneId' => 1,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertContains($client->getResponse()->getStatusCode(), [302, 401, 403]);
    }

    public function testAnonymousUserCannotAccessAdminOnlyEntitiesApi(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/entities/excuses');

        self::assertContains($client->getResponse()->getStatusCode(), [302, 401, 403]);
    }
}


