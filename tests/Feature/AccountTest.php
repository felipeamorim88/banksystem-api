<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FinancialMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Generator as Faker;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Str;

class AccountTest extends TestCase
{
    use WithFaker;

    /**
     * Test Auth user profile data
     *
     * @return void
     */
    public function testCreate()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user->first());
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/account', ['password' => $user->password]);
        $user->delete();
        $response
            ->assertStatus(201);
    }

    public function testDeposit()
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['username' => $user->name, 'password' => $user->password]);

        $token = auth('api')->login($user->first());
        $local_file = getenv("HOME") . 'tests/TestFiles/test-file.png';
        $uploadedFile = new UploadedFile(
            $local_file,
            'dep.jpg',
            'image/jpeg',
            null
        );
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/account/deposit', ['image' => $uploadedFile, 'id' => $acc->id, 'value' => 10]);
        $user->delete();
        $acc->delete();
        $response
            ->assertStatus(200);
    }
    public function testDepositFail()
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['username' => $user->name, 'password' => $user->password]);

        $token = auth('api')->login($user->first());
        $local_file = getenv("HOME") . 'tests/TestFiles/test-file.png';
        $uploadedFile = new UploadedFile(
            $local_file,
            'dep.jpg',
            'image/jpeg',
            null
        );
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/account/deposit', [ 'id' => $acc->id, 'value' => 10]);
        $user->delete();
        $acc->delete();
        $response
            ->assertStatus(422);
    }

    public function testCheckAccount()
    {
        $accPass = Str::random(10);
        $user = User::factory()->create(['password' => $accPass]);

        $token = auth('api')->login($user->first());

        $createResponse = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/account', ['password' => $user->password]);
        $createResponse
            ->assertStatus(201);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/account/verification', ['username' => $user->name, 'password' => $accPass]);

        $response
            ->assertStatus(200);

        $user->delete();
        Account::where(['username'=>$user->name])->delete();
    }
}