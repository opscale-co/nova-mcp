<?php

namespace Opscale\NovaMCP\Contracts;

interface DomainResolver
{
    /**
     * Resolve and return the complete DBML representation of the domain.
     *
     * This method should generate a comprehensive DBML (Database Markup Language)
     * schema that includes:
     * - Project-level documentation with notes
     * - All table definitions with business descriptions
     * - All column definitions with business descriptions
     * - Enum values where applicable
     * - Relationship definitions
     *
     * The DBML must be valid and include notes at all levels (project, tables, columns).
     *
     * @return string The complete DBML schema with business descriptions
     *
     * @example
     * Project MyProject [note: 'E-commerce platform for selling products online'] {
     *   database_type: 'PostgreSQL'
     * }
     *
     * Table users [note: 'User accounts that can access the system'] {
     *   id integer [primary key, increment]
     *   email varchar [not null, unique, note: 'Unique email address for login']
     *   first_name varchar [not null, note: 'User\'s given name']
     *   status varchar [not null, note: 'Account status. Values: active, suspended, inactive']
     * }
     *
     * Table orders [note: 'Customer orders placed in the system'] {
     *   id integer [primary key, increment]
     *   user_id integer [not null, note: 'Reference to the user who placed the order']
     *   total decimal [not null, note: 'Total order amount in currency']
     * }
     *
     * Ref: orders.user_id > users.id
     */
    public function resolve(): string;
}
