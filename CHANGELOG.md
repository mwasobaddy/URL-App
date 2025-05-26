# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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