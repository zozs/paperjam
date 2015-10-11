# Database schema

Written for PostgreSQL.

```sql
CREATE TABLE senders (
  id SERIAL NOT NULL,
  name TEXT NOT NULL UNIQUE,
  PRIMARY KEY(id)
);

CREATE TABLE tags (
  id SERIAL NOT NULL,
  name TEXT NOT NULL UNIQUE,
  PRIMARY KEY(id)
);

CREATE TABLE documents (
  id SERIAL NOT NULL,
  received DATE NOT NULL,
  sender INTEGER NOT NULL REFERENCES senders (id),
  PRIMARY KEY(id)
);

CREATE TABLE pages (
  id SERIAL NOT NULL,
  file TEXT NOT NULL, -- only the filename, no path.
  document INTEGER NULL REFERENCES documents (id),
  page_order INTEGER NULL, -- 0-based page order in document.
  page_count INTEGER NOT NULL DEFAULT 1,
  uploaded TIMESTAMP NOT NULL DEFAULT (NOW() AT TIME ZONE 'utc'), -- UTC time when file was uploaded.
  PRIMARY KEY(id)
);

CREATE TABLE documents_tags (
  did INTEGER NOT NULL REFERENCES documents (id),
  tid INTEGER NOT NULL REFERENCES tags (id),
  UNIQUE (did, tid)
);

CREATE VIEW unorganised_pages AS
  SELECT id, file FROM pages WHERE document IS NULL;
```
