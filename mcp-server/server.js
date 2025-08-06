#!/usr/bin/env node

/**
 * Tjboba MCP Server
 * Model Context Protocol server untuk project Laravel Tjboba
 * Fitur: Database query, Laravel commands, file system, Git integration
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ErrorCode,
  ListToolsRequestSchema,
  McpError,
} from '@modelcontextprotocol/sdk/types.js';
import sql from 'mssql';
import { exec } from 'child_process';
import { promises as fs } from 'fs';
import path from 'path';
import { promisify } from 'util';

const execAsync = promisify(exec);

class TjbobaMCPServer {
  constructor() {
    this.server = new Server(
      {
        name: 'tjboba-mcp-server',
        version: '1.0.0',
      },
      {
        capabilities: {
          tools: {},
        },
      }
    );

    // Konfigurasi default
    this.config = {
      projectPath: process.env.TJBOBA_PROJECT_PATH || '/path/to/tjboba',
      database: {
        server: process.env.DB_HOST || 'localhost',
        database: process.env.DB_DATABASE || 'tjboba_db',
        user: process.env.DB_USERNAME || 'sa',
        password: process.env.DB_PASSWORD || '',
        options: {
          encrypt: false, // SQL Server 2008 compatibility
          trustServerCertificate: true
        }
      }
    };

    this.setupHandlers();
  }

  setupHandlers() {
    // List available tools
    this.server.setRequestHandler(ListToolsRequestSchema, async () => {
      return {
        tools: [
          // Database Tools
          {
            name: 'query_database',
            description: 'Execute SQL query pada database Tjboba (SQL Server 2008)',
            inputSchema: {
              type: 'object',
              properties: {
                query: {
                  type: 'string',
                  description: 'SQL query to execute (SELECT, INSERT, UPDATE, DELETE)'
                },
                params: {
                  type: 'array',
                  description: 'Query parameters untuk prepared statements',
                  items: { type: 'string' }
                }
              },
              required: ['query']
            }
          },
          {
            name: 'get_table_schema',
            description: 'Get schema informasi untuk tabel tertentu',
            inputSchema: {
              type: 'object',
              properties: {
                table_name: {
                  type: 'string',
                  description: 'Nama tabel yang ingin dilihat schema-nya'
                }
              },
              required: ['table_name']
            }
          },

          // Laravel Tools
          {
            name: 'artisan_command',
            description: 'Execute Laravel Artisan command',
            inputSchema: {
              type: 'object',
              properties: {
                command: {
                  type: 'string',
                  description: 'Artisan command (e.g., migrate, make:controller, route:list)'
                },
                options: {
                  type: 'string',
                  description: 'Additional options for the command'
                }
              },
              required: ['command']
            }
          },
          {
            name: 'read_laravel_config',
            description: 'Read Laravel configuration files',
            inputSchema: {
              type: 'object',
              properties: {
                config_file: {
                  type: 'string',
                  description: 'Config file name (app, database, cache, etc.)',
                  enum: ['app', 'database', 'cache', 'queue', 'mail', 'filesystems']
                }
              },
              required: ['config_file']
            }
          },
          {
            name: 'read_env_file',
            description: 'Read .env file contents',
            inputSchema: {
              type: 'object',
              properties: {
                env_file: {
                  type: 'string',
                  description: 'Environment file to read (.env, .env.local, etc.)',
                  default: '.env'
                }
              }
            }
          },

          // File System Tools
          {
            name: 'read_project_file',
            description: 'Read any file from project directory',
            inputSchema: {
              type: 'object',
              properties: {
                file_path: {
                  type: 'string',
                  description: 'Relative path to file from project root'
                }
              },
              required: ['file_path']
            }
          },
          {
            name: 'list_directory',
            description: 'List contents of directory',
            inputSchema: {
              type: 'object',
              properties: {
                directory_path: {
                  type: 'string',
                  description: 'Relative path to directory from project root',
                  default: '.'
                },
                recursive: {
                  type: 'boolean',
                  description: 'List recursively',
                  default: false
                }
              }
            }
          },
          {
            name: 'find_files',
            description: 'Find files by pattern (controller, model, view, etc.)',
            inputSchema: {
              type: 'object',
              properties: {
                pattern: {
                  type: 'string',
                  description: 'File pattern to search (*.php, *Controller.php, etc.)'
                },
                directory: {
                  type: 'string',
                  description: 'Directory to search in',
                  default: '.'
                }
              },
              required: ['pattern']
            }
          },

          // Git Tools
          {
            name: 'git_status',
            description: 'Get Git repository status',
            inputSchema: {
              type: 'object',
              properties: {}
            }
          },
          {
            name: 'git_log',
            description: 'Get Git commit history',
            inputSchema: {
              type: 'object',
              properties: {
                limit: {
                  type: 'number',
                  description: 'Number of commits to show',
                  default: 10
                }
              }
            }
          },

          // Monitoring Tools
          {
            name: 'check_laravel_logs',
            description: 'Read Laravel log files',
            inputSchema: {
              type: 'object',
              properties: {
                log_file: {
                  type: 'string',
                  description: 'Log file name (laravel.log, or date like 2024-01-15)',
                  default: 'laravel.log'
                },
                lines: {
                  type: 'number',
                  description: 'Number of lines to read from end',
                  default: 50
                }
              }
            }
          },
          {
            name: 'composer_info',
            description: 'Get Composer package information',
            inputSchema: {
              type: 'object',
              properties: {
                command: {
                  type: 'string',
                  description: 'Composer command (show, outdated, diagnose)',
                  enum: ['show', 'outdated', 'diagnose', 'validate'],
                  default: 'show'
                }
              }
            }
          }
        ]
      };
    });

    // Handle tool calls
    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      const { name, arguments: args } = request.params;

      try {
        switch (name) {
          case 'query_database':
            return await this.queryDatabase(args);
          case 'get_table_schema':
            return await this.getTableSchema(args);
          case 'artisan_command':
            return await this.executeArtisan(args);
          case 'read_laravel_config':
            return await this.readLaravelConfig(args);
          case 'read_env_file':
            return await this.readEnvFile(args);
          case 'read_project_file':
            return await this.readProjectFile(args);
          case 'list_directory':
            return await this.listDirectory(args);
          case 'find_files':
            return await this.findFiles(args);
          case 'git_status':
            return await this.getGitStatus();
          case 'git_log':
            return await this.getGitLog(args);
          case 'check_laravel_logs':
            return await this.checkLaravelLogs(args);
          case 'composer_info':
            return await this.getComposerInfo(args);
          default:
            throw new McpError(ErrorCode.MethodNotFound, `Unknown tool: ${name}`);
        }
      } catch (error) {
        return {
          content: [
            {
              type: 'text',
              text: `Error executing ${name}: ${error.message}`
            }
          ],
          isError: true
        };
      }
    });
  }

  // Database Methods
  async queryDatabase(args) {
    try {
      const pool = await sql.connect(this.config.database);
      const request = pool.request();
      
      // Add parameters if provided
      if (args.params) {
        args.params.forEach((param, index) => {
          request.input(`param${index}`, param);
        });
      }

      const result = await request.query(args.query);
      await pool.close();

      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              recordset: result.recordset,
              rowsAffected: result.rowsAffected,
              output: result.output
            }, null, 2)
          }
        ]
      };
    } catch (error) {
      throw new Error(`Database query failed: ${error.message}`);
    }
  }

  async getTableSchema(args) {
    const query = `
      SELECT 
        COLUMN_NAME,
        DATA_TYPE,
        IS_NULLABLE,
        COLUMN_DEFAULT,
        CHARACTER_MAXIMUM_LENGTH
      FROM INFORMATION_SCHEMA.COLUMNS 
      WHERE TABLE_NAME = '${args.table_name}'
      ORDER BY ORDINAL_POSITION
    `;
    
    return await this.queryDatabase({ query });
  }

  // Laravel Methods
  async executeArtisan(args) {
    const command = `php artisan ${args.command}`;
    const options = args.options ? ` ${args.options}` : '';
    
    try {
      const { stdout, stderr } = await execAsync(command + options, {
        cwd: this.config.projectPath
      });
      
      return {
        content: [
          {
            type: 'text',
            text: stdout || stderr || 'Command executed successfully'
          }
        ]
      };
    } catch (error) {
      throw new Error(`Artisan command failed: ${error.message}`);
    }
  }

  async readLaravelConfig(args) {
    const configPath = path.join(this.config.projectPath, 'config', `${args.config_file}.php`);
    
    try {
      const content = await fs.readFile(configPath, 'utf8');
      return {
        content: [
          {
            type: 'text',
            text: content
          }
        ]
      };
    } catch (error) {
      throw new Error(`Failed to read config file: ${error.message}`);
    }
  }

  async readEnvFile(args) {
    const envPath = path.join(this.config.projectPath, args.env_file || '.env');
    
    try {
      const content = await fs.readFile(envPath, 'utf8');
      return {
        content: [
          {
            type: 'text',
            text: content
          }
        ]
      };
    } catch (error) {
      throw new Error(`Failed to read .env file: ${error.message}`);
    }
  }

  // File System Methods
  async readProjectFile(args) {
    const filePath = path.join(this.config.projectPath, args.file_path);
    
    try {
      const content = await fs.readFile(filePath, 'utf8');
      return {
        content: [
          {
            type: 'text',
            text: content
          }
        ]
      };
    } catch (error) {
      throw new Error(`Failed to read file: ${error.message}`);
    }
  }

  async listDirectory(args) {
    const dirPath = path.join(this.config.projectPath, args.directory_path || '.');
    
    try {
      const items = await fs.readdir(dirPath, { withFileTypes: true });
      const result = items.map(item => ({
        name: item.name,
        type: item.isDirectory() ? 'directory' : 'file'
      }));
      
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify(result, null, 2)
          }
        ]
      };
    } catch (error) {
      throw new Error(`Failed to list directory: ${error.message}`);
    }
  }

  async findFiles(args) {
    const command = `find ${args.directory || '.'} -name "${args.pattern}" -type f`;
    
    try {
      const { stdout } = await execAsync(command, {
        cwd: this.config.projectPath
      });
      
      return {
        content: [
          {
            type: 'text',
            text: stdout || 'No files found'
          }
        ]
      };
    } catch (error) {
      throw new Error(`Find command failed: ${error.message}`);
    }
  }

  // Git Methods
  async getGitStatus() {
    try {
      const { stdout } = await execAsync('git status --porcelain', {
        cwd: this.config.projectPath
      });
      
      return {
        content: [
          {
            type: 'text',
            text: stdout || 'Working tree clean'
          }
        ]
      };
    } catch (error) {
      throw new Error(`Git status failed: ${error.message}`);
    }
  }

  async getGitLog(args) {
    const limit = args.limit || 10;
    const command = `git log --oneline -${limit}`;
    
    try {
      const { stdout } = await execAsync(command, {
        cwd: this.config.projectPath
      });
      
      return {
        content: [
          {
            type: 'text',
            text: stdout || 'No commits found'
          }
        ]
      };
    } catch (error) {
      throw new Error(`Git log failed: ${error.message}`);
    }
  }

  // Monitoring Methods
  async checkLaravelLogs(args) {
    const logFile = args.log_file || 'laravel.log';
    const lines = args.lines || 50;
    const logPath = path.join(this.config.projectPath, 'storage/logs', logFile);
    
    try {
      const { stdout } = await execAsync(`tail -${lines} "${logPath}"`);
      
      return {
        content: [
          {
            type: 'text',
            text: stdout || 'Log file is empty'
          }
        ]
      };
    } catch (error) {
      throw new Error(`Failed to read log file: ${error.message}`);
    }
  }

  async getComposerInfo(args) {
    const command = `composer ${args.command || 'show'}`;
    
    try {
      const { stdout } = await execAsync(command, {
        cwd: this.config.projectPath
      });
      
      return {
        content: [
          {
            type: 'text',
            text: stdout
          }
        ]
      };
    } catch (error) {
      throw new Error(`Composer command failed: ${error.message}`);
    }
  }

  async run() {
    const transport = new StdioServerTransport();
    await this.server.connect(transport);
    console.error('Tjboba MCP Server running on stdio');
  }
}

// Start server
const server = new TjbobaMCPServer();
server.run().catch(console.error);