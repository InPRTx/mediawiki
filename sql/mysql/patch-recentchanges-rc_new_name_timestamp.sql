-- This file is automatically generated using maintenance/generateSchemaChangeSql.php.
-- Source: sql/abstractSchemaChanges/patch-recentchanges-rc_new_name_timestamp.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
DROP INDEX new_name_timestamp ON /*_*/recentchanges;

CREATE INDEX rc_new_name_timestamp ON /*_*/recentchanges (
    rc_new, rc_namespace, rc_timestamp
  );
