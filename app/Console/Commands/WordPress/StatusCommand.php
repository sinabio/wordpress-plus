<?php

namespace App\Console\Commands\WordPress;

use Illuminate\Console\Command;

function yesno($boolean)
{
    return $boolean ? 'YES' : 'NO';
}

class StatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'wordpress:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show wordpress status.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // MEMO need for diagnosis
        define('WP_INSTALLING', true);
//        define('WP_REPAIRING', true);

        require_once wordpress_path('wp-load.php');

        foreach ($this->gather() as $result) {
            $this->{$result['method']}($result['message']);
        }

        $this->info('Done');
    }

    protected function gather()
    {
        $results = [];

        // Multisite
        $results[] = ['method' => 'info', 'message' => 'Multisite: '.yesno(is_multisite())];

        // Blog tables
        if (is_blog_installed()) {
            $results[] = ['method' => 'info', 'message' => 'Blog installed: '.yesno(true)];
        } else {
            $results[] = ['method' => 'error', 'message' => 'Blog installed: '.yesno(false)];
        }

        if (env('WP_MULTISITE')) {
            $this->info('Network Domain: ', network_domain_check());
        }
        else {
        }

        return $results;
    }

    /**
     * Check for an existing network.
     *
     * @since 3.0.0
     * @return Whether a network exists.
     */
    protected function network_domain_check()
    {
        global $wpdb;

        $sql = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->site));
        if ($wpdb->get_var($sql)) {
            return $wpdb->get_var("SELECT domain FROM $wpdb->site ORDER BY id ASC LIMIT 1");
        }

        return false;
    }
}
