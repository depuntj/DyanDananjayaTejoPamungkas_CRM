# Smart CRM - ISP Customer Relationship Management System

A comprehensive CRM system built for PT. Smart, an Internet Service Provider (ISP) company, to manage leads, projects, products, and customers. This application streamlines the sales workflow from lead acquisition to customer conversion.

## Features

### User Management

- Role-based access control (Admin, Manager, Sales)
- User authentication and profile management

### Lead Management

- Create and track potential customers (leads)
- Assign leads to sales personnel
- Track lead status (new, contacted, qualified, proposal, negotiation, lost, converted)
- Detailed lead information tracking

### Project Management

- Create projects from leads
- Add services and products to projects
- Manager approval workflow
- Project status tracking (pending, approved, rejected, in_progress, completed)

### Product Catalog

- Manage internet service offerings
- Multiple service types (Residential, Business, Enterprise)
- Track service details (speed, price, description)

### Customer Management

- Convert approved projects to customers
- Manage customer services and subscriptions
- Track service renewals and status

### Dashboard

- Overview of key metrics
- Recent leads and projects
- Pending approvals

## Technologies Used

- **Backend**: Laravel 11
- **Frontend**: Vue 3 with Inertia.js
- **Database**: PostgreSQL
- **UI**: Tailwind CSS with shadcn/ui components
- **Authentication**: Laravel Breeze

## Installation

### Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js and NPM
- PostgreSQL 14

### Setup Instructions

1. Clone the repository

```bash
git clone https://github.com/yourusername/smart_crm.git
cd smart_crm
```

2. Install PHP dependencies

```bash
composer install
```

3. Install JavaScript dependencies

```bash
npm install
```

4. Configure environment variables

```bash
cp .env.example .env
```

5. Update `.env` file with your database credentials

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_crm
DB_USERNAME=postgres
DB_PASSWORD=yourpassword
```

6. Generate application key

```bash
php artisan key:generate
```

7. Create PostgreSQL database

```bash
createdb smart_crm
```

8. Run database migrations

```bash
php artisan migrate
```

9. Seed the database with initial data

```bash
php artisan db:seed
```

10. Build frontend assets

```bash
npm run build
```

11. Start the development server

```bash
php artisan serve
```

## Default Users

After running the database seeders, the following users will be available:

| Email               | Password | Role    |
| ------------------- | -------- | ------- |
| admin@ptsmart.com   | password | Admin   |
| manager@ptsmart.com | password | Manager |
| sales1@ptsmart.com  | password | Sales   |
| sales2@ptsmart.com  | password | Sales   |

## Usage Guide

### Login

Access the application and login with your credentials.

### Sales Process Workflow

1. **Create a Lead**: Capture potential customer information
2. **Create a Project**: When a lead shows interest, create a project with the required products
3. **Project Approval**: Managers review and approve/reject projects
4. **Convert to Customer**: Once a project is approved, convert it to a customer
5. **Manage Services**: Add/modify services for the customer

### Role-Based Permissions

#### Admin

- Full access to all features
- User management
- System configuration

#### Manager

- Approve or reject projects
- Manage sales team
- View all leads and projects
- Convert approved projects to customers

#### Sales

- Create and manage leads
- Create projects (requires manager approval)
- Convert approved projects to customers
- View assigned leads and projects

## Data Structure

### Products

The system comes preloaded with several internet service packages:

- Residential packages (Basic, Plus, Premium)
- Business packages (Basic, Plus, Premium)
- Enterprise package

### Project Status Flow

- **Pending**: Initial state, awaiting manager approval
- **Approved**: Project approved by manager, ready for conversion
- **Rejected**: Project rejected by manager
- **In Progress**: Implementation in progress
- **Completed**: Project fully implemented and converted to customer

### Lead Status Flow

- **New**: Initial state
- **Contacted**: Initial communication established
- **Qualified**: Lead qualified as potential customer
- **Proposal**: Project proposal created
- **Negotiation**: In negotiation phase
- **Lost**: Failed to convert
- **Converted**: Successfully converted to customer

## License

This project is open-sourced software.

## Additional Information

For any questions or issues, please contact the development team or refer to the documentation.
