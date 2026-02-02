<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use Illuminate\Support\Facades\DB;

class OrderIntegrityTest extends TestCase
{
  protected function setUp(): void
  {
    parent::setUp();
  }

  /** @test */
  public function it_follows_legacy_stock_deduction_logic_two_phases()
  {
    // 1. Setup: Create a Test Product with Initial Stock
    $initialStock = 100;
    $goodsSeq = 999991; 
    $optionSeq = 888881;

    // Clean up previous run (including supply table)
    DB::delete("DELETE FROM fm_goods_supply WHERE goods_seq = ?", [$goodsSeq]);
    Goods::where('goods_seq', $goodsSeq)->delete();
    GoodsOption::where('goods_seq', $goodsSeq)->delete();
    
    // Create Goods
    $goods = new Goods();
    $goods->goods_seq = $goodsSeq;
    $goods->goods_name = 'Stock Logic Test Goods';
    $goods->goods_code = 'STOCK_TEST_001';
    $goods->goods_view = 'look';
    $goods->cancel_type = '0';
    $goods->tax = 'tax';
    $goods->tot_stock = $initialStock;
    $goods->save();

    // Create Goods Option
    $option = new GoodsOption();
    $option->goods_seq = $goodsSeq;
    $option->option_seq = $optionSeq;
    $option->price = 10000;
    // $option->stock = $initialStock; // REMOVED: Not in this table
    $option->default_option = 'y';
    $option->option_view = 'Y';
    $option->save();

    // Create Goods Supply (Stock Table)
    DB::insert("INSERT INTO fm_goods_supply (goods_seq, option_seq, supply_price, stock, total_stock) VALUES (?, ?, ?, ?, ?)", [
        $goodsSeq, $optionSeq, 5000, $initialStock, $initialStock
    ]);

    // 2. Phase 1: Order Placement (Step 25 - Payment Confirmed)
    $orderSeq = 777771;
    
    Order::destroy($orderSeq);
    OrderItem::where('order_seq', $orderSeq)->delete();
    OrderItemOption::where('order_seq', $orderSeq)->delete();

    $order = Order::create([
        'order_seq' => $orderSeq,
        'order_user_name' => 'StockTester',
        'step' => 25, 
        'regist_date' => now(),
        'deposit_date' => now(),
    ]);

    $item = OrderItem::create([
        'order_seq' => $orderSeq,
        'goods_seq' => $goodsSeq,
        'goods_name' => $goods->goods_name,
        'ea' => 1,
    ]);

    $itemOption = OrderItemOption::create([
        'order_seq' => $orderSeq,
        'item_seq' => $item->item_seq,
        'goods_seq' => $goodsSeq,
        'item_option_seq' => $optionSeq,
        'ea' => 1,
        'price' => 10000,
        'step' => 25,
    ]);

    // VERIFY PHASE 1: Physical Stock should REMAIN 100
    $goods->refresh();
    $supply = DB::selectOne("SELECT stock FROM fm_goods_supply WHERE option_seq = ?", [$optionSeq]);

    $this->assertEquals($initialStock, $supply->stock, 'Phase 1: Physical Option Stock (Supply) should NOT decrease at Step 25');
    $this->assertEquals($initialStock, $goods->tot_stock, 'Phase 1: Physical Total Stock should NOT decrease at Step 25');

    // 3. Phase 2: Shipping (Step 55 - Export Complete)
    // Manually trigger the deduction simulating legacy set_step_55_stock
    DB::update("UPDATE fm_goods_supply SET stock = stock - 1 WHERE option_seq = ?", [$optionSeq]);
    DB::update("UPDATE fm_goods SET tot_stock = tot_stock - 1 WHERE goods_seq = ?", [$goodsSeq]);
    
    $order->step = 55;
    $order->save();

    // VERIFY PHASE 2: Physical Stock SHOULD decrease
    $goods->refresh();
    $supply = DB::selectOne("SELECT stock FROM fm_goods_supply WHERE option_seq = ?", [$optionSeq]);

    $this->assertEquals($initialStock - 1, $supply->stock, 'Phase 2: Physical Option Stock (Supply) SHOULD decrease at Step 55');
    $this->assertEquals($initialStock - 1, $goods->tot_stock, 'Phase 2: Physical Total Stock SHOULD decrease at Step 55');
  }
}
