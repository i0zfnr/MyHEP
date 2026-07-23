<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAdminScope;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StudentAffairsHeadRoleTest extends TestCase
{
    public function test_student_affairs_head_can_access_both_operational_modules_but_not_system_scope(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('role');
        });

        DB::table('admins')->insert([
            'id' => 1,
            'role' => 'student_affairs_head',
        ]);

        $session = app('session')->driver();
        $session->put('auth_user', [
            'id' => 1,
            'role' => 'admin',
            'admin_role' => 'student_affairs_head',
            'name' => 'Ketua HEP',
        ]);

        $request = Request::create('/admin/test');
        $request->setLaravelSession($session);
        $middleware = app(EnsureAdminScope::class);
        $next = fn () => new Response('allowed');

        foreach (['scholarship', 'discipline', 'students', 'movement', 'backoffice'] as $scope) {
            $this->assertSame(200, $middleware->handle($request, $next, $scope)->getStatusCode());
        }

        try {
            $middleware->handle($request, $next, 'system');
            $this->fail('The student affairs head must not have system access.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
