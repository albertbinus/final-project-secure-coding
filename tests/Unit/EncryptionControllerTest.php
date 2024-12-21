<?php
namespace Tests\Unit;
use Tests\TestCase;
use App\Http\Controllers\EncryptionController;
use Illuminate\Http\Request;
class EncryptionControllerTest extends TestCase
{
   protected $controller;
    protected function setUp(): void
   {
       parent::setUp();
       $this->controller = new EncryptionController();
       
       // Set environment variables for testing
       config([
           'app.aes_key' => 'your-32-character-key-here123456789',
           'app.aes_iv' => '1234567890123456'  // 16 karakter
       ]);
   }
    public function test_can_encrypt_data()
   {
       // Arrange
       $testData = "Hello World";
       $request = new Request();
       $request->merge(['data' => $testData]);
        // Act
       $response = $this->controller->encryptData($request);
       $result = json_decode($response->getContent(), true);
        // Assert
       $this->assertArrayHasKey('encrypted_data', $result);
       $this->assertNotEquals($testData, $result['encrypted_data']);
       $this->assertIsString($result['encrypted_data']);
   }
    public function test_encryption_is_consistent()
   {
       // Arrange
       $testData = "Test Data";
       $request = new Request();
       $request->merge(['data' => $testData]);
        // Act
       $response1 = $this->controller->encryptData($request);
       $response2 = $this->controller->encryptData($request);
        $result1 = json_decode($response1->getContent(), true);
       $result2 = json_decode($response2->getContent(), true);
        // Assert
       $this->assertEquals(
           $result1['encrypted_data'], 
           $result2['encrypted_data'],
           'Encryption should be consistent for the same input'
       );
   }
}