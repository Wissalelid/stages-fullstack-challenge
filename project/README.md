# 📦 Blog Platform - Installation Guide

## 🚀 Quick Start

### Prerequisites
- Docker Desktop installed
- Git
- 8GB RAM minimum

### Installation Steps

```bash
# 1. Navigate to project folder
cd project

# 2. Start Docker containers
docker-compose up -d

# 3. Wait for containers to start (30-60 seconds)
docker ps

# 4. Install backend dependencies
docker exec -it blog_backend composer install

# 5. Setup Laravel
docker exec -it blog_backend cp env.example .env
docker exec -it blog_backend php artisan key:generate

# 6. Run migrations and seeders
docker exec -it blog_backend php artisan migrate:fresh --seed

# 7. Install frontend dependencies (if needed)
docker exec -it blog_frontend npm install
```

### 🌐 Access the Application

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **MySQL**: localhost:3306

### 🔑 Default Credentials

```
Email: admin@blog.com
Password: Admin123!
```

⚠️ **Note**: The password is stored in plain text (SEC-001 bug)

---

## 🛠️ Useful Commands

### Backend Commands

```bash
# Access backend container
docker exec -it blog_backend bash

# Run migrations
docker exec -it blog_backend php artisan migrate

# Seed database
docker exec -it blog_backend php artisan db:seed

# Clear cache
docker exec -it blog_backend php artisan cache:clear

# View logs
docker logs blog_backend -f
```

### Frontend Commands

```bash
# Access frontend container
docker exec -it blog_frontend sh

# Rebuild frontend
docker exec -it blog_frontend npm run build

# View logs
docker logs blog_frontend -f
```

### Database Commands

```bash
# Access MySQL
docker exec -it blog_mysql mysql -u blog_user -p
# Password: blog_password

# Backup database
docker exec blog_mysql mysqldump -u blog_user -pblog_password blog_db > backup.sql

# Restore database
docker exec -i blog_mysql mysql -u blog_user -pblog_password blog_db < backup.sql
```

---

## 🔄 Restart / Stop

```bash
# Stop all containers
docker-compose down

# Stop and remove volumes (⚠️ deletes database)
docker-compose down -v

# Restart containers
docker-compose restart

# Rebuild containers (after Dockerfile changes)
docker-compose up -d --build
```

---

## 🐛 Troubleshooting

### Port already in use

If ports 3000, 8000, or 3306 are already used:

```bash
# Check what's using the port
lsof -i :3000
lsof -i :8000

# Kill the process or change ports in docker-compose.yml
```

### Database connection refused

```bash
# Check MySQL is running
docker ps | grep mysql

# Restart MySQL
docker-compose restart mysql

# Wait 30 seconds and try again
```

### Permission errors (Linux/Mac)

```bash
# Fix Laravel storage permissions
docker exec -it blog_backend chown -R www-data:www-data /var/www/html/storage
docker exec -it blog_backend chmod -R 775 /var/www/html/storage
```

### Frontend not loading

```bash
# Rebuild frontend
docker-compose restart frontend
docker logs blog_frontend -f

# Check if Vite is running on port 3000
```

---

## 📁 Project Structure

```
/project/
├── docker-compose.yml          # Docker orchestration
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Models/            # Database models
│   │   ├── Http/Controllers/  # API controllers
│   │   └── ...
│   ├── database/
│   │   ├── migrations/        # Database schema
│   │   └── seeders/           # Sample data
│   ├── routes/api.php         # API routes
│   └── .env.example
├── frontend/                   # React application
│   ├── src/
│   │   ├── components/        # React components
│   │   ├── services/          # API calls
│   │   └── App.jsx
│   └── package.json
└── README.md                   # This file
```

---

## 🧪 Testing the Bugs

Once the application is running, you can test the bugs:

### [BUG-001] Search with accents
1. Create an article with title "Le café du matin"
2. Search for "café" → No results ❌
3. Search for "cafe" → Found ✅

### [BUG-002] Delete last comment
1. Create an article with 1 comment
2. Try to delete it → Error 500 ❌

### [BUG-003] Upload large image
1. Try uploading an image > 2MB → Error 413 ❌

### [BUG-004] Date format
1. Look at any article date → Shows US format/timezone ❌

### [SEC-001] Passwords in plain text
```bash
docker exec -it blog_mysql mysql -u blog_user -pblog_password blog_db
SELECT email, password FROM users;
# Passwords are visible in plain text ❌
```

### [PERF-001] Slow article list
1. Open browser DevTools → Network tab
2. Load article list
3. Check backend logs → 101 SQL queries for 50 articles ❌

---

## 📞 Need Help?

If you're stuck:
1. Check Docker logs: `docker logs blog_backend -f`
2. Verify all containers are running: `docker ps`
3. Check the main CHALLENGE.md for more details
4. Contact the recruiter if blocked > 2 hours

---

Good luck! 🚀

