# MediMax

MediMax is a responsive pharmacy and wellness storefront prototype built with plain PHP, HTML, CSS, and vanilla JavaScript. It includes a customer shopping experience, an administration interface, reusable PHP views, product imagery, and lightweight client-side interactions.

> **Project status:** This repository is currently a front-end and server-rendered UI prototype. Product, cart, wishlist, order, and user records come from `app/models/DummyData.php`; there is no database, persistent session, real authentication, checkout processing, or API integration yet.

## Highlights

- Responsive pharmacy storefront with a branded home page
- Product catalogue with search, category filters, and client-side sorting
- Product cards with cart and wishlist visual feedback
- Cart, wishlist, orders, login, registration, about, and contact views
- Admin dashboard, product, user, order, profile, and password-management screens
- Reusable layouts and view components
- Query-parameter routing through a small custom MVC-style router
- Accessible interaction details including labels, focus behavior, reduced-motion support, and keyboard-friendly controls
- Local product, profile, and interface imagery included in `public/images/`

## Technology

- PHP 7.4 or newer
- Apache through XAMPP
- HTML5 and PHP templates
- CSS3 with Bootstrap and Font Awesome CDN resources
- Vanilla JavaScript
- No Composer, Node.js, database server, or build step is required for the current prototype

## Requirements

- Windows with [XAMPP](https://www.apachefriends.org/)
- Apache enabled in XAMPP
- PHP 7.4 or newer
- A modern browser with JavaScript enabled
- An internet connection if the external Bootstrap, Font Awesome, and Google Fonts resources should load

## Quick Start With XAMPP

1. Clone or copy the repository into the XAMPP web root:

   ```text
   C:\xampp\htdocs\MediMax
   ```

2. Start **Apache** from the XAMPP Control Panel.

3. Open the application:

   ```text
   http://localhost/MediMax/
   ```

4. Open a page directly with the `page` query parameter, for example:

   ```text
   http://localhost/MediMax/?page=products
   ```

The project also contains a root `index.php`, so requests to the project directory are forwarded to the public front controller in `public/index.php`.

### Optional PHP Development Server

From the repository root, run:

```bash
php -S localhost:8000 -t .
```

Then visit:

```text
http://localhost:8000/
```

For the most predictable asset paths, XAMPP with the project inside `htdocs` is recommended because `config/app.php` currently uses the base URL `/MediMax/`.

## Available Pages

All routes use the format `?page=<route>`.

| Route | URL | Purpose |
| --- | --- | --- |
| `home` | `/?page=home` | Storefront home page; this is the default route |
| `products` | `/?page=products` | Browse and search the catalogue |
| `cart` | `/?page=cart` | View the demo cart |
| `wishlist` | `/?page=wishlist` | View the demo wishlist |
| `orders` | `/?page=orders` | View demo orders for the sample user |
| `about` | `/?page=about` | Pharmacy information and service details |
| `contact` | `/?page=contact` | Contact page UI |
| `login` | `/?page=login` | Login page UI |
| `register` | `/?page=register` | Registration page UI |
| `admin` | `/?page=admin` | Admin dashboard |
| `admin-products` | `/?page=admin-products` | Admin product list |
| `admin-add-product` | `/?page=admin-add-product` | Add-product form UI |
| `admin-edit-product` | `/?page=admin-edit-product&id=1` | Edit-product form UI |
| `admin-users` | `/?page=admin-users` | Admin user list |
| `admin-add-user` | `/?page=admin-add-user` | Add-user form UI |
| `admin-edit-user` | `/?page=admin-edit-user&id=2` | Edit-user form UI |
| `admin-orders` | `/?page=admin-orders` | Admin order list |
| `admin-update-profile` | `/?page=admin-update-profile` | Profile form UI |
| `admin-update-password` | `/?page=admin-update-password` | Password form UI |

## Project Structure

```text
MediMax/
├── app/
│   ├── controllers/       Request handlers for storefront and admin pages
│   ├── core/              Base controller and query-parameter router
│   ├── models/            Static demo data
│   └── views/
│       ├── components/    Reusable UI fragments
│       ├── layouts/       Shared customer and admin layouts
│       └── pages/         Customer and admin page templates
├── config/
│   └── app.php            Base URL, paths, and shared helpers
├── public/
│   ├── css/custom.css     Application styles
│   ├── images/            Product, profile, logo, and page imagery
│   ├── js/app.js          Client-side interactions
│   └── index.php          Public front controller
├── routes/
│   └── routes.php         Route registrations
├── index.php               Root entry point
├── .gitignore              Local, secret, generated, and runtime-file rules
└── README.md               Project documentation
```

## How It Works

1. A request enters through the root `index.php` or `public/index.php`.
2. `config/app.php` defines application paths and URL helpers.
3. `app/core/Router.php` reads the `page` query parameter and selects a registered controller method.
4. Controllers retrieve data from `DummyData` and render a view through `app/core/Controller.php`.
5. Layouts and components assemble the final page.
6. `public/js/app.js` adds presentation behavior such as filtering, sorting, toasts, quantity controls, menus, animations, and password visibility toggles.

## Demo Limitations

This version intentionally focuses on the interface and routing foundation:

- Forms do not currently persist submissions.
- Login and registration do not authenticate users.
- Cart and wishlist actions provide visual feedback but do not update server-side data.
- Admin routes are not protected by authorization middleware.
- Product, user, and order data is hard-coded in `DummyData.php`.
- Images placed in `public/images/temp/` are treated as runtime uploads and are ignored by Git.

Before using this as a production pharmacy application, add server-side validation, authentication, authorization, CSRF protection, secure password hashing, database persistence, file-upload validation, HTTPS, audit logging, and appropriate handling of health-related data.

## Configuration

Update `config/app.php` when the project is hosted under a different path:

```php
define('BASE_URL', '/MediMax/');
```

For example, if the application is served from the domain root, use `/` instead. Keep the trailing slash so the existing URL and asset helpers continue to generate correct paths.

## Development Notes

- Add or change routes in `routes/routes.php`.
- Add controller behavior in `app/controllers/`.
- Update demo records in `app/models/DummyData.php`.
- Add customer or admin pages under `app/views/pages/`.
- Prefer shared layouts and components over duplicating markup.
- Keep committed assets that are referenced by templates or CSS in `public/images/`.
- Run a PHP syntax check after modifying PHP files:

  ```bash
  php -l path/to/file.php
  ```

## Contributing

1. Create a feature branch.
2. Keep changes focused and consistent with the existing PHP structure.
3. Verify the affected route in a browser.
4. Run PHP syntax checks on changed PHP files.
5. Do not commit `.env` files, local editor settings, logs, databases, or runtime uploads.
6. Open a pull request describing the behavior that changed and how it was tested.

## License

No license has been specified for this repository yet. Add a `LICENSE` file before distributing the project or accepting external reuse under defined terms.
