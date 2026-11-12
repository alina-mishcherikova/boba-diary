<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testLoginForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('form.login-form.form h1', 'Please sign in');
        self::assertSelectorExists('form.login-form input[name="email"][type="email"]');
        self::assertSelectorExists('form.login-form input[name="password"][type="password"]');
        self::assertSelectorExists('form.login-form input[name="_csrf_token"]');
        self::assertSelectorExists('form.login-form input[name="_remember_me"]');
        self::assertSelectorTextContains('form.login-form button[type="submit"]', 'Sign in');
    }
}
