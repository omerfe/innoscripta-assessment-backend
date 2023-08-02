#!/bin/bash

# Start the Docker containers
docker-compose up -d

# Wait for the MySQL container to be ready
echo "Waiting for the MySQL container to be ready..."
waitfor=10
sleep $waitfor

echo "MySQL container is ready. Applying database migrations..."
# Run database migrations
docker-compose exec app php artisan migrate

echo "Migrations applied. Seeding the database with news articles from the API..."
# Seed the database with news articles from the API
docker-compose exec app php artisan app:fetch-news-api-articles

echo "News articles seeded. Seeding the database with articles from The Guardian API..."
# Seed the database with articles from The Guardian API
docker-compose exec app php artisan app:fetch-the-guardian-api-articles

echo "Setup completed! You can now visit http://localhost/api/articles to see the articles."
