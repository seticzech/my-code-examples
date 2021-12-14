-- Find tenant by ID
-- Returns: table

DROP FUNCTION IF EXISTS bb_sys.tenant_find(i_tenant_id uuid);

CREATE OR REPLACE FUNCTION bb_sys.tenant_find(i_tenant_id uuid)
    RETURNS table (
        id uuid,
        code varchar,
        host varchar,
        is_active boolean
    )
    SECURITY DEFINER
AS $$
BEGIN
    RETURN QUERY
        SELECT t.id, t.code, th.host, t.is_active
        FROM bb_sys.tenants t
        LEFT JOIN bb_sys.tenants_hosts th ON th.tenant_id = t.id
        WHERE t.id = i_tenant_id AND t.is_active = 't' AND t.deleted_at IS NULL;
END
$$ LANGUAGE plpgsql;

GRANT EXECUTE ON FUNCTION bb_sys.tenant_verify(i_tenant_id uuid) TO bb_usr;
