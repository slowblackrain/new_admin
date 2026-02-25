<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupAbandonedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cleanup-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up step 0 (pending) orders older than 2 hours and restores stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of abandoned orders...');

        // Find orders created more than 2 hours ago that are still step 0
        $abandonedOrders = Order::where('step', 0)
            ->where('regist_date', '<', now()->subHours(2))
            ->get();

        if ($abandonedOrders->isEmpty()) {
            $this->info('No abandoned orders found. Exiting.');
            return 0;
        }

        $count = 0;
        foreach ($abandonedOrders as $order) {
            DB::beginTransaction();
            try {
                // Restore Stock
                foreach ($order->items as $item) {
                    $ea = $item->options->sum('ea');
                    if ($ea <= 0) continue;

                    $goods = $item->goods;
                    if (!$goods) continue;
                    
                    if ($goods->package_yn == 'y') {
                        // TODO: Set Product return logic if needed, skipping for now basic items
                    } else {
                        // Return Provider Stock
                        DB::table('fm_goods_supply')
                            ->where('goods_seq', $goods->goods_seq)
                            ->increment('stock', $ea);
                            
                        // Return Option Stock (if applicable)
                        if ($item->options->first() && $item->options->first()->option1) {
                            $opt = $item->options->first();
                            DB::table('fm_goods_option')
                                ->where('goods_seq', $goods->goods_seq)
                                ->where('option1', $opt->option1)
                                ->where('option2', $opt->option2 ?? '')
                                ->where('option3', $opt->option3 ?? '')
                                ->where('option4', $opt->option4 ?? '')
                                ->where('option5', $opt->option5 ?? '')
                                ->increment('stock', $ea);
                        }
                            
                        // Return Total Stock
                        DB::table('fm_goods')
                            ->where('goods_seq', $goods->goods_seq)
                            ->increment('tot_stock', $ea);
                    }
                }
                
                // Delete the order and related records
                $order->items()->delete();
                $order->delete();
                
                DB::commit();
                $count++;
                $this->info("Abandoned Order {$order->order_seq} cleaned up.");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to clean up abandoned order {$order->order_seq}: " . $e->getMessage());
                $this->error("Failed to clean up abandoned order {$order->order_seq}. Check logs.");
            }
        }

        $this->info("Cleanup finished. Total $count orders cleaned.");
        return 0;
    }
}
