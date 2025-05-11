# Copilot Guidelines: URL-App Development

This document provides best practices and architectural guidance for developing the URL-App using the following stack:

* **PHP:** 8.2+
* **Laravel:** 12.0+
* **Laravel Tinker:** 2.10.1+
* **Livewire Flux:** 2.1.1+
* **Livewire Volt:** 1.7.0+

**Project Description:**

URL-App is a web application that allows users to create, manage, and share lists of URLs. Key features include list creation, URL addition/editing/deletion, custom/automatic URL generation, list publishing/sharing, and user list management.

**I. Core Principles**

1.  **Follow Laravel Conventions:**
    * Adhere to Laravel's directory structure, naming conventions, and Eloquent ORM principles.
    * Use Artisan commands for generating boilerplate code (controllers, models, migrations, etc.).
    * Leverage Blade templates for view rendering.

2.  **Component-Based Architecture:**
    * Prioritize Livewire components (using Volt and Flux where applicable) for building interactive UI elements.
    * Break down the UI into smaller, reusable components.

3.  **Clean Code:**
    * Write readable, maintainable, and well-documented code.
    * Follow SOLID principles where appropriate.
    * Use clear and descriptive variable/function names.

4.  **Security First:**
    * Sanitize user inputs to prevent XSS and SQL injection vulnerabilities.
    * Use Laravel's built-in security features (CSRF protection, etc.).
    * Secure routes and authorize access where necessary.

**II. Technology Stack Best Practices**

1.  **Laravel 12.0+**
    * Leverage new features and syntax enhancements in Laravel 12.
    * Use Eloquent for database interactions.
    * Utilize Laravel's routing system for defining application routes.
    * Employ middleware for request filtering (e.g., authentication).
    * Use Laravel's validation features for data validation.

2.  **Livewire**
    * **Volt:**
        * Use Volt for creating single-file Livewire components, especially for simpler UI elements. This promotes conciseness.
        * Favor Volt when a component's logic is tightly coupled with its view.
    * **Flux:**
        * Employ Flux for managing state within Livewire components, particularly for more complex interactions.
        * Use actions to mutate state and getters to derive data from state.
        * Structure Flux stores to reflect the component's data requirements.
    * **General Livewire:**
        * Use Livewire for dynamic parts of the UI (e.g., adding/editing URLs, list interactions).
        * Keep Livewire components focused on specific UI concerns.
        * Avoid excessive database queries within the view; fetch data in the component's methods.
        * Use Livewire's lifecycle hooks (e.g., `mount`, `updating`) effectively.
        * Use Livewire's events for communication between components.

3.  **PHP 8.2+**
    * Write code that is compatible with PHP 8.2+ and takes advantage of its features.
    * Use modern PHP syntax and best practices.

4.  **Laravel Tinker**
    * Use Tinker for debugging and interacting with the application from the command line.
    * Execute database queries, test logic, and inspect application state using Tinker.

**III. Feature-Specific Guidance**

1.  **URL List Management (FR001 - FR005, FR011 - FR012)**
    * Create a `UrlList` model to represent a list of URLs.
    * Use a relationship (e.g., `hasMany`) to associate URLs with a list.
    * Implement Livewire components for:
        * Creating new lists (FR001)
        * Adding, editing, and deleting URLs within a list (FR002 - FR005)
        * Displaying lists and individual URLs (FR003, FR011, FR010)
        * Deleting entire lists (FR012)
    * Use Volt for simpler list and URL display/management.
    * Use Flux for managing the state of URL editing or adding workflows within a list.

2.  **Custom/Automatic URL Generation (FR006 - FR007)**
    * Add a `custom_url` column to the `UrlList` model.
    * Implement logic to:
        * Allow users to input a custom URL (FR006).
        * Validate the uniqueness of custom URLs.
        * Generate a unique, random URL if the user doesn't provide one (FR007).
    * Use Livewire to handle user input and display generated URLs.

3.  **Publishing and Sharing (FR008 - FR010)**
    * Implement a "publish" mechanism (e.g., a boolean flag on the `UrlList` model).
    * Ensure that only published lists are accessible to the public.
    * Use Laravel's routing to handle accessing lists via their URLs.
    * Provide UI elements for easily copying the list URL (FR009).

**IV. Code Generation Prompts**

When prompting the AI, be specific and provide context:

* **Good:** "Generate a Livewire component using Volt to display a list of URLs with edit and delete buttons."
* **Better:** "In the `UrlList` module, generate a Livewire component using Volt named `UrlListDisplay` that fetches the URLs associated with a given `UrlList` model instance and displays them in a table. Include buttons to edit and delete each URL, which should emit Livewire events. Use Flux to manage the state of the editing workflow."
* **Include:**
    * Component names and locations.
    * Model names and relationships.
    * Specific functionality required.
    * Data to be handled.
    * State management needs (if any).
    * Error handling considerations.
    * Security considerations.

**V. Example Workflow**

1.  **Start with Models and Migrations:** Generate the `UrlList` and `Url` models and their corresponding migrations.
2.  **Implement List Creation:** Create a Livewire component (potentially using Volt) for creating new URL lists.
3.  **Implement URL Management:** Create Livewire components (using Volt and Flux) for adding, editing, and deleting URLs within a list.
4.  **Implement URL Display:** Create a Livewire component to display the URLs in a list.
5.  **Implement Custom/Automatic URL Generation:** Add the logic for handling custom and automatic URL generation within the list creation process.
6.  **Implement Publishing and Sharing:** Add the logic for publishing lists and generating shareable links.
7.  **Implement List Overview:** Create a Livewire component to display all of a user's lists.

**VI. Additional Considerations**

* **Testing:** Write unit and feature tests to ensure code quality and prevent regressions.
* **Error Handling:** Implement robust error handling to gracefully handle exceptions and provide informative error messages to the user.
* **Performance:** Optimize database queries and UI rendering for optimal performance.
* **User Experience (UX):** Design a user-friendly and intuitive interface.

By following these guidelines, we can ensure that the URL-App is developed in a consistent, efficient, and maintainable manner.