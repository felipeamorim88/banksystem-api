<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FinancialMovementStatus;
use Illuminate\Http\Request;
use App\Models\FinancialMovement;


class FinancialMovementController extends Controller
{

    public function GetByUser(string $user_id)
    {
        return FinancialMovement::where(['user_id' => $user_id])->get();
    }
    public function GetByAdmin()
    {
        return Response(FinancialMovement::all(), 200);
    }
    public function GetPending()
    {
        return Response(FinancialMovement::where(['status' => FinancialMovementStatus::PENDING])->get(), 200);
    }
    public function Purchasing(Request $request)
    {
        $acc = Account::find($request->id);

        if ($acc != null) {

            if (($acc->balance - $request->value) < 0)
                return Response(['status' => 'fail', 'message' => 'insufficient funds'], 422);

            FinancialMovement::create([
                'user_id' => $request->id,
                'previous_value' => $acc->balance,
                'value' => $request->value,
                'status' => FinancialMovementStatus::ACCEPTED,
                'transaction_id' => bin2hex(random_bytes(16))
            ]);

            $acc->balance -= $request->value;
            $acc->save();
            return Response(['status' => 'success', 'message' => 'purchasing complete'], 201);
        }
        
        return Response(['status' => 'fail', 'message' => 'account not found'], 404);

    }

}