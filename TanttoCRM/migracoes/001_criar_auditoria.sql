-- Migração: criar tabela auditoria (nomes em português)
-- Execute este arquivo para criar a tabela de auditoria com nomes em português.

CREATE TABLE IF NOT EXISTS auditoria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entidade_tipo VARCHAR(50) NOT NULL,
  entidade_id INT NOT NULL,
  usuario_id INT NULL,
  acao VARCHAR(20) NOT NULL,
  campo VARCHAR(100) NULL,
  valor_antigo TEXT NULL,
  valor_novo TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_aud_entidade ON auditoria(entidade_tipo, entidade_id);
CREATE INDEX idx_aud_usuario ON auditoria(usuario_id);

-- Obs: este arquivo é equivalente a migrations/003_create_auditoria.sql, mas com nome em português.
