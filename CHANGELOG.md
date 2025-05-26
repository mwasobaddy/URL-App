# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-05-26

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