<?php

namespace Slides\Saml2\Commands;

use Illuminate\Console\Command;

/**
 * Class CreateCertificate
 *
 * @package Slides\Saml2\Commands
 */
class CreateCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:cert
                            {--days=7300 : Number of days to add from today as the expiration date}
                            {--keyname=key.pem : Full name of the certificate key file}
                            {--certname=cert.pem : Full name to the certificate file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new certificate and private key for your SP';

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
        $storagePath = storage_path('samlsp');
        $days = $this->option('days');
        $keyname = $this->option('keyname');
        $certname = $this->option('certname');

        // Create storage/samlidp directory
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $key = sprintf('%s/%s', $storagePath, $keyname);
        $cert = sprintf('%s/%s', $storagePath, $certname);
        $question = 'The name chosen for the PEM files already exist. Would you like to overwrite existing PEM files?';
        if ((!file_exists($key) && !file_exists($cert)) || $this->confirm($question)) {
            $command = 'openssl req -x509 -sha256 -nodes -days %s -newkey rsa:2048 -keyout %s -out %s';
            exec(sprintf($command, $days, $key, $cert));
            $this->info("The certificate was successfully created.");
        }
    }
}