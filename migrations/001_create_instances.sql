-- VoxelSwarm — instances table
CREATE TABLE IF NOT EXISTS instances (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    slug            TEXT UNIQUE NOT NULL,
    subdomain       TEXT UNIQUE NOT NULL,
    name            TEXT NOT NULL,
    email           TEXT NOT NULL,
    status          TEXT NOT NULL DEFAULT 'queued',
    type            TEXT NOT NULL DEFAULT 'tenant',
    step            TEXT,
    document_root   TEXT,
    provisioned_at  TEXT,
    last_active_at  TEXT,
    deleted_at      TEXT,
    notes           TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_instances_status ON instances(status);
CREATE INDEX IF NOT EXISTS idx_instances_type ON instances(type);
CREATE INDEX IF NOT EXISTS idx_instances_email ON instances(email);
