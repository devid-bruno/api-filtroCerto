<?php

namespace App\Jobs;
ini_set('memory_limit', '256M');

use App\Jobs\ProcessBatch;
use App\Jobs\CombineBatchResults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $uuid;

    public function __construct($filePath, $userId, $uuid)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->uuid = $uuid;
    }

    public function handle()
    {
        Log::info('Iniciando o processamento do arquivo no job.', ['filePath' => $this->filePath, 'uuid' => $this->uuid]);

        $telefones = [];
        $contents = file($this->filePath);

        foreach ($contents as $line) {
            $telefone = trim($line);
            if (!empty($telefone)) {
                $telefones[] = $telefone;
            }
        }

        $batches = array_chunk($telefones, 400);
        $batchCount = count($batches);

        $upload = Upload::create([
            'user_id' => $this->userId,
            'uuid' => $this->uuid,
            'status' => 'processing'
        ]);
        Log::info('Arquivo processado. Despachando batches.', ['uuid' => $this->uuid, 'batchCount' => $batchCount]);


        foreach ($batches as $index => $batch) {
            ProcessBatch::dispatch($batch, $index + 1, $this->userId, $this->uuid, $batchCount);
        }

        CombineBatchResults::dispatch($batchCount, $this->userId, $this->uuid);

        Log::info('Batches despachados.', ['uuid' => $this->uuid]);

    }
}

