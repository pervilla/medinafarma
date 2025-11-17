<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAIMatchingTables extends Migration
{
    public function up()
    {
        // Table for matching suggestions
        $this->db->query("
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='digemid_matching_suggestions' AND xtype='U')
            CREATE TABLE digemid_matching_suggestions (
                id INT IDENTITY(1,1) PRIMARY KEY,
                cod_prod INT NOT NULL,
                art_key NUMERIC(8,0) NOT NULL,
                score DECIMAL(5,4) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                validated_by INT NULL,
                validated_at DATETIME NULL,
                metadata NVARCHAR(MAX) NULL,
                created_at DATETIME NOT NULL DEFAULT GETDATE(),
                CONSTRAINT FK_suggestions_digemid FOREIGN KEY (cod_prod) 
                    REFERENCES PRECIOS_DIGEMID(Cod_Prod)
            )
        ");
        
        // Table for learning history
        $this->db->query("
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='digemid_matching_history' AND xtype='U')
            CREATE TABLE digemid_matching_history (
                id INT IDENTITY(1,1) PRIMARY KEY,
                cod_prod INT NOT NULL,
                art_key NUMERIC(8,0) NOT NULL,
                score DECIMAL(5,4) NOT NULL,
                action VARCHAR(20) NOT NULL,
                user_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT GETDATE()
            )
        ");
        
        // Indexes
        $this->db->query("
            IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='idx_suggestions_status')
            CREATE INDEX idx_suggestions_status ON digemid_matching_suggestions(status, score DESC)
        ");
        
        $this->db->query("
            IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='idx_history_action')
            CREATE INDEX idx_history_action ON digemid_matching_history(action, created_at DESC)
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS digemid_matching_history");
        $this->db->query("DROP TABLE IF EXISTS digemid_matching_suggestions");
    }
}
