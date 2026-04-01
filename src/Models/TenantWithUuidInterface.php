<?php

namespace Slides\Saml2\Models;

/**
 * Interface TenantWithUuidInterface
 *
 * @property string            $id
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
interface TenantWithUuidInterface
{
}
