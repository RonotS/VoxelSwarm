-- VoxelSwarm — operator_sessions table
CREATE TABLE IF NOT EXISTS operator_sessions (
    id          TEXT PRIMARY KEY,
    expires_at  TEXT NOT NULL,
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);
