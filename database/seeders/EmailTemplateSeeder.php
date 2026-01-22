<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EmailTemplate::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $templates = [

            ['name' => 'Register', 'subject' => 'Thankyou for Registering', 'template' => '<p>Welcome ##name##</p><p>&nbsp;</p><p>Thank you for registering with Sling Billing. You can use the details below to log in and start managing your billing.</p><p>&nbsp;</p><p>Your User Name: ##user_name##</p><p></p><p>Your Password: ##password##</p><p>&nbsp;</p><p>Click the link below to log in:</p><p>&nbsp;</p><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<a href="##route##"><strong>Login</strong></a></p><p>Thanks &amp; regards</p><p>Sling Billing</p>', 'is_active' => 1, 'created_by' => 1],
        ];

        foreach ($templates as $key => $value) {

            EmailTemplate::create($value);

        }
    }
}

