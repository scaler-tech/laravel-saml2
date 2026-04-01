<?php

namespace Slides\Saml2\Repositories;

use Slides\Saml2\Models\Tenant;

/**
 * Class TenantRepository
 */
class TenantRepository
{
    /**
     * @var string
     */
    protected $class;

    public function __construct()
    {
        $this->class = config('saml2.tenantModel', Tenant::class);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Create a new query.
     *
     * @param bool $withTrashed whether need to include safely deleted records
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(bool $withTrashed = false)
    {   
        $query = $this->class::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Find all tenants.
     *
     * @param bool $withTrashed whether need to include safely deleted records
     *
     * @return array<Tenant>|\Illuminate\Database\Eloquent\Collection
     */
    public function all(bool $withTrashed = true)
    {
        return $this->query($withTrashed)->get();
    }

    /**
     * Find a tenant by any identifier.
     *
     * @param int|string $key         ID, key or UUID
     * @param bool       $withTrashed whether need to include safely deleted records
     *
     * @return array<Tenant>|\Illuminate\Database\Eloquent\Collection
     */
    public function findByAnyIdentifier($key, bool $withTrashed = true)
    {
        $query = $this->query($withTrashed);

        if (is_int($key)) {
            return $query->where('id', $key)->get();
        }

        return $query->where('key', $key)
            ->orWhere('uuid', $key)
            ->get();
    }

    /**
     * Find a tenant by the key.
     *
     * @return \Illuminate\Database\Eloquent\Model|Tenant|null
     */
    public function findByKey(string $key, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('key', $key)
            ->first();
    }

    /**
     * Find a tenant by ID.
     *
     * @return \Illuminate\Database\Eloquent\Model|Tenant|null
     */
    public function findById(int $id, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('id', $id)
            ->first();
    }

    /**
     * Find a tenant by ID as string. Like MongoDB
     *
     * @return \Illuminate\Database\Eloquent\Model|Tenant|null
     */
    public function findByIdString(string $id, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('id', $id)
            ->first();
    }

    /**
     * Find a tenant by UUID.
     *
     * @param int $uuid
     *
     * @return \Illuminate\Database\Eloquent\Model|Tenant|null
     */
    public function findByUUID(string $uuid, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('uuid', $uuid)
            ->first();
    }
}
