-- VoxelSwarm — provision_logs table
CREATE TABLE IF NOT EXISTS provision_logs (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    instance_id     INTEGER NOT NULL REFERENCES instances(id),
    step            TEXT NOT NULL,
    status          TEXT NOT NULL,
    error           TEXT,
    duration_ms     INTEGER,
    created_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_provision_logs_instance ON provision_logs(instance_id);
