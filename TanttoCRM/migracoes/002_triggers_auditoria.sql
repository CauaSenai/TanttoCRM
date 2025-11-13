-- Triggers de exemplo para a tabela 'auditoria'
DELIMITER $$
CREATE TRIGGER trg_clientes_after_insert
AFTER INSERT ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo)
  VALUES ('cliente', NEW.id_cliente, NULL, 'create', CONCAT('{"nome":"', REPLACE(NEW.nome, '"', '\"'), '","email":"', REPLACE(NEW.email, '"', '\"'), '","telefone":"', REPLACE(NEW.telefone, '"', '\"'), '"}'));
END$$

CREATE TRIGGER trg_clientes_after_update
AFTER UPDATE ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo, valor_novo)
  VALUES ('cliente', NEW.id_cliente, NULL, 'update', CONCAT('{"nome":"', REPLACE(OLD.nome, '"', '\"'), '","email":"', REPLACE(OLD.email, '"', '\"'), '","telefone":"', REPLACE(OLD.telefone, '"', '\"'), '"}'),
                                                                 CONCAT('{"nome":"', REPLACE(NEW.nome, '"', '\"'), '","email":"', REPLACE(NEW.email, '"', '\"'), '","telefone":"', REPLACE(NEW.telefone, '"', '\"'), '"}'));
END$$

CREATE TRIGGER trg_clientes_after_delete
AFTER DELETE ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo)
  VALUES ('cliente', OLD.id_cliente, NULL, 'delete', CONCAT('{"nome":"', REPLACE(OLD.nome, '"', '\"'), '","email":"', REPLACE(OLD.email, '"', '\"'), '","telefone":"', REPLACE(OLD.telefone, '"', '\"'), '"}'));
END$$

CREATE TRIGGER trg_negociacoes_after_insert
AFTER INSERT ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo)
  VALUES ('negociacao', NEW.id_negociacao, NULL, 'create', CONCAT('{"titulo":"', REPLACE(NEW.titulo, '"', '\"'), '","valor":"', REPLACE(NEW.valor, '"', '\"'), '","status":"', REPLACE(NEW.status, '"', '\"'), '"}'));
END$$

CREATE TRIGGER trg_negociacoes_after_update
AFTER UPDATE ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo, valor_novo)
  VALUES ('negociacao', NEW.id_negociacao, NULL, 'update', CONCAT('{"titulo":"', REPLACE(OLD.titulo, '"', '\"'), '","valor":"', REPLACE(OLD.valor, '"', '\"'), '","status":"', REPLACE(OLD.status, '"', '\"'), '"}'),
                                                                      CONCAT('{"titulo":"', REPLACE(NEW.titulo, '"', '\"'), '","valor":"', REPLACE(NEW.valor, '"', '\"'), '","status":"', REPLACE(NEW.status, '"', '\"'), '"}'));
END$$

CREATE TRIGGER trg_negociacoes_after_delete
AFTER DELETE ON negociacoes
FOR EACH ROW
BEGIN
  INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo)
  VALUES ('negociacao', OLD.id_negociacao, NULL, 'delete', CONCAT('{"titulo":"', REPLACE(OLD.titulo, '"', '\"'), '","valor":"', REPLACE(OLD.valor, '"', '\"'), '","status":"', REPLACE(OLD.status, '"', '\"'), '"}'));
END$$
DELIMITER ;

-- Observações: ajuste conforme seu esquema e teste em ambiente de desenvolvimento.
