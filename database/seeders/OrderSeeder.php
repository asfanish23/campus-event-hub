<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = [
            ['order_id' => 'ORD001', 'product_id' => 1, 'customer_name' => 'Ahmad Zaki', 'quantity' => 2, 'total' => 90.00, 'payment_method' => 'Online', 'status' => 'Pending', 'date' => '2024-12-09'],
            ['order_id' => 'ORD002', 'product_id' => 2, 'customer_name' => 'Sarah Lee', 'quantity' => 1, 'total' => 85.00, 'payment_method' => 'Online', 'status' => 'Processing', 'date' => '2024-12-09'],
            ['order_id' => 'ORD003', 'product_id' => 3, 'customer_name' => 'Michael Chen', 'quantity' => 3, 'total' => 45.00, 'payment_method' => 'Cash', 'status' => 'Shipped', 'date' => '2024-12-08'],
            ['order_id' => 'ORD004', 'product_id' => 4, 'customer_name' => 'Fatimah Ali', 'quantity' => 1, 'total' => 25.00, 'payment_method' => 'Online', 'status' => 'Completed', 'date' => '2024-12-08'],
            ['order_id' => 'ORD005', 'product_id' => 1, 'customer_name' => 'David Wong', 'quantity' => 1, 'total' => 45.00, 'payment_method' => 'Online', 'status' => 'Cancelled', 'date' => '2024-12-07'],
            ['order_id' => 'ORD006', 'product_id' => 2, 'customer_name' => 'Emma Johnson', 'quantity' => 2, 'total' => 170.00, 'payment_method' => 'Cash', 'status' => 'Completed', 'date' => '2024-12-07'],
            ['order_id' => 'ORD007', 'product_id' => 3, 'customer_name' => 'Ravi Patel', 'quantity' => 5, 'total' => 75.00, 'payment_method' => 'Online', 'status' => 'Completed', 'date' => '2024-12-06'],
            ['order_id' => 'ORD008', 'product_id' => 4, 'customer_name' => 'Lisa Wong', 'quantity' => 2, 'total' => 50.00, 'payment_method' => 'Online', 'status' => 'Completed', 'date' => '2024-12-06'],
            ['order_id' => 'ORD009', 'product_id' => 1, 'customer_name' => 'Marcus Bell', 'quantity' => 3, 'total' => 135.00, 'payment_method' => 'Cash', 'status' => 'Completed', 'date' => '2024-12-05'],
            ['order_id' => 'ORD010', 'product_id' => 2, 'customer_name' => 'Zainab Hassan', 'quantity' => 1, 'total' => 85.00, 'payment_method' => 'Online', 'status' => 'Completed', 'date' => '2024-12-05'],
            ['order_id' => 'ORD011', 'product_id' => 3, 'customer_name' => 'Tom Wilson', 'quantity' => 8, 'total' => 120.00, 'payment_method' => 'Online', 'status' => 'Completed', 'date' => '2024-12-04'],
            ['order_id' => 'ORD012', 'product_id' => 4, 'customer_name' => 'Olivia Grant', 'quantity' => 4, 'total' => 100.00, 'payment_method' => 'Cash', 'status' => 'Completed', 'date' => '2024-12-04'],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }
    }
}
