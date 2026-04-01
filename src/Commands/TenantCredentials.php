<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Repositories\TenantRepository;
use Slides\Saml2\Models\TenantWithUuidInterface;

/**
 * Class TenantCredentials
 */
class TenantCredentials extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:tenant-credentials {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List tenant credentials for IdP';

    /** @var TenantRepository */
    protected $tenants;

    /**
     * DeleteTenant constructor.
     */
    public function __construct(TenantRepository $tenants)
    {
        $this->tenants = $tenants;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $reflectionClass = new \ReflectionClass($this->tenants->getClass());

        if ($reflectionClass->implementsInterface(TenantWithUuidInterface::class)) {
            $tenant = $this->tenants->findByIdString($this->argument('id'));
        } else {
            $tenant = $this->tenants->findById($this->argument('id'));
        }

        if (!$tenant) {
            $this->error('Cannot find a tenant #' . $this->argument('id'));

            return;
        }

        $this->renderTenants($tenant, 'The tenant model');
        $this->renderTenantCredentials($tenant);

        $this->output->newLine();
    }
}
