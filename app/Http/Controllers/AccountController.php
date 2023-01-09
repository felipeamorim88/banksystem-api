<?php

namespace App\Http\Controllers;

use App\Models\FinancialMovement;
use App\Models\FinancialMovementStatus;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
class AccountController extends Controller
{

    public function CheckAccount(Request $request)
    {
        $acc = Account::where('username', $request->username)->first();
        if ($acc == null)
            return Response(['status' => 'fail', 'message' => 'not found'], 404);

        $hashedPassword = $acc->password;

        if (!Hash::check($request->password, $hashedPassword)) {
            return Response(['status' => 'fail', 'message' => 'unauthorized'], 403);

        }
        return Response(['status' => 'success', 'message' => 'validated', 'balance' => $acc->first()->balance], 200);
    }

    public function Deposit(Request $request)
    {
        //TODO: convert to uploado to Google storage
        $image = base64_encode(file_get_contents($request->file('image')));
        $acc = Account::find($request->id);
        if ($request->image == null) {
            FinancialMovement::create([
                'user_id' => $request->id,
                'previous_value' => $acc->balance,
                'value' => $request->value,
                'status' => FinancialMovementStatus::REJECTED,
            ]);
            return Response(['status' => 'fail', 'message' => 'upload an image'], 422);
        }
        FinancialMovement::create([
            'user_id' => $request->id,
            'previous_value' => $acc->balance,
            'value' => $request->value,
            'status' => FinancialMovementStatus::PENDING,
            'image_base64' => $image

        ]);

        return Response(['status' => FinancialMovementStatus::PENDING], 200);
    }

    public function Accept(string $id)
    {
        $fm = FinancialMovement::find($id);
        $us = User::find($fm->user_id)->first();
        if ($fm != null) {
            $acc = Account::where(['username' => $us->name])->first();
            $fm->status = FinancialMovementStatus::ACCEPTED;
            $fm->save();

            $acc->balance += $fm->value;
            $acc->save();

            return Response(['status' => FinancialMovementStatus::ACCEPTED, 'balance' => $acc->balance], 200);
        }
        return Response(['status' => FinancialMovementStatus::PENDING, 'message' => 'not found'], 404);
    }

    public function Reject(string $id)
    {
        $fm = FinancialMovement::find($id);
        $us = User::find($fm->user_id)->first();

        if ($fm != null) {
            $acc = Account::where(['username' => $us->name])->first();

            if ($acc == null)
                return Response(['status' => 'fail', 'message' => 'account not found'], 404);

            $fm->status = FinancialMovementStatus::REJECTED;
            $fm->save();
            return Response(['status' => FinancialMovementStatus::REJECTED, 'balance' => $acc->balance], 200);
        }

    }

    public function Post(Request $request)
    {
        $id = auth('api')->user()->id;
        $inserted = Account::create([
            'balance' => 0,
            'username' => $request->user()->name,
            'password' => Hash::make($request->password),
            'user_id'=> $id
        ]);
        return Response([
            'status' => 'success',
            'message' => 'created',
            'account_id' => $inserted->id
        ], 201);
    }
}