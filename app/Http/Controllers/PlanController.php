<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    public function Plans(){
        return Plan::all();
    }

    public function QueryBalanceUser(string $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        
        $userPlan = UserPlan::where('user_id', $user->id)->first();
        if (!$userPlan) {
            return response()->json(['message' => 'Plano do usuário não encontrado'], 404);
        }

        return response()->json([
            'Information' => [
                'Balance' => $userPlan->whatsapp_queries_remaining,
            ]
        ]);
    }
    public function purchasePlan(Request $request)
    {
        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        UserPlan::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'whatsapp_queries_remaining' => 0,
        ]);

        return response()->json(['message' => 'Plano adquirido com sucesso!']);
    }


    public function addWhatsappQueries(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $userPlan = UserPlan::where('user_id', $validatedData['user_id'])->firstOrFail();
        $plan = $userPlan->plan;

        if (!$plan) {
            return response()->json(['message' => 'Plano não encontrado para o usuário.'], 404);
        }

        $amount = $validatedData['amount'];
        $additionalQueries = ($amount / $plan->price_per_100k) * 100000;
        $userPlan->whatsapp_queries_remaining += $additionalQueries;
        $userPlan->save();

        return response()->json(['message' => 'Créditos adicionados com sucesso!']);
    }

    public function queryPortability(Request $request)
    {
        // Implementar a lógica de consulta de portabilidade
    }

    public function queryWhatsapp(Request $request)
    {
        $user = $request->user();
        $userPlan = UserPlan::where('user_id', $user->id)->firstOrFail();

        if ($userPlan->deductWhatsappQueries($request->query_count)) {
            // Implementar a lógica de consulta de WhatsApp
            return response()->json(['message' => 'Consulta realizada com sucesso!']);
        }

        return response()->json(['message' => 'Créditos insuficientes!'], 400);
    }
}
