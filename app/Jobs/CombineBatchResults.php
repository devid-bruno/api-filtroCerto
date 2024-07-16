<?php

namespace App\Jobs;
ini_set('memory_limit', '256M');

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class CombineBatchResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchCount;
    protected $userId;
    protected $uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $batchCount, int $userId, string $uuid)
    {
        $this->batchCount = $batchCount;
        $this->userId = $userId;
        $this->uuid = $uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $directory = 'responses/' . $this->userId;
        $allResults = [];

        try {
            // Combinar os resultados de todos os batches
            for ($i = 1; $i <= $this->batchCount; $i++) {
                $filename = $directory . '/' . $this->uuid . '_batch_' . $i . '.txt';
                if (Storage::exists($filename)) {
                    $batchResults = json_decode(Storage::get($filename), true);
                    $allResults = array_merge($allResults, $batchResults);
                    // Excluir arquivos individuais do batch
                    Storage::delete($filename);
                }
            }

            // Salvar o resultado combinado
            $finalFilename = $directory . '/' . $this->uuid . '.txt';
            Storage::put($finalFilename, json_encode($allResults, JSON_PRETTY_PRINT));

            // Atualizar status do upload no banco de dados
            $upload = Upload::where('uuid', $this->uuid)->first();
            if ($upload) {
                $upload->status = 'completed';
                $upload->save();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao combinar resultados dos batches: ' . $e->getMessage());
        }
    }
}
