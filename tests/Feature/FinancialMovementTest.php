<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FinancialMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Faker\Generator as Faker;

class FinancialMovementTest extends TestCase
{
    use WithFaker;

    /**
     * Test Auth user profile data
     *
     * @return void
     */
    public function testUserMovementById()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user->first());
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('api/financial-movement/user/1');
        $user->delete();
        $response
            ->assertStatus(200);
    }

    public function testUserMovementByAdmin()
    {
        $user = User::factory()->create(['type' => 1]);
        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('api/financial-movement');
        $user->delete();
        $response
            ->assertStatus(200);
    }

    public function testUserMovementPending()
    {
        $user = User::factory()->create(['type' => 1]);
        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('api/financial-movement/pending');
        $user->delete();
        $response
            ->assertStatus(200);
    }

    public function testUserMovementReject()
    {
        $user = User::factory()->create(['type' => 1]);
        $fm = FinancialMovement::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($user->id == $fm->user_id);

        $acc = Account::factory()->create(['username' => $user->name, 'password'=>$user->password]);
        $this->assertTrue($acc->username == $user->name);

        $us = User::find($fm->user_id)->first();
        $acc = Account::where(['username'=>$user->name])->first();

        $this->assertTrue($us != null);
        $this->assertTrue($acc != null);


        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("api/account/transaction/reject/{$fm->id}");
        $user->delete();
        $fm->delete();
        $acc->delete();
        $response
            ->assertStatus(200);
    }

    public function testUserMovementApccept()
    {
        $user = User::factory()->create(['type' => 1]);
        $fm = FinancialMovement::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($user->id == $fm->user_id);

        $acc = Account::factory()->create(['username' => $user->name, 'password'=>$user->password]);
        $this->assertTrue($acc->username == $user->name);

        $us = User::find($fm->user_id)->first();
        $acc = Account::where(['username'=>$user->name])->first();

        $this->assertTrue($us != null);
        $this->assertTrue($acc != null);


        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("api/account/transaction/accept/{$fm->id}");
        $user->delete();
        $fm->delete();
        $acc->delete();
        $response
            ->assertStatus(200);
    }
    public function testPurchasing()
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['username'=>$user->name,'password'=>$user->password,'balance'=>10]);
        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/purchasing/',['id'=>$acc->id,'value'=>10]);
        $user->delete();
        $acc->delete();
        $response
            ->assertStatus(201);
    }
    public function testPurchasingInsuficientFunds()
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['username'=>$user->name,'password'=>$user->password,'balance'=>10]);
        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/purchasing/',['id'=>$acc->id,'value'=>20]);
        $user->delete();
        $acc->delete();
        $response
            ->assertStatus(422);
    }
    public function testPurchasingFail()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/purchasing/',['id'=>0,'value'=>20]);
        $user->delete();
        $response
            ->assertStatus(404);
    }
}