<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CombineBatchResults;
use App\Jobs\ProcessBatch;
use App\Jobs\CombineBatchResultsWpp;
use App\Jobs\ProcessBatchWpp;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\UserPlan;
use App\Models\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CarrierFilterController extends Controller
{

    public function filterCarrier(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $telefones = [];

                $contents = file($file);
                foreach ($contents as $line) {
                    $telefone = trim($line);
                    if (!empty($telefone)) {
                        $telefones[] = $telefone;
                    }
                }

                $batches = array_chunk($telefones, 400);
                $batchCount = count($batches);
                $userId = Auth::id();
                $uuid = Str::uuid()->toString();

                Upload::create([
                    'user_id' => $userId,
                    'uuid' => $uuid,
                    'status' => 'processing',
                    'upload_type_id' => UploadType::TIPO_PORTABILIDADE
                ]);

                foreach ($batches as $index => $batch) {
                    ProcessBatch::dispatch($batch, $index + 1, $userId, $uuid, $batchCount);
                }

                CombineBatchResults::dispatch($batchCount, $userId, $uuid);

                return response()->json(['message' => 'O processamento foi iniciado. Você será notificado quando estiver concluído.', 'uuid' => $uuid]);
            } else {
                return response()->json(['error' => 'Arquivo não encontrado.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }


    public function whatsappemLote(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'Arquivo não encontrado.'], 400);
            }

            $file = $request->file('file');
            $telefones = [];

            $contents = file($file);
            foreach ($contents as $line) {
                $telefone = trim($line);
                if (!empty($telefone)) {
                    $telefones[] = $telefone;
                }
            }

            if (empty($telefones)) {
                return response()->json(['error' => 'Nenhum telefone encontrado no arquivo.'], 400);
            }

            $batches = array_chunk($telefones, 400);
            $batchCount = count($batches);
            $totalQueries = count($telefones);
            $userId = Auth::id();

            // Verificar o saldo do usuário
            $userPlan = UserPlan::where('user_id', $userId)->firstOrFail();
            if ($userPlan->whatsapp_queries_remaining < $totalQueries) {
                return response()->json(['error' => 'Saldo insuficiente para realizar a consulta.'], 400);
            }

            // Deduzir o saldo
            $userPlan->whatsapp_queries_remaining -= $totalQueries;
            $userPlan->save();

            $uuid = Str::uuid()->toString();

            Upload::create([
                'user_id' => $userId,
                'uuid' => $uuid,
                'status' => 'processing',
                'upload_type_id' => UploadType::TIPO_WHATSAPP
            ]);

            foreach ($batches as $index => $batch) {
                ProcessBatchWpp::dispatch($batch, $index + 1, $userId, $uuid, $batchCount);
            }

            CombineBatchResultsWpp::dispatch($batchCount, $userId, $uuid);

            return response()->json(['message' => 'O processamento foi iniciado. Você será notificado quando estiver concluído.', 'uuid' => $uuid]);

        } catch (\Exception $e) {
            Log::error('Erro no processamento do arquivo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }


    public function userFiles()
    {
        $userId = Auth::id();
        $uploads = Upload::where('user_id', $userId)->get();

        return response()->json(['uploads' => $uploads]);
    }

    public function downloadForUUID($uuid)
    {
        $filePath = 'responses/' . auth()->id() . '/' . $uuid . '.txt';
        if (Storage::exists($filePath)) {
            return response()->download(storage_path('app/' . $filePath));
        } else {
            return response()->json(['error' => 'Arquivo não encontrado.'], 404);
        }
    }

    public function deleteFileForUUID($uuid)
    {
        $filePath = 'responses/' . auth()->id() . '/' . $uuid . '.txt';
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            Upload::where('uuid', $uuid)->delete();

            return response()->json(['message' => 'Arquivo excluído com sucesso.']);
        } else {
            return response()->json(['error' => 'Arquivo não encontrado.'], 404);
        }
    }

    public function filterWpp(string $telefones)
    {
        try {
            $user = Auth::user();
            $userPlan = UserPlan::where('user_id', $user->id)->firstOrFail();

            if ($userPlan->whatsapp_queries_remaining < 1) {
                return response()->json(['error' => 'Saldo insuficiente para realizar a consulta.'], 400);
            }

            $url = 'http://central-valida.portabilidadecelular.com/painel/consulta_whatsapp_json.php';

            $numerosArray = explode(',', $telefones);
            $numerosString = '[' . implode(',', $numerosArray) . ']';

            $queryParams = [
                'user' => 'joaodedeus',
                'pass' => 'Jovem153',
                'numeros' => $numerosString
            ];

            $response = Http::get($url, $queryParams);

            if ($response->successful()) {
                $responseData = $response->json();

                $userPlan->whatsapp_queries_remaining -= 1;
                $userPlan->save();

                Query::create([
                    'user_id' => $user->id,
                    'type' => 'whatsapp',
                    'query_count' => 1
                ]);

                return response()->json(['data' => $responseData]);
            } else {
                return response()->json(['error' => 'Erro ' . $response->status() . ': ' . $response->body()], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }




    public function filterPortabilidade(string $telefone)
    {
        try {
            $url = 'https://consultas.portabilidadecelular.com/painel/consulta_numero.php?user=joaodedeus&pass=Jovem153&search_number=' . $telefone . '&completo';

            $response = Http::get($url);
            if ($response->successful()) {
                $parts = explode('|', $response->body());

                $data = [
                    'data' => [
                        'codigo_operadora' => $parts[0],
                        'portabilidade' => $parts[1] === '1' ? true : false,
                        'data_portabilidade' => $parts[2],
                        'nome_operadora' => $parts[3]
                    ],

                ];
                return response()->json($data);
            } else {
                return response()->json(['error' => 'Erro ' . $response->status() . ': ' . $response->body()], $response->status());
            }
        } catch (\Exception  $e) {
            return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function importPhoneList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
            'phone' => 'required|string',
            'interval' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $fileName = $request->file('file')->getClientOriginalName();
        $phone = $request->input('phone');
        $interval = $request->input('interval');
        $uuid = Str::uuid()->toString();

        $content = file($file->getRealPath());
        
        $lineCount = count($content);
        $newContent = [];

        for ($i = 0; $i < $lineCount; $i++) {
            $newContent[] = $content[$i];
            if (($i + 1) % $interval == 0) {
                $newContent[] = $phone . PHP_EOL;
            }
        }

        $userId = Auth::id();

        Upload::create([
            'user_id' => $userId,
            'uuid' => $uuid,
            'status' => 'completed',
            'upload_type_id' => UploadType::TIPO_INCLUSAO
        ]);

        $newFileName = 'responses/' . $userId . '/' . $uuid . '.txt';
        Storage::put($newFileName, implode('', $newContent));

        return response()->json(['message' => 'File processed successfully', 'file' => Storage::url($newFileName)], 200);
    }
};
