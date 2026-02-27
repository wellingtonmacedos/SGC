SET @dbname = DATABASE();
SET @tablename = "courses";
SET @columnname = "has_certificate";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE courses ADD COLUMN has_certificate TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Define se o curso/palestra gera certificado';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
