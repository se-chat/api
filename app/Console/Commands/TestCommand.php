<?php

namespace App\Console\Commands;

use App\Services\CryptoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $path = 'encrypt/' . date('Y/m/d').'/'.Uuid::uuid4()->toString();

        $url = Storage::disk('public')->put($path,'xrada');
        dd($url);
    }
}
