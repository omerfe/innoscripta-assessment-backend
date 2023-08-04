# All The News - Backend

This is the backend repository for the "All The News" project, a news aggregation and browsing platform. The backend is built using the Laravel PHP framework and provides API endpoints to interact with news articles, sources, categories, and user-related data.

## Project Overview

The "All The News" project aims to provide users with a centralized platform to browse and discover news articles from various sources. The backend is responsible for handling data storage, retrieval, and user authentication. It communicates with the frontend, built with Next.js 13, to provide a seamless user experience.

## Live URLs

-   Backend Laravel Project Live URL: [https://innoscripta-assessment-backend-production.up.railway.app/](https://innoscripta-assessment-backend-production.up.railway.app/)
-   Next.js 13 Frontend Project Live URL: [https://innoscripta-assessment-frontend.vercel.app/](https://innoscripta-assessment-frontend.vercel.app/)

## How to Run the Backend Locally

To run the backend locally, follow these steps:

### Prerequisites

1. Ensure you have Docker installed on your machine.

### Setup

1. Clone this repository to your local machine.

```bash
git clone <backend-repo-url>
```

2. Checkout to the "docker-build" branch to access the Docker files. (The "main" branch doesn't contain the Docker files because Railway had problems building the Docker image. Instead it uses nixpacks.)

```bash
git checkout docker-build
```

3. Fill in the required env variables in the .env file. I've attached the required local .env file in the email since it has sensitive information and the repo is public.
   <br>

4. In the root directory of the backend project, run the setup.sh script to build and start the Docker containers.

```bash
bash setup.sh
```

5. Once the setup is complete, you can access the backend API at http://localhost. The API endpoints are exposed at http://localhost/api.

## API Endpoints

The backend provides the following API endpoints to interact with the data:

-   /api/articles: Retrieve a list of news articles.
-   /api/articles/{id}: Retrieve a single news article by its ID.
-   /api/sources: Retrieve a list of news sources.
-   /api/categories: Retrieve a list of news categories.
-   /api/register: Register a new user account.
-   /api/login: Authenticate and log in a user.
-   /api/user: Get the authenticated user's information.
-   /api/articles/{id}/favorites: Get the list of articles favorited by the user.
-   /api/categories/{id}/favorites: Get the list of categories favorited by the user.

## Database Schema

The backend uses a MySQL database to store articles, sources, categories, and user-related data. The database credentials can be configured via environment variables.

## Scheduled tasks and content scraping

The backend uses Laravel's task scheduling feature to scrape news articles from various sources and store them in the database. The scraping is done using the Symfony Browser kit component. The scraping tasks are scheduled to run every day. The scraping tasks can be found in the app/Console/Commands directory.

Thank you for your interest in "All The News"!
