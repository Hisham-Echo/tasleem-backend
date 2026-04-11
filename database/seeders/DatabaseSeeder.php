<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────
        // Admin
        $admin = DB::table('users')->updateOrInsert(
            ['email' => 'admin@tasleem.com'],
            [
                'name'       => 'Admin',
                'email'      => 'admin@tasleem.com',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $adminId = DB::table('users')->where('email', 'admin@tasleem.com')->value('id');

        // Seller demo account
        DB::table('users')->updateOrInsert(
            ['email' => 'seller@tasleem.com'],
            [
                'name'       => 'Demo Seller',
                'email'      => 'seller@tasleem.com',
                'password'   => Hash::make('seller123'),
                'role'       => 'seller',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // ── Categories ───────────────────────────────────────────────
        $categories = [
            ['name' => 'Smartphones',        'status' => '1'],
            ['name' => 'Tablets',            'status' => '1'],
            ['name' => 'Laptops',            'status' => '1'],
            ['name' => 'Desktop Computers',  'status' => '1'],
            ['name' => 'Monitors',           'status' => '1'],
            ['name' => 'Televisions',        'status' => '1'],
            ['name' => 'Cameras',            'status' => '1'],
            ['name' => 'Headphones',         'status' => '1'],
            ['name' => 'Speakers',           'status' => '1'],
            ['name' => 'Smartwatches',       'status' => '1'],
            ['name' => 'Gaming Consoles',    'status' => '1'],
            ['name' => 'Printers',           'status' => '1'],
            ['name' => 'Scanners',           'status' => '1'],
            ['name' => 'Projectors',         'status' => '1'],
            ['name' => 'Networking',         'status' => '1'],
            ['name' => 'Storage Devices',    'status' => '1'],
            ['name' => 'Memory Cards',       'status' => '1'],
            ['name' => 'Power Banks',        'status' => '1'],
            ['name' => 'Chargers',           'status' => '1'],
            ['name' => 'Cables',             'status' => '1'],
            ['name' => 'Phones',             'status' => '1'],
            ['name' => 'Film Cameras',       'status' => '1'],
            ['name' => 'Ultrabooks',         'status' => '1'],
            ['name' => 'E-Readers',          'status' => '1'],
            ['name' => 'Smart TVs',          'status' => '1'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['name' => $cat['name']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── Products (50 real items from AI dataset) ─────────────────
        $existing = DB::table('products')->count();
        if ($existing === 0) {
            DB::table('products')->insert([
            ['name'=>'iPhone 15 Pro Max','description'=>'Apple iPhone 15 Pro Max with A17 Pro chip, 256GB storage','price'=>45000.0,'category_id'=>1,'owner_id'=>$adminId,'quantity'=>10,'view_count'=>17,'rate'=>4.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Samsung Galaxy S24 Ultra','description'=>'Samsung Galaxy S24 Ultra with AI features, S Pen included','price'=>42000.0,'category_id'=>1,'owner_id'=>$adminId,'quantity'=>8,'view_count'=>13,'rate'=>4.4,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'iPad Pro 12.9-inch','description'=>'Apple iPad Pro with M2 chip, Liquid Retina XDR display','price'=>38000.0,'category_id'=>2,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>5,'rate'=>5.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'MacBook Pro 14-inch','description'=>'Apple MacBook Pro with M3 Pro chip, 512GB SSD','price'=>65000.0,'category_id'=>3,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>9,'rate'=>4.75,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sony A7 IV','description'=>'Sony A7 IV full-frame mirrorless camera, 33MP, 4K video','price'=>42000.0,'category_id'=>7,'owner_id'=>$adminId,'quantity'=>1,'view_count'=>17,'rate'=>4.33,'pay_count'=>10,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sony WH-1000XM5','description'=>'Sony WH-1000XM5 wireless noise-cancelling headphones','price'=>8500.0,'category_id'=>8,'owner_id'=>$adminId,'quantity'=>15,'view_count'=>5,'rate'=>4.5,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Apple Watch Ultra 2','description'=>'Apple Watch Ultra 2 with 49mm titanium case','price'=>18000.0,'category_id'=>10,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>9,'rate'=>4.5,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'PlayStation 5','description'=>'Sony PlayStation 5 console with 1TB storage','price'=>18000.0,'category_id'=>11,'owner_id'=>$adminId,'quantity'=>10,'view_count'=>13,'rate'=>3.83,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'LG C3 OLED 65-inch','description'=>'LG 65-inch OLED evo TV, 4K, smart TV','price'=>65000.0,'category_id'=>6,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>9,'rate'=>3.25,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'iPad Pro 12.9-inch','description'=>'Apple iPad Pro with M2 chip, Liquid Retina XDR display, 256GB','price'=>38000.0,'category_id'=>2,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>6,'rate'=>3.67,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'MacBook Pro 14-inch','description'=>'Apple MacBook Pro with M3 Pro chip, 512GB SSD, 18GB RAM','price'=>65000.0,'category_id'=>3,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>22,'rate'=>4.27,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'iMac 24-inch','description'=>'Apple iMac with M3 chip, 256GB SSD, 8GB RAM, 4.5K Retina display','price'=>55000.0,'category_id'=>4,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>4,'rate'=>4.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Mac Studio','description'=>'Apple Mac Studio with M2 Max chip, 512GB SSD, 32GB RAM','price'=>70000.0,'category_id'=>4,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>6,'rate'=>4.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Samsung Odyssey G9','description'=>'Samsung 49-inch curved gaming monitor, 240Hz, 1ms response time','price'=>35000.0,'category_id'=>5,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>4,'rate'=>4.5,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'LG UltraFine 5K','description'=>'LG 27-inch 5K monitor, perfect for Mac, P3 color gamut','price'=>28000.0,'category_id'=>5,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>8,'rate'=>3.25,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'LG C3 OLED 65-inch','description'=>'LG 65-inch OLED evo TV, 4K, AI-powered processor, smart TV','price'=>65000.0,'category_id'=>6,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>14,'rate'=>3.83,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sony A7 IV','description'=>'Sony A7 IV full-frame mirrorless camera, 33MP, 4K video','price'=>42000.0,'category_id'=>7,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>14,'rate'=>3.86,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sony WH-1000XM5','description'=>'Sony WH-1000XM5 wireless noise-cancelling headphones','price'=>8500.0,'category_id'=>8,'owner_id'=>$adminId,'quantity'=>12,'view_count'=>20,'rate'=>4.4,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sonos Era 300','description'=>'Sonos Era 300 spatial audio speaker, Wi-Fi and Bluetooth','price'=>6500.0,'category_id'=>9,'owner_id'=>$adminId,'quantity'=>6,'view_count'=>16,'rate'=>4.62,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'JBL Charge 5','description'=>'JBL Charge 5 portable Bluetooth speaker, waterproof, 20-hour battery','price'=>3500.0,'category_id'=>9,'owner_id'=>$adminId,'quantity'=>12,'view_count'=>8,'rate'=>4.25,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Apple Watch Ultra 2','description'=>'Apple Watch Ultra 2 with 49mm titanium case, cellular, 100m water resistant','price'=>18000.0,'category_id'=>10,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>3,'rate'=>5.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'PlayStation 5','description'=>'Sony PlayStation 5 console with 1TB storage, DualSense controller','price'=>18000.0,'category_id'=>11,'owner_id'=>$adminId,'quantity'=>10,'view_count'=>7,'rate'=>4.67,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'HP LaserJet Pro M283fdw','description'=>'HP LaserJet Pro wireless color printer, scanner, copier, fax','price'=>8500.0,'category_id'=>12,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>8,'rate'=>3.5,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Canon PIXMA TS9520','description'=>'Canon PIXMA wireless all-in-one printer, 5-color ink system','price'=>4500.0,'category_id'=>12,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>5,'rate'=>5.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Fujitsu ScanSnap iX1600','description'=>'Fujitsu ScanSnap wireless document scanner, 40ppm, touch screen','price'=>9500.0,'category_id'=>13,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>9,'rate'=>4.5,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Brother ADS-1700W','description'=>'Brother wireless portable document scanner, 25ppm, network ready','price'=>5500.0,'category_id'=>13,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>10,'rate'=>2.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Epson Home Cinema 5050UB','description'=>'Epson 4K PRO-UHD projector, 2600 lumens, HDR10','price'=>35000.0,'category_id'=>14,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>9,'rate'=>3.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'BenQ TK860i','description'=>'BenQ 4K HDR home theater projector, 3300 lumens, Android TV','price'=>28000.0,'category_id'=>14,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>7,'rate'=>4.33,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Dell XPS 13 Plus','description'=>'Dell XPS 13 Plus laptop, Intel Core i7, 512GB SSD, 16GB RAM','price'=>38000.0,'category_id'=>23,'owner_id'=>$adminId,'quantity'=>4,'view_count'=>5,'rate'=>3.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Microsoft Surface Laptop 5','description'=>'Microsoft Surface Laptop 5, Intel Core i7, 512GB SSD, 16GB RAM','price'=>35000.0,'category_id'=>23,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>7,'rate'=>4.33,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'iPad Mini 6th Gen','description'=>'Apple iPad Mini with A15 chip, 256GB, 8.3-inch display','price'=>22000.0,'category_id'=>24,'owner_id'=>$adminId,'quantity'=>6,'view_count'=>3,'rate'=>5.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Samsung Galaxy Tab S8','description'=>'Samsung Galaxy Tab S8, 11-inch, 128GB, S Pen included','price'=>18000.0,'category_id'=>24,'owner_id'=>$adminId,'quantity'=>5,'view_count'=>9,'rate'=>5.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'LG C2 OLED 55-inch','description'=>'LG C2 55-inch OLED evo TV, 4K, a9 Gen5 AI processor','price'=>48000.0,'category_id'=>25,'owner_id'=>$adminId,'quantity'=>3,'view_count'=>13,'rate'=>4.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Samsung QN85B Neo QLED 65-inch','description'=>'Samsung 65-inch Neo QLED 4K TV, Quantum HDR','price'=>52000.0,'category_id'=>25,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>9,'rate'=>4.75,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Aruba Networking Devices Plus','description'=>'Aruba Networking Devices Plus - Networking Devices with Secure Connection, Multiple Ports, Mesh System. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>7279.0,'category_id'=>15,'owner_id'=>$adminId,'quantity'=>13,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Zyxel Networking Devices Lite','description'=>'Zyxel Networking Devices Lite - Networking Devices with Gaming Optimized. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>4808.0,'category_id'=>15,'owner_id'=>$adminId,'quantity'=>35,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Seagate Storage Devices Lite','description'=>'Seagate Storage Devices Lite - Storage Devices with High Speed, USB-C. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>5346.0,'category_id'=>16,'owner_id'=>$adminId,'quantity'=>24,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Transcend Storage Devices 2023','description'=>'Transcend Storage Devices 2023 - Storage Devices with LED Indicator. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>5888.0,'category_id'=>16,'owner_id'=>$adminId,'quantity'=>48,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sony Memory Cards Ultra','description'=>'Sony Memory Cards Ultra - Memory Cards with Water Proof, Magnetic Proof. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>732.0,'category_id'=>17,'owner_id'=>$adminId,'quantity'=>44,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Lexar Memory Cards Plus','description'=>'Lexar Memory Cards Plus - Memory Cards with X-ray Proof, High Speed. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>581.0,'category_id'=>17,'owner_id'=>$adminId,'quantity'=>19,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Belkin Power Banks 19','description'=>'Belkin Power Banks 19 - Power Banks with Temperature Control. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>438.0,'category_id'=>18,'owner_id'=>$adminId,'quantity'=>7,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Aukey Power Banks Pro Max','description'=>'Aukey Power Banks Pro Max - Power Banks with Overcharge Protection, LED Indicator, High Capacity. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>698.0,'category_id'=>18,'owner_id'=>$adminId,'quantity'=>24,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Anker Chargers Ultra','description'=>'Anker Chargers Ultra - Chargers with GaN Technology, Overcharge Protection, Foldable Plug. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>895.0,'category_id'=>19,'owner_id'=>$adminId,'quantity'=>30,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Samsung Chargers Lite','description'=>'Samsung Chargers Lite - Chargers with Foldable Plug, Fast Charging. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>136.0,'category_id'=>19,'owner_id'=>$adminId,'quantity'=>32,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Cable Matters Cables Ultra','description'=>'Cable Matters Cables Ultra - Cables with Fast Charging. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>443.0,'category_id'=>20,'owner_id'=>$adminId,'quantity'=>29,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Startech Cables Plus','description'=>'Startech Cables Plus - Cables with Universal Compatibility, Data Transfer, Braided Design. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>320.0,'category_id'=>20,'owner_id'=>$adminId,'quantity'=>8,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Motorola Phones Plus','description'=>'Motorola Phones Plus - Phones with Long Battery. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>41252.0,'category_id'=>21,'owner_id'=>$adminId,'quantity'=>42,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Xiaomi Phones Lite','description'=>'Xiaomi Phones Lite - Phones with Fast Charging. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>39880.0,'category_id'=>21,'owner_id'=>$adminId,'quantity'=>2,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Leica Camera 26','description'=>'Leica Camera 26 - Camera with 4K Video. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>33192.0,'category_id'=>22,'owner_id'=>$adminId,'quantity'=>19,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Leica Camera 27','description'=>'Leica Camera 27 - Camera with Image Stabilization, Waterproof, 4K Video. Premium quality, excellent condition, comes with original accessories and warranty.','price'=>32175.0,'category_id'=>22,'owner_id'=>$adminId,'quantity'=>17,'view_count'=>0,'rate'=>0.0,'pay_count'=>0,'addingToCart_count'=>0,'type'=>'sale','status'=>'1','created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        $this->command->info('✓ Seeder done');
        $this->command->info('  Admin:  admin@tasleem.com  / admin123');
        $this->command->info('  Seller: seller@tasleem.com / seller123');
    }
}
