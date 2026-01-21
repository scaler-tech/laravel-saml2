<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Helpers\ConsoleHelper;
use Slides\Saml2\Models\Tenant;
use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class CreateTenant
 *
 * @package Slides\Saml2\Commands
 */
class CreateTenant extends \Illuminate\Console\Command
{
    use RendersTenants, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:create-tenant
                            { --k|key= : A tenant custom key }
                            { --entityId= : IdP Issuer URL }
                            { --loginUrl= : IdP Sign on URL }
                            { --logoutUrl= : IdP Logout URL }
                            { --relayStateUrl= : Redirection URL after successful login }
                            { --nameIdFormat= : Name ID Format ("persistent" by default) }
                            { --x509cert= : x509 certificate (base64) }
                            { --metadata= : A custom metadata (JSON) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Tenant entity (relying identity provider)';

    /**
     * @var TenantRepository
     */
    protected $tenants;

    /**
     * DeleteTenant constructor.
     *
     * @param TenantRepository $tenants
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
        if (!$entityId = $this->option('entityId')) {
            $this->error('Entity ID must be passed as an option --entityId');
            return;
        }

        if (!$loginUrl = $this->option('loginUrl')) {
            $this->error('Login URL must be passed as an option --loginUrl');
            return;
        }

        if (!$logoutUrl = $this->option('logoutUrl')) {
            $this->error('Logout URL must be passed as an option --logoutUrl');
            return;
        }

        if (!$x509cert = $this->option('x509cert')) {
            $this->error('x509 certificate (base64) must be passed as an option --x509cert');
            return;
        }

        $key = $this->option('key');
        $metadata = $this->option('metadata') ? \json_encode($this->option('metadata')) : '[]';

        if($key && ($tenant = $this->tenants->findByKey($key))) {
            $this->renderTenants($tenant, 'Already found tenant(s) using this key');
            $this->error(
                'Cannot create a tenant because the key is already being associated with other tenants.'
                    . PHP_EOL . 'Firstly, delete tenant(s) or try to create with another with another key.'
            );

            return;
        }

        $class = config('saml2.tenantModel', Tenant::class);
        $tenant = new $class([
            'key' => $key,
            'uuid' => \Ramsey\Uuid\Uuid::uuid4(),
            'idp_entity_id' => $entityId,
            'idp_login_url' => $loginUrl,
            'idp_logout_url' => $logoutUrl,
            'idp_x509_cert' => $x509cert,
            'relay_state_url' => $this->option('relayStateUrl'),
            'name_id_format' => $this->resolveNameIdFormat(),
            'metadata' => $metadata,
        ]);

        if(!$tenant->save()) {
            $this->error('Tenant cannot be saved.');
            return;
        }

        $this->info("The tenant #{$tenant->id} ({$tenant->uuid}) was successfully created.");

        $this->renderTenantCredentials($tenant);

        $this->output->newLine();
    }
}