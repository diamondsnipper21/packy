<?php

namespace app\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedAlphaDatabase2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:seed-alpha-database-2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (config('app.env') === 'production') {
            return;
        }

        DB::insert("INSERT INTO `vat_rates` (`id`, `type`, `category`, `country`, `rate`, `stripe_tax_rate_id`, `created_at`, `updated_at`)
            VALUES
                ('1', 'General', 'Electronically Supplied Services', 'AT', '20.00', 'txr_1QiIpHQtNiGCI2hJndbWau2M', NULL, NULL),
                ('2', 'General', 'Electronically Supplied Services', 'BE', '21.00', 'txr_1QiIpIQtNiGCI2hJ7au7kSxP', NULL, NULL),
                ('3', 'General', 'Electronically Supplied Services', 'BG', '20.00', 'txr_1QiIpIQtNiGCI2hJd7KkiswW', NULL, NULL),
                ('4', 'General', 'Electronically Supplied Services', 'CZ', '20.00', 'txr_1QiIpIQtNiGCI2hJEyxM0rbT', NULL, NULL),
                ('5', 'General', 'Electronically Supplied Services', 'DK', '25.00', 'txr_1QiIpIQtNiGCI2hJVVqsXdAZ', NULL, NULL),
                ('6', 'General', 'Electronically Supplied Services', 'DE', '19.00', 'txr_1QiIpJQtNiGCI2hJVYShVAfH', NULL, NULL),
                ('7', 'General', 'Electronically Supplied Services', 'EE', '22.00', 'txr_1QiIpJQtNiGCI2hJZPtYafC5', NULL, NULL),
                ('8', 'General', 'Electronically Supplied Services', 'IE', '23.00', 'txr_1QiIpJQtNiGCI2hJND9n0iyz', NULL, NULL),
                ('9', 'General', 'Electronically Supplied Services', 'GR', '24.00', 'txr_1QiIv9QtNiGCI2hJMU0PUYRW', NULL, NULL),
                ('10', 'General', 'Electronically Supplied Services', 'ES', '21.00', 'txr_1QiIpJQtNiGCI2hJfUA9LC91', NULL, NULL),
                ('11', 'General', 'Electronically Supplied Services', 'FR', '20.00', 'txr_1QiIpKQtNiGCI2hJiH1O5oRY', NULL, NULL),
                ('12', 'General', 'Electronically Supplied Services', 'IT', '22.00', 'txr_1QiIpKQtNiGCI2hJ3p6vQWF7', NULL, NULL),
                ('13', 'General', 'Electronically Supplied Services', 'CY', '19.00', 'txr_1QiIpKQtNiGCI2hJYLCNITb8', NULL, NULL),
                ('14', 'General', 'Electronically Supplied Services', 'LV', '21.00', 'txr_1QiIpKQtNiGCI2hJ4DlTjMX7', NULL, NULL),
                ('15', 'General', 'Electronically Supplied Services', 'LT', '21.00', 'txr_1QiIpLQtNiGCI2hJuyg6Pavr', NULL, NULL),
                ('16', 'General', 'Electronically Supplied Services', 'LU', '17.00', 'txr_1QiIpLQtNiGCI2hJtAjbUWlx', NULL, NULL),
                ('17', 'General', 'Electronically Supplied Services', 'HU', '27.00', 'txr_1QiIpLQtNiGCI2hJEKMa2Gvt', NULL, NULL),
                ('18', 'General', 'Electronically Supplied Services', 'MT', '18.00', 'txr_1QiIpLQtNiGCI2hJKkIFo3QR', NULL, NULL),
                ('19', 'General', 'Electronically Supplied Services', 'NL', '21.00', 'txr_1QiIpLQtNiGCI2hJfsp0Xnof', NULL, NULL),
                ('20', 'General', 'Electronically Supplied Services', 'PL', '23.00', 'txr_1QiIpMQtNiGCI2hJYOvvTo8l', NULL, NULL),
                ('21', 'General', 'Electronically Supplied Services', 'PT', '23.00', 'txr_1QiIpMQtNiGCI2hJ5EGpAxJA', NULL, NULL),
                ('22', 'General', 'Electronically Supplied Services', 'RO', '19.00', 'txr_1QiIpMQtNiGCI2hJti0rpNFu', NULL, NULL),
                ('23', 'General', 'Electronically Supplied Services', 'SI', '22.00', 'txr_1QiIpMQtNiGCI2hJadUnKpLS', NULL, NULL),
                ('24', 'General', 'Electronically Supplied Services', 'SK', '20.00', 'txr_1QiIpNQtNiGCI2hJQ46cN3ne', NULL, NULL),
                ('25', 'General', 'Electronically Supplied Services', 'FI', '25.50', 'txr_1QiIpNQtNiGCI2hJl8SMzjl0', NULL, NULL),
                ('26', 'General', 'Electronically Supplied Services', 'SE', '25.00', 'txr_1QiIpNQtNiGCI2hJOUei6ZHM', NULL, NULL),
                ('27', 'General', 'Electronically Supplied Services', 'GB', '20.00', 'txr_1QiIx0QtNiGCI2hJM4Q3EvZX', NULL, NULL);
        ");
    }
}
