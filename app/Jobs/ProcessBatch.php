<?php

namespace App\Jobs;
ini_set('memory_limit', '256M');

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProcessBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batch;
    protected $batchNumber;
    protected $userId;
    protected $uuid;
    protected $batchCount;

    public function __construct(array $batch, int $batchNumber, int $userId, string $uuid, int $batchCount)
    {
        $this->batch = $batch;
        $this->batchNumber = $batchNumber;
        $this->userId = $userId;
        $this->uuid = $uuid;
        $this->batchCount = $batchCount;
    }

    public function handle()
    {
        Log::info('ProcessBatch job started for user: ' . $this->userId);

        $numeros = json_encode($this->batch);
        $url = 'https://consultas.portabilidadecelular.com/painel/consulta_numero_json.php';
        $queryParams = [
            'user' => env('API_USER'),
            'pass' => env('API_PASSWORD'),
            'numeros' => $numeros
        ];
        
        $response = Http::get($url, $queryParams);

        if ($response->successful()) {
            $data = $response->json();
            $filename = 'responses/' . $this->userId . '/' . $this->uuid . '_batch_' . $this->batchNumber . '.txt';
            Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT));
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
