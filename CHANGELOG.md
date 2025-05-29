# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.12] - 2025-05-29

### Fixed
- Fixed SQL query error in Subscription Metrics Dashboard
  - Resolved ambiguous column name issue by properly qualifying all table columns
  - Fixed SQLite compatibility issues with quoting in raw SQL
  - Improved query structure in MRR/ARR calculations

## [1.2.11] - 2025-05-28

### Fixed
- Fixed circular dependency issue in service providers
  - Resolved circular dependency between SubscriptionService and PayPalSubscriptionService
  - Updated SubscriptionServiceProvider to properly register all services
  - Added error handling to the Customer Subscription Overview component
  - Fixed "trim(): Argument #1 ($string) must be of type string, array given" error when accessing Customer Overview
- Added missing customer detail view
  - Created new admin.customers.show route in web.php
  - Added Customer Show component to display detailed user information
  - Fixed "Route [admin.customers.show] not defined" error when clicking "View Customer"

## [1.2.10] - 2025-05-31

### Changed
- Enhanced sidebar navigation system
  - Combined admin navigation elements from admin.blade.php into sidebar.blade.php
  - Added conditional display of admin sections based on user roles
  - Updated route names to match current routes in admin.php
  - Added missing navigation link for Revenue Analytics
  - Added Monitoring section to System Health navigation
  - Maintained consistent styling and design patterns
  - Preserved role-based access control for all admin sections

## [1.2.9] - 2025-05-26

### Added
- System health monitoring dashboard
  - Real-time server status monitoring (CPU, memory, disk)
  - Queue health metrics with failure tracking
  - Cache performance statistics and hit ratio
  - Error rate monitoring with log analysis
  - Auto-refreshing metrics
  - Health status indicators
  - Detailed system information display

## [1.2.8] - 2025-05-27

### Fixed
- Fixed error "Call to undefined method Livewire\Volt\VoltManager::provide()" in SubscriptionServiceProvider.php
- Removed invalid method calls that don't exist in Livewire Volt 1.7.1
- Updated service provider to work correctly with the current Volt API
- Fixed route registration error with the ManageListAccess Livewire component
- Added documentation explaining the proper way to use services in Volt components
- Temporarily modified `/lists/{urlList}/access` route to use a closure function

### Added
- Revenue reports export system
  - Custom date range selection
  - Multiple export formats (XLSX, CSV, PDF)
  - Automated report scheduling (daily, weekly, monthly)
  - Tax reporting features
  - Email delivery of scheduled reports
  - S3 storage integration for report files
  - Interactive UI for report configuration

## [1.2.7] - 2025-05-27

### Added
- Subscription metrics dashboard
  - Real-time active subscriptions tracking
  - Trial conversion rate analysis
  - Churn rate monitoring with historical data
  - MRR/ARR calculations and trend visualization
  - Revenue trend chart with customizable date ranges
  - Interactive metrics filtering and date selection
  - Responsive design for all screen sizes

## [1.2.6] - 2025-05-27

### Added
- Customer subscription overview interface
  - Advanced customer listing with subscription status
  - Real-time subscription metrics per customer
  - Usage statistics visualization
  - Multiple filtering options for customer management
  - Comprehensive view of customer subscription states
  - Usage tracking with progress indicators
  - Quick access to detailed subscription information

## [1.2.5] - 2024-01-30

### Changed
- Converted plan management interface to use Livewire Volt
  - Reimplemented plan listing with reactive state management
  - Converted plan creation/editing forms to Volt syntax
  - Added computed properties for derived plan data
  - Improved form validation and error handling
  - Enhanced UI responsiveness with Volt lifecycle hooks
  - Removed legacy Livewire class components
  - Optimized state management and data flow

## [1.2.4] - 2025-05-27

### Added
- Complete user and role management system
  - Advanced user listing with filters and search
  - User activity tracking and management
  - Role management interface with CRUD operations
  - Dynamic permission management
  - Bulk role update capabilities
  - Protected system roles (admin, free, premium)
  - Real-time updates and notifications
  - User statistics dashboard

## [1.2.3] - 2025-05-27

### Added
- Comprehensive subscription management interface
  - Advanced subscription listing with filters and search
  - Detailed subscription view with real-time updates
  - Manual subscription controls for admins
  - Subscription usage tracking and visualization
  - PayPal integration status monitoring
  - Customer information overview
  - Usage statistics with progress bars

## [1.2.2] - 2025-05-27

### Changed
- Expanded admin dashboard scope and requirements
  - Added comprehensive subscription and plan management
  - Enhanced user and role management features
  - Added detailed financial management capabilities
  - Included system monitoring and health tracking
  - Enhanced activity logging and audit trails
  - Added PayPal integration monitoring

## [1.2.1] - 2025-05-27

### Added
- Subscription renewal notification system
  - Created SubscriptionRenewalNotification for upcoming and completed renewals
  - Added daily scheduled task for checking upcoming renewals
  - Implemented notification preferences in user settings
  - Added PayPal webhook integration for renewal events
  - 7-day and 1-day renewal reminders
  - Email and in-app notifications support
  - Notification preference controls in settings

## [1.2.0] - 2025-05-27

### Added
- Plan versioning system
  - New PlanVersion model for tracking plan features, pricing, and validity periods
  - Version transition handling with proration support
  - Automatic version migration for existing plans and subscriptions
  - PayPal integration for version-based billing
- Subscription plan switching functionality
  - Automated proration calculations
  - PayPal subscription updates with price adjustments
  - Seamless version transitions
- Version management in Plan model
  - Version tracking and retrieval methods
  - Active version management
  - Version-based feature access control
- Enhanced subscription capabilities
  - Version tracking in subscriptions
  - Helper methods for version switching
  - Proration calculation support
- Improved PayPal integration
  - Version-aware subscription management
  - Proration handling for plan changes
  - Price adjustment support

## [1.1.0] - 2025-05-26

### Added
- PayPalTokenService for OAuth2 token management
  - Automatic token refresh
  - Token caching
  - Token status checking
- Enhanced PayPalSubscriptionService
  - Subscription plan creation and syncing
  - Payment processing and verification
  - Success/failure handling
  - Trial period support
- New subscription activation flow
  - Real-time activation status
  - Automatic retry mechanism
  - User-friendly error handling
  - Webhook integration for status updates
- PayPalAPIService for robust API interactions
  - Rate limiting handling
  - Idempotency key generation
  - Retry mechanism with exponential backoff
  - Comprehensive error handling

### Changed
- Updated project architecture to prioritize Volt components and services over middleware
- Restructured subscription system to use service-based approach
- Enhanced feature gating to use Volt components with services

### Added
- New RoleCheckService for centralized role and permission validation
- Volt-based role-check component for conditional UI rendering
- Feature-gate component with premium upgrade prompts
- Service-based permission validation system
- New SubscriptionService for subscription state management
- UsageTrackingService for feature usage monitoring
- SubscriptionServiceProvider for service registration
- Services integration with Volt components
- Refactored all Livewire components to use Volt syntax for better maintainability and performance
- Removed old PHP class-based components in favor of Volt components
- Components refactored:
  - ManageListAccess
  - NotificationsDropdown
  - RequestListAccess
  - ManageSubscription
  - PricingTable
  - Subscribe

### Removed
- Removed old PHP class-based Livewire components from app/Livewire directory
## [1.2.0] - 2025-05-28
### Added
- Redesigned all error pages with modern UI/UX and interactive illustrations
- Custom SVG illustrations for each error type (404, 403, 500, 503, 419, 429, 401, 402)
- Enhanced error page template with responsive split-screen layout
- Advanced animations and micro-interactions for error pages
- Improved error messaging and user guidance

### Changed
- Updated minimal.blade.php with modern design elements and animations
- Enhanced typography across error pages using Poppins font
- Standardized error page styling to match dashboard theme

## [1.2.1] - 2025-05-28
### Fixed
- Resolved "The yield expression can only be used inside a function" error in error pages
- Updated error page templates to use @section/@endsection for message descriptions
- Fixed template namespace references (errors:: to errors.)
- Temporarily disabled test error abort in routes for development
