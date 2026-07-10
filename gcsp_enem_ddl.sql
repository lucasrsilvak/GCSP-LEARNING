-- ==============================================================================
-- 0. CRIAÇÃO DO BANCO DE DADOS
-- ==============================================================================
CREATE DATABASE IF NOT EXISTS gcsp_enem
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gcsp_enem;

-- ==============================================================================
-- 1. TABELA DE ESCOLAS (DADOS INEP)
-- ==============================================================================
CREATE TABLE escolas (
    co_escola INT PRIMARY KEY,               
    co_municipio_esc INT,                    
    no_municipio_esc VARCHAR(150),           
    co_uf_esc INT,                           
    sg_uf_esc VARCHAR(2),                    
    tp_dependencia_adm_esc INT,              
    tp_localizacao_esc INT,                  
    tp_sit_func_esc INT                      
);

-- ==============================================================================
-- 2. TABELA DE PARTICIPANTES (DADOS INEP)
-- ==============================================================================
CREATE TABLE participantes (
    nu_inscricao VARCHAR(20) PRIMARY KEY,    
    nu_ano INT,                              
    tp_faixa_etaria INT,                     
    tp_sexo VARCHAR(1),                      
    tp_estado_civil INT,                     
    tp_cor_raca INT,                         
    tp_nacionalidade INT,                    
    tp_st_conclusao INT,                     
    tp_ano_concluiu INT,                     
    tp_ensino INT,                           
    in_treineiro INT,                        
    co_municipio_prova INT,                  
    no_municipio_prova VARCHAR(150),         
    co_uf_prova INT,                         
    sg_uf_prova VARCHAR(2),                  
    q001 VARCHAR(1), 
    q002 VARCHAR(1), 
    q003 VARCHAR(1), 
    q004 VARCHAR(1), 
    q005 INT,        
    q006 VARCHAR(1), 
    q007 VARCHAR(1), 
    q008 VARCHAR(1), 
    q009 VARCHAR(1), 
    q010 VARCHAR(1), 
    q011 VARCHAR(1), 
    q012 VARCHAR(1), 
    q013 VARCHAR(1), 
    q014 VARCHAR(1), 
    q015 VARCHAR(1), 
    q016 VARCHAR(1), 
    q017 VARCHAR(1), 
    q018 VARCHAR(1), 
    q019 VARCHAR(1), 
    q020 VARCHAR(1), 
    q021 VARCHAR(1), 
    q022 VARCHAR(1), 
    q023 VARCHAR(1)  
);

-- ==============================================================================
-- 3. TABELA DE RESULTADOS DAS PROVAS E REDAÇÃO (DADOS INEP)
-- ==============================================================================
CREATE TABLE resultados_provas (
    nu_sequencial INT PRIMARY KEY,           
    nu_inscricao VARCHAR(20) NOT NULL,       
    co_escola INT,                           
    tp_presenca_cn INT,                      
    tp_presenca_ch INT,                      
    tp_presenca_lc INT,                      
    tp_presenca_mt INT,                      
    co_prova_cn INT,                         
    co_prova_ch INT,                         
    co_prova_lc INT,                         
    co_prova_mt INT,                         
    nu_nota_cn DECIMAL(10,2),                
    nu_nota_ch DECIMAL(10,2),                
    nu_nota_lc DECIMAL(10,2),                
    nu_nota_mt DECIMAL(10,2),                
    tx_respostas_cn VARCHAR(45),             
    tx_respostas_ch VARCHAR(45),             
    tx_respostas_lc VARCHAR(50),             
    tx_respostas_mt VARCHAR(45),             
    tp_lingua INT,                           
    tx_gabarito_cn VARCHAR(45),              
    tx_gabarito_ch VARCHAR(45),              
    tx_gabarito_lc VARCHAR(50),              
    tx_gabarito_mt VARCHAR(45),              
    tp_status_redacao INT,                   
    nu_nota_comp1 INT,                       
    nu_nota_comp2 INT,                       
    nu_nota_comp3 INT,                       
    nu_nota_comp4 INT,                       
    nu_nota_comp5 INT,                       
    nu_nota_redacao INT,                     
    
    FOREIGN KEY (nu_inscricao) REFERENCES participantes(nu_inscricao) ON DELETE CASCADE,
    FOREIGN KEY (co_escola) REFERENCES escolas(co_escola) ON DELETE SET NULL
);

-- ==============================================================================
-- 4. TABELA DE ITENS DA PROVA - DADOS DA TRI (DADOS INEP)
-- ==============================================================================
CREATE TABLE itens_prova (
    co_item INT PRIMARY KEY,                 
    co_prova INT,                            
    tx_cor VARCHAR(50),                      
    tp_lingua INT,                           
    co_posicao INT,                          
    sg_area VARCHAR(2),                      
    tx_gabarito VARCHAR(1),                  
    in_item_adaptado INT,                    
    in_item_aban INT,                        
    tx_motivo_aban VARCHAR(40),              
    co_habilidade INT,                       
    nu_param_a DECIMAL(10,5),                
    nu_param_b DECIMAL(10,5),                
    nu_param_c DECIMAL(10,5)                 
);

-- ==============================================================================
-- 5. TABELA DE USUÁRIOS (ALUNOS REAIS DA PLATAFORMA)
-- ==============================================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================================================
-- 6. TABELA DE PERFIL SOCIOECONÔMICO E CADASTRAL DOS USUÁRIOS
-- (Espelho exato da tabela 'participantes', ligado ao 'usuario_id')
-- ==============================================================================
CREATE TABLE perfil_socioeconomico (
    usuario_id INT PRIMARY KEY,
    
    -- Dados Pessoais (Espelho de Participantes)
    nu_ano INT,                              
    tp_faixa_etaria INT,                     
    tp_sexo VARCHAR(1),                      
    tp_estado_civil INT,                     
    tp_cor_raca INT,                         
    tp_nacionalidade INT,                    
    
    -- Dados Escolares
    tp_st_conclusao INT,                     
    tp_ano_concluiu INT,                     
    tp_ensino INT,                           
    in_treineiro INT,                        
    
    -- Dados de Localização da Prova
    co_municipio_prova INT,                  
    no_municipio_prova VARCHAR(150),         
    co_uf_prova INT,                         
    sg_uf_prova VARCHAR(2),  

    -- Questionário Socioeconômico
    q001 VARCHAR(1), 
    q002 VARCHAR(1), 
    q003 VARCHAR(1), 
    q004 VARCHAR(1), 
    q005 INT,        
    q006 VARCHAR(1), 
    q007 VARCHAR(1), 
    q008 VARCHAR(1), 
    q009 VARCHAR(1), 
    q010 VARCHAR(1), 
    q011 VARCHAR(1), 
    q012 VARCHAR(1), 
    q013 VARCHAR(1), 
    q014 VARCHAR(1), 
    q015 VARCHAR(1), 
    q016 VARCHAR(1), 
    q017 VARCHAR(1), 
    q018 VARCHAR(1), 
    q019 VARCHAR(1), 
    q020 VARCHAR(1), 
    q021 VARCHAR(1), 
    q022 VARCHAR(1), 
    q023 VARCHAR(1),
    q024 VARCHAR(1),
    q025 VARCHAR(1),
    
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);