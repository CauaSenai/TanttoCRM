-- Migration: adicionar índices únicos para evitar duplicatas
-- ATENÇÃO: Se existirem valores duplicados, estes ALTERs irão falhar. Use o script PHP check_and_add_constraints.php para detectar duplicatas primeiro.

ALTER TABLE usuarios ADD UNIQUE INDEX ux_usuarios_email (email);
ALTER TABLE clientes ADD UNIQUE INDEX ux_clientes_email (email);
ALTER TABLE clientes ADD UNIQUE INDEX ux_clientes_telefone (telefone);
ALTER TABLE negociacoes ADD UNIQUE INDEX ux_negociacoes_cliente_titulo (cliente_id, titulo);
