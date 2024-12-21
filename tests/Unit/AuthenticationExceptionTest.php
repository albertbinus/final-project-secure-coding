<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Exceptions\AuthenticationException;

class AuthenticationExceptionTest extends TestCase
{
    /** @test */
    public function it_renders_correct_json_response()
    {
        // Arrange
        $errorMessage = 'Test authentication error';
        $exception = new AuthenticationException($errorMessage);

        // Act
        $response = $exception->render();

        // Assert
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(['error' => $errorMessage], json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_preserves_custom_error_message()
    {
        // Arrange
        $errorMessage = 'Custom authentication error message';
        $exception = new AuthenticationException($errorMessage);

        // Act
        $response = $exception->render();
        $responseData = json_decode($response->getContent(), true);

        // Assert
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals($errorMessage, $responseData['error']);
    }
} 