<?php
namespace Tests\Unit;
use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
class HandlerTest extends TestCase
{
   protected $handler;
   protected $request;
    protected function setUp(): void
   {
       parent::setUp();
       $this->handler = app(Handler::class);
       $this->request = Request::create('/api/test', 'GET');
   }
    /** @test */
   public function it_handles_unauthenticated_exception()
   {
       $exception = new AuthenticationException('Unauthenticated');
       // Gunakan render() sebagai gantinya karena ini public method
       $response = $this->handler->render($this->request, $exception);
        $this->assertEquals(401, $response->getStatusCode());
       $this->assertEquals(['error' => 'Unauthenticated'], json_decode($response->getContent(), true));
   }

 
}