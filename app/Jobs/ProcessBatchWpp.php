<?php

namespace App\Jobs;
ini_set('memory_limit', '256M');

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessBatchWpp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batch;
    protected $batchNumber;
    protected $userId;
    protected $uuid;
    protected $batchCount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $batch, int $batchNumber, int $userId, string $uuid, int $batchCount)
    {
        $this->batch = $batch;
        $this->batchNumber = $batchNumber;
        $this->userId = $userId;
        $this->uuid = $uuid;
        $this->batchCount = $batchCount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        Log::info('ProcessBatchWpp job started for user: ' . $this->userId);

        $url = 'http://central-valida.portabilidadecelular.com/painel/consulta_whatsapp_json.php';
        
        $numerosString = '[' . implode(',', $this->batch) . ']';

        $queryParams = [
            'user' => env('API_USER'),
            'pass' => env('API_PASSWORD'),
            'numeros' => $numerosString
        ];

        $response = Http::get($url, $queryParams);

        if ($response->successful()) {
            $responseData = [];
            $data = $response->json();

            foreach ($this->batch as $numero) {
                $result = $data[$numero] ?? null;

                if ($result) {
                    $responseData[] = [
                        'numero_consultado' => $result['numero_requisitado'],
                        'codigo_operadora' => $result['operadora'],
                        'portabilidade' => $result['portado'] == 1,
                        'data_portabilidade' => $result['data_portabilidade'],
                        'whatsapp' => $result['whatsapp'] ?? false // Add this key if API provides it
                    ];
                }
            }

            $filename = 'responses/' . $this->userId . '/' . $this->uuid . '_batch_' . $this->batchNumber . '.txt';
            Storage::put($filename, json_encode($responseData, JSON_PRETTY_PRINT));
        } else {
            $errorData = [
                'status' => $response->status(),
                'body' => $response->body()
            ];
            $filename = 'responses/' . $this->userId . '/' . $this->uuid . '/error_' . $this->batchNumber . '.txt';
            Storage::put($filename, json_encode($errorData, JSON_PRETTY_PRINT));
        }
    }
}
