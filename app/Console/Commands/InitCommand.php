<?php

namespace App\Console\Commands;

use App\Services\CryptoService;
use App\Utils\HashId;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Command\Command as CommandAlias;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $priKeyHex = CryptoService::generatePriKey();
            $systemWallet = config('app.system_wallet');
            $pubKeyHex = CryptoService::getPubKeyHex($priKeyHex);
            $pubKey = CryptoService::getPubKey($priKeyHex);
            $content = str_replace(
                [
                    'SYSTEM_WALLET_PRI_KEY=' . $systemWallet['pri_key'],
                    'SYSTEM_WALLET_PUB_KEY=' . $systemWallet['pub_key'],
                    'SYSTEM_WALLET_ADDRESS=' . $systemWallet['address'],
                    'HASH_ID_ALPHABET=' . env('HASH_ID_ALPHABET'),

                ],
                [
                    'SYSTEM_WALLET_PRI_KEY=' . $priKeyHex,
                    'SYSTEM_WALLET_PUB_KEY=' . $pubKeyHex,
                    'SYSTEM_WALLET_ADDRESS=' . CryptoService::pubKeyToAddress($pubKey['x'], $pubKey['y']),
                    'HASH_ID_ALPHABET=' . HashId::getAlphabet(),
                ],
                file_get_contents($path)
            );
            file_put_contents($path, $content);
        }
        Cache::flush();
        return CommandAlias::SUCCESS;
    }
}
