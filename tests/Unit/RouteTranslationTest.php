<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RouteTranslationTest extends TestCase
{
    public function test_every_grouped_route_message_exists_in_each_supported_locale(): void
    {
        $routes = file_get_contents(__DIR__.'/../../routes/web.php');
        preg_match_all("/__\\(\\s*['\"]messages\\.([^'\"]+)['\"]\\s*\\)/", $routes, $matches);

        $keys = array_values(array_unique($matches[1]));
        $this->assertNotEmpty($keys);

        foreach (['en', 'ms'] as $locale) {
            $messages = require __DIR__."/../../lang/{$locale}/messages.php";

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $messages, "Missing {$locale} route translation: messages.{$key}");
                $this->assertNotSame('', trim((string) $messages[$key]), "Empty {$locale} route translation: messages.{$key}");
            }
        }
    }
}
