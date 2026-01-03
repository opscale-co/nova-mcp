<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\DomainResolver;

/**
 * Workbench Domain Resolver
 *
 * Example implementation of DomainResolver for the workbench environment.
 * This provides DBML documentation for a simple users table.
 */
class WorkbenchDomainResolver implements DomainResolver
{
    /**
     * Resolve and return the complete DBML representation of the domain.
     *
     * This example uses a simple users table to demonstrate the DBML format.
     * In a real implementation, this would scan all your models and generate
     * comprehensive DBML with business descriptions.
     */
    public function resolve(): string
    {
        return <<<'DBML'
Project nova_mcp_workbench {
  database_type: 'PostgreSQL'
  Note: '''
    # Workbench Domain Model

    This is an example domain model for the Nova MCP workbench environment.
    It demonstrates how business domain information should be documented.

    ## Purpose
    This workbench provides a simple user management system to demonstrate
    the capabilities of the Nova MCP platform.
  '''
}

Table users {
  id bigint [pk, increment, note: 'Unique identifier for each user']
  name varchar(255) [not null, note: 'Full name of the user (e.g., "John Smith")']
  email varchar(255) [unique, not null, note: 'Email address used for login and notifications. Must be unique across all users.']
  email_verified_at timestamp [null, note: 'When the user verified their email address. NULL if not yet verified.']
  password varchar(255) [not null, note: 'Encrypted password for authentication. Never stored in plain text.']
  remember_token varchar(100) [null, note: 'Token used for "Remember Me" functionality when logging in']
  created_at timestamp [not null, note: 'When this user account was created']
  updated_at timestamp [not null, note: 'When this user record was last modified']

  Note: '''
    ## Users Table

    **Business Purpose**:
    Stores information about people who can access the platform. Each user represents
    a person with login credentials and profile information.

    **Common Use Cases**:
    - User registration and authentication
    - Profile management
    - Access control and permissions
    - Activity tracking and audit trails

    **Business Rules**:
    - Email addresses must be unique
    - Passwords must be encrypted before storage
    - Email verification is recommended but not required
    - Users can be soft-deleted (if using SoftDeletes trait)

    **Related Processes**:
    - User Registration
    - Login/Authentication
    - Password Reset
    - Profile Updates
  '''
}

DBML;
    }
}
