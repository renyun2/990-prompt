# 会员注册管理系统

一个现代化的PHP会员注册管理系统，具有完整的用户注册、登录、个人资料管理和后台管理功能。

## 🛠 技术栈

- **后端**: PHP 8.2 (Apache)
- **数据库**: MySQL 8.0
- **前端**: Tailwind CSS + HTML5
- **容器化**: Docker Compose
- **密钥管理**: bcrypt密码加密

## 🎯 功能特性

### 用户功能
- ✅ 会员注册（邮箱/用户名验证）
- ✅ 会员登录（带"记住我"功能）
- ✅ 个人资料修改
- ✅ 密码修改
- ✅ 登录日志查看
- ✅ 安全退出

### 管理功能
- ✅ 用户列表管理
- ✅ 用户启用/禁用
- ✅ 用户删除
- ✅ 角色切换（管理员/普通用户）
- ✅ 系统统计
- ✅ 登录日志查看

## ⚠️ 安全须知（重要，请先阅读）

### 关于密码与敏感信息

`docker-compose.yml` **不再直接存储任何密码**。所有凭据通过项目根目录下的 `.env` 文件注入，该文件已被 `.gitignore` 排除，**不会被提交到版本控制系统**。

`.env.example` 是示例模板，其中的默认值仅供**本地开发/演示**使用。**部署到任何可被他人访问的环境前，必须修改为强密码。**

> **审查说明**  
> 早期版本曾将 `MYSQL_ROOT_PASSWORD: root123456` 等明文密码直接写在 `docker-compose.yml` 中，存在一旦仓库泄露即暴露数据库凭据的风险。当前版本已通过 `${MYSQL_ROOT_PASSWORD}` 等变量引用方式将所有凭据外置到 `.env` 文件，并在 `.gitignore` 中排除该文件。

### 密码强度建议

| 场景 | 建议 |
|------|------|
| 本地开发 | 可沿用 `.env.example` 中的默认值 |
| 测试/预发布 | 修改为随机生成的强密码（≥16位） |
| 生产环境 | 使用密钥管理服务（Vault、AWS Secrets Manager 等），**禁止使用 `.env` 文件** |

---

## 🚀 快速启动

### 前置要求
- Docker Desktop（已安装）
- git（可选）

### 启动步骤

1. **进入项目目录**
```bash
cd <your-project-path>
```

2. **创建 `.env` 配置文件**（首次启动必须执行）
```bash
cp .env.example .env
```
> 若用于非本地环境，请编辑 `.env` 文件，将 `MYSQL_ROOT_PASSWORD`、`DB_PASSWORD` 等修改为强密码后再执行后续步骤。

3. **启动容器**
```bash
docker compose up --build
```

首次启动需等待 MySQL 初始化，通常需要 1-2 分钟。

4. **访问应用**
- 打开浏览器访问：http://localhost:8080

5. **初始化数据库**
- 首次访问会看到"数据库未初始化"提示
- 点击"初始化数据库"按钮，等待 3-5 秒
- 初始化成功后自动跳转到登录页

## 🔗 服务地址

| 服务 | 地址 | 用途 |
|-----|------|------|
| 前端应用 | http://localhost:8080 | 会员系统主应用 |
| MySQL数据库 | localhost:3306 | 数据库连接 |

## 🧪 测试账户

系统预置了以下测试账户：

### 管理员账户
```
用户名: admin
密码: admin123456
```

### 普通用户账户
```
用户名: user1 - user5
密码: user123456
```

## 📋 数据库架构

系统会自动创建以下数据表：

### 用户表 (users)
- 用户基本信息、角色、状态
- 邮箱为可选字段（可为NULL）

### 登录日志表 (login_logs)
- 记录每次登录的IP地址和时间

## 🗄️ 数据库配置指南

### 默认配置

系统默认使用以下数据库配置（来自 `.env.example`，**生产环境请务必修改**）：

| 配置项 | `.env.example` 示例值 | 说明 |
|--------|--------|------|
| **数据库主机** | `db` | Docker 容器服务名 |
| **数据库端口** | `3306` | MySQL 默认端口 |
| **数据库名称** | `member_system` | 应用数据库名 |
| **数据库用户** | `member_user` | 应用数据库用户 |
| **数据库密码** | `member_pass123` | 应用数据库密码（请修改） |
| **Root密码** | `root123456` | MySQL root 用户密码（请修改） |

### 修改数据库配置

#### 方法1：修改 `.env` 文件（推荐）

```bash
# 编辑 .env，修改以下变量
MYSQL_ROOT_PASSWORD=your_strong_root_password   # 修改 root 密码
DB_USER=your_db_user                            # 修改数据库用户
DB_PASSWORD=your_strong_db_password            # 修改数据库密码
DB_NAME=your_database_name                     # 修改数据库名
```

`docker-compose.yml` 中已通过 `${VAR}` 语法读取这些变量，**无需直接修改 `docker-compose.yml`**。

**重要提示：**
- 修改 `.env` 后需要重建容器：`docker compose up --build`
- 数据库已有数据时，先备份再操作：`docker compose down -v` 会清空所有数据

#### 方法2：通过配置文件修改

编辑 `app/src/config/config.php` 文件：

```php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'member_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'member_pass123');
define('DB_NAME', getenv('DB_NAME') ?: 'member_system');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
```

**注意：** 如果使用环境变量，环境变量的优先级高于配置文件中的默认值。

### 外部工具连接数据库

#### 使用MySQL客户端连接

```bash
# 使用命令行连接
mysql -h localhost -P 3306 -u member_user -pmember_pass123 member_system

# 或使用root用户连接
mysql -h localhost -P 3306 -u root -proot123456
```

#### 使用图形化工具连接

**Navicat / DBeaver / MySQL Workbench / phpMyAdmin 等工具连接参数：**

```
主机: localhost
端口: 3306
用户名: member_user
密码: member_pass123
数据库: member_system
```

**Root用户连接参数：**

```
主机: localhost
端口: 3306
用户名: root
密码: root123456
```

#### 使用phpMyAdmin（可选）

如果需要图形化管理界面，可以在 `docker-compose.yml` 中添加phpMyAdmin服务：

```yaml
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: member_phpmyadmin
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root123456
    ports:
      - "8081:80"
    depends_on:
      - db
    networks:
      - app_network
```

然后访问：http://localhost:8081

### 数据库备份与恢复

#### 备份数据库

```bash
# 备份整个数据库
docker compose exec db mysqldump -u root -proot123456 member_system > backup.sql

# 备份特定表
docker compose exec db mysqldump -u root -proot123456 member_system users login_logs > tables_backup.sql

# 备份到容器内（推荐用于生产环境）
docker compose exec db mysqldump -u root -proot123456 member_system > /var/lib/mysql/backup_$(date +%Y%m%d_%H%M%S).sql
```

#### 恢复数据库

```bash
# 从备份文件恢复
docker compose exec -T db mysql -u root -proot123456 member_system < backup.sql

# 从容器内备份恢复
docker compose exec db mysql -u root -proot123456 member_system < /var/lib/mysql/backup_20240123_120000.sql
```

#### 导出/导入数据

```bash
# 导出数据
docker compose exec db mysql -u root -proot123456 -e "SELECT * FROM member_system.users" > users_export.csv

# 导入数据（需要先准备CSV文件）
docker compose exec -T db mysql -u root -proot123456 member_system -e "LOAD DATA INFILE '/var/lib/mysql/users.csv' INTO TABLE users FIELDS TERMINATED BY ','"
```

### 数据库连接测试

#### 在容器内测试连接

```bash
# 进入Web容器
docker compose exec web bash

# 测试数据库连接
php -r "
\$conn = new mysqli('db', 'member_user', 'member_pass123', 'member_system', 3306);
if (\$conn->connect_error) {
    die('连接失败: ' . \$conn->connect_error);
}
echo '数据库连接成功！\n';
\$conn->close();
"
```

#### 查看数据库状态

```bash
# 查看数据库运行状态
docker compose exec db mysqladmin -u root -proot123456 status

# 查看数据库版本
docker compose exec db mysql -u root -proot123456 -e "SELECT VERSION();"

# 查看所有数据库
docker compose exec db mysql -u root -proot123456 -e "SHOW DATABASES;"

# 查看数据库大小
docker compose exec db mysql -u root -proot123456 -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'member_system' GROUP BY table_schema;"
```

### 常见配置问题

#### Q: 修改密码后无法连接？

**A:** 确保 `docker-compose.yml` 中 `web` 服务的环境变量与 `db` 服务的配置一致：

```yaml
# db服务
MYSQL_USER: member_user
MYSQL_PASSWORD: member_pass123

# web服务（必须一致）
DB_USER: member_user
DB_PASSWORD: member_pass123
```

#### Q: 如何重置数据库密码？

**A:** 

1. 停止容器：`docker compose down`
2. 删除数据卷：`docker compose down -v`（**注意：会删除所有数据**）
3. 修改 `docker-compose.yml` 中的密码
4. 重新启动：`docker compose up --build`

#### Q: 数据库连接超时？

**A:** 

1. 检查数据库容器是否正常运行：`docker compose ps`
2. 检查数据库健康状态：`docker compose logs db`
3. 等待数据库完全启动（首次启动需要30-60秒）
4. 检查网络连接：`docker compose exec web ping db`

#### Q: 如何迁移到外部数据库？

**A:** 

1. 修改 `docker-compose.yml`，移除 `db` 服务
2. 修改 `web` 服务的环境变量：

```yaml
web:
  environment:
    DB_HOST: your_external_db_host      # 外部数据库地址
    DB_PORT: 3306                       # 外部数据库端口
    DB_USER: your_db_user
    DB_PASSWORD: your_db_password
    DB_NAME: member_system
```

3. 确保外部数据库已创建数据库和用户
4. 重新启动：`docker compose up --build`

### 性能优化建议

1. **生产环境建议：**
   - 使用强密码（至少16位，包含大小写字母、数字、特殊字符）
   - 限制数据库用户权限（只授予必要的权限）
   - 定期备份数据库
   - 监控数据库性能

2. **开发环境建议：**
   - 可以使用默认配置
   - 定期清理测试数据

3. **安全建议：**
   - `.env` 文件已被 `.gitignore` 排除，**永远不要手动将其添加到 git 提交中**
   - `.env.example` 中的示例密码仅供本地开发，上线前必须替换
   - 生产环境应使用密钥管理服务（HashiCorp Vault、AWS Secrets Manager、Docker Secrets 等）替代 `.env` 文件
   - 定期更新 MySQL 版本以修复安全漏洞

## 🔐 安全特性

- ✅ bcrypt 密码加密
- ✅ SQL 防注入（PDO 参数化查询）
- ✅ XSS 防护（HTML 转义）
- ✅ CSRF 防护（所有表单均含 token 验证）
- ✅ 会话管理
- ✅ 敏感凭据外置到 `.env`，不提交至版本控制
- ✅ 登录错误提示（用户名不存在、密码错误、账户禁用）

## 🏗️ 项目结构

```
project/
├── app/
│   ├── public/
│   │   └── index.php               # 路由入口（仅做分发，不含业务逻辑）
│   └── src/
│       ├── config/
│       │   ├── config.php          # 配置文件（读取环境变量）
│       │   ├── Database.php        # 数据库连接类（PDO 单例）
│       │   └── DBInitializer.php   # 数据库初始化
│       ├── controllers/
│       │   ├── AuthController.php  # 登录 / 注册业务逻辑
│       │   ├── ProfileController.php # 个人资料 / 密码修改
│       │   └── AdminController.php # 后台管理所有操作
│       ├── middleware/
│       │   └── AuthMiddleware.php  # 认证中间件
│       ├── utils/
│       │   └── Helper.php          # 工具类（验证、加密、CSRF）
│       └── views/
│           ├── layout.php          # 主布局
│           └── pages/
│               ├── home.php        # 首页
│               ├── register.php    # 注册页
│               ├── login.php       # 登录页
│               ├── profile.php     # 个人资料
│               └── admin.php       # 后台管理
├── Dockerfile                      # PHP 容器配置
├── docker-compose.yml              # 容器编排（凭据通过 ${VAR:-默认值} 从 .env 读取）
├── .env.example                    # 环境变量模板（仅含示例值，可安全提交）
├── .env                            # 真实环境变量（已在 .gitignore 中排除）
├── .gitignore                      # 已包含 .env，防止凭据泄露
├── .dockerignore                   # Docker 忽略文件（已排除 .env）
└── README.md                       # 本文档
```

## 🔧 开发指南

### 查看日志

```bash
# 查看Web服务日志
docker compose logs -f web

# 查看数据库日志
docker compose logs -f db
```

### 数据库管理

```bash
# 进入MySQL命令行
docker compose exec db mysql -u member_user -pmember_pass123 member_system

# 查看所有用户
docker compose exec db mysql -u member_user -pmember_pass123 member_system -e "SELECT username, email, role FROM users;"
```

**详细数据库配置指南请查看 [数据库配置指南](#-数据库配置指南) 章节**

### 重置系统

```bash
# 停止并删除所有数据（⚠️ 会清空数据库）
docker compose down -v

# 重新启动
docker compose up --build
```

## 🐛 常见问题

### Q: 容器启动失败？
**A:** 检查Docker Desktop是否正常运行，查看日志：`docker compose logs`

### Q: 无法访问 http://localhost:8080？
**A:** 
1. 确认容器已启动：`docker compose ps`（应显示 web 和 db 都是 Up 状态）
2. 检查8080端口是否被占用

### Q: 登录失败？
**A:** 
1. 确保数据库已初始化（访问首页会提示）
2. 使用正确的测试账户：`admin` / `admin123456`
3. 查看页面错误提示（用户名不存在、密码错误等）

### Q: 如何重置所有数据？
**A:** 
```bash
docker compose down -v  # 删除数据卷
docker compose up --build  # 重新启动
```

---

## 📝 许可证

MIT License