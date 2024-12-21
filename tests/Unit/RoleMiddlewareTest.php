<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use App\Helpers\PublicHelper;
use Closure;
use stdClass;
use Mockery;
use Firebase\JWT\JWT;

class RoleMiddlewareTest extends TestCase
{
    protected $middleware;
    protected $request;
    protected $next;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RoleMiddleware();
        $this->next = function ($request) {
            return response()->json(['message' => 'Next middleware called']);
        };
    }

    private function generateTestToken($role = 'admin')
    {
        $secretKey = env('JWT_KEY');
        $tokenId = base64_encode(random_bytes(16));
        $issuedAt = new \DateTimeImmutable();
        $expire = $issuedAt->getTimestamp() + 3600;
       
       $data = [
           'iat' => $issuedAt->getTimestamp(),
           'jti' => $tokenId,
           'iss' => "localhost",
           'nbf' => $issuedAt->getTimestamp(),
           'exp' => $expire,
           'data' => [
               'userID' => 1,
               'role' => $role
           ]
       ];
        return JWT::encode($data, $secretKey, 'HS512');
   }
    public function test_allows_access_with_correct_role()
   {
       // Generate real JWT token
       $token = $this->generateTestToken('admin');
       
       // Create request with proper authorization header
       $this->request = Request::create('/api/test', 'GET');
       $this->request->headers->set('Authorization', 'Bearer ' . $token);
       
       // Set server variables manually
       $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        // Test middleware with admin role
       $response = $this->middleware->handle($this->request, $this->next, 'admin');
       
       // Assert response is successful
       $this->assertEquals(200, $response->status());
       $this->assertEquals(['message' => 'Next middleware called'], $response->getData(true));
   }
    public function test_denies_access_with_incorrect_role()
   {
       // Generate token with user role
       $token = $this->generateTestToken('user');
       
       // Create request with proper authorization header
       $this->request = Request::create('/api/test', 'GET');
       $this->request->headers->set('Authorization', 'Bearer ' . $token);
       
       // Set server variables manually
       $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        // Test middleware with admin role requirement
       $response = $this->middleware->handle($this->request, $this->next, 'admin');
       
       // Assert response is forbidden
       $this->assertEquals(403, $response->status());
       $this->assertEquals(['message' => 'Forbidden'], $response->getData(true));
   }
    public function test_allows_access_with_multiple_allowed_roles()
   {
       // Generate token with user role
       $token = $this->generateTestToken('user');
       
       // Create request with proper authorization header
       $this->request = Request::create('/api/test', 'GET');
       $this->request->headers->set('Authorization', 'Bearer ' . $token);
       
       // Set server variables manually
       $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        // Test middleware with multiple roles
       $response = $this->middleware->handle($this->request, $this->next, 'admin', 'user');
       
       // Assert response is successful
       $this->assertEquals(200, $response->status());
       $this->assertEquals(['message' => 'Next middleware called'], $response->getData(true));
   }
    protected function tearDown(): void
   {
       parent::tearDown();
       unset($_SERVER['HTTP_AUTHORIZATION']);
       Mockery::close();
   }
}