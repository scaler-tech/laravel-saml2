<?php

namespace Slides\Saml2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tenant
 *
 * @property int            $id
 * @property string         $uuid
 * @property string         $key
 * @property string         $idp_entity_id
 * @property string         $idp_login_url
 * @property string         $idp_logout_url
 * @property string         $idp_x509_cert
 * @property string         $relay_state_url
 * @property string         $name_id_format
 * @property array          $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Tenant extends Model implements TenantInterface
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'saml2_tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'key',
        'idp_entity_id',
        'idp_login_url',
        'idp_logout_url',
        'idp_x509_cert',
        'relay_state_url',
        'name_id_format',
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];
}
