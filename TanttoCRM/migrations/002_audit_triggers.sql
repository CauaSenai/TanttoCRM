-- Optional: example triggers to automatically write audit logs for `clientes` and `negociacoes`.
-- WARNING: triggers may need adjustment for your schema (column names, types) and may not escape special characters.
-- Use locally and test on a copy of the DB before deploying to production.

-- Example for `clientes` table (adjust column list as needed)
DELIMITER $$
CREATE TRIGGER trg_clientes_after_insert
AFTER INSERT ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo)
  VALUES ('cliente', NEW.id_cliente, NULL, 'create', CONCAT('{"nome":"', REPLACE(NEW.nome, '"', '\\\"'), '","email":"', REPLACE(NEW.email, '"', '\\\"'), '","telefone":"', REPLACE(NEW.telefone, '"', '\\\"'), '"}'));
END$$

CREATE TRIGGER trg_clientes_after_update
AFTER UPDATE ON clientes
FOR EACH ROW
BEGIN
  -- Example: write a single 'update' entry with combined new_value; per-field triggers are possible but verbose
  INSERT INTO auditoria (entidade_tipo, entidade_id, user_id, acao, valor_antigo, valor_novo)
  VALUES ('cliente', NEW.id_cliente, NULL, 'update', CONCAT('{"nome":"', REPLACE(OLD.nome, '"', '\\\"'), '","email":"', REPLACE(OLD.email, '"', '\\\"'), '","telefone":"', REPLACE(OLD.telefone, '"', '\\\"'), '"}'),
                                                                 CONCAT('{"nome":"', REPLACE(NEW.nome, '"', '\\\"'), '","email":"', REPLACE(NEW.email, '"', '\\\"'), '","telefone":"', REPLACE(NEW.telefone, '"', '\\\"'), '"}'));
END$$

CREATE TRIGGER trg_clientes_after_delete
AFTER DELETE ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, user_id, acao, valor_antigo)
  VALUES ('cliente', OLD.id_cliente, NULL, 'delete', CONCAT('{"nome":"', REPLACE(OLD.nome, '"', '\\\"'), '","email":"', REPLACE(OLD.email, '"', '\\\"'), '","telefone":"', REPLACE(OLD.telefone, '"', '\\\"'), '"}'));
END$$

-- Example for `negociacoes` table (adjust column list as needed)
CREATE TRIGGER trg_negociacoes_after_insert
AFTER INSERT ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo)
  VALUES ('negociacao', NEW.id_negociacao, NULL, 'create', CONCAT('{"titulo":"', REPLACE(NEW.titulo, '"', '\\\"'), '","valor":"', REPLACE(NEW.valor, '"', '\\\"'), '","status":"', REPLACE(NEW.status, '"', '\\\"'), '"}'));
END$$

CREATE TRIGGER trg_negociacoes_after_update
AFTER UPDATE ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, user_id, acao, valor_antigo, valor_novo)
  VALUES ('negociacao', NEW.id_negociacao, NULL, 'update', CONCAT('{"titulo":"', REPLACE(OLD.titulo, '"', '\\\"'), '","valor":"', REPLACE(OLD.valor, '"', '\\\"'), '","status":"', REPLACE(OLD.status, '"', '\\\"'), '"}'),
                                                                      CONCAT('{"titulo":"', REPLACE(NEW.titulo, '"', '\\\"'), '","valor":"', REPLACE(NEW.valor, '"', '\\\"'), '","status":"', REPLACE(NEW.status, '"', '\\\"'), '"}'));
END$$

CREATE TRIGGER trg_negociacoes_after_delete
AFTER DELETE ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, user_id, acao, valor_antigo)
  VALUES ('negociacao', OLD.id_negociacao, NULL, 'delete', CONCAT('{"titulo":"', REPLACE(OLD.titulo, '"', '\\\"'), '","valor":"', REPLACE(OLD.valor, '"', '\\\"'), '","status":"', REPLACE(OLD.status, '"', '\\\"'), '"}'));
END$$
DELIMITER ;

-- Notes:
-- 1) These triggers set user_id to NULL. If you want to capture the current PHP session user, you'd need to set a session variable in MySQL (e.g. via SET @current_user_id = 123) from your app before executing statements in the same connection, or keep PHP-based logging as implemented.
-- 2) Adjust column lists for each table to include the fields you want audited.
-- 3) Test triggers on a development DB copy; they may require modification depending on column names and types.
