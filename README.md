# Cynthia Brown - Portfolio

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
A custom-built developer portfolio with a PHP and MySQL backend, featuring a secure admin panel protected by Google and GitHub OAuth authentication. Built from scratch with a REST API, session-based authorization, and a dynamic frontend that pulls all content live from the database.

## File Structure
```
portfolio/
├── config/
│   └── config.php          ← EDIT THIS FIRST
├── includes/
│   ├── auth.php
│   ├── db.php
│   └── response.php
├── api/
│   ├── auth/
│   │   ├── google/
│   │   │   ├── login.php
│   │   │   └── callback.php
│   │   ├── github/
│   │   │   ├── login.php
│   │   │   └── callback.php
│   │   ├── logout.php
│   │   └── status.php
│   ├── projects/
│   │   └── index.php
│   └── settings/
│       └── index.php
├── public/
│   ├── index.php           ← Portfolio homepage
│   └── admin/
│       ├── index.php       ← Admin dashboard
│       └── login.php       ← Login page
├── schema.sql
├── .htaccess
└── README.md