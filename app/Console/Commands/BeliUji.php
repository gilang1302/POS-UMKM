<?php

namespace App\Console\Commands;

use App\Exceptions\StokException;
use App\Services\TransactionService;
use Illuminate\Console\Command;

// dipanggil oleh stok:uji-balapan, satu proses = satu kasir yang membeli 1 pcs
class BeliUji extends Command
{
    protected $signature = 'stok:beli-uji {product} {branch} {user}';

    protected $description = 'Dipakai internal oleh stok:uji-balapan';

    protected $hidden = true;

    public function handle()
    {
        try {
            TransactionService::createSale(
                $this->argument('user'),
                $this->argument('branch'),
                [['product_id' => $this->argument('product'), 'qty' => 1]],
                'cash',
                1000000
            );

            $this->line('OK transaksi tersimpan');
        } catch (StokException $e) {
            $this->line('DITOLAK ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->line('ERROR ' . $e->getMessage());
        }

        return self::SUCCESS;
    }
}
