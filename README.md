## Support us

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Quickly deploy a MCP (Model Context Protocol) server for your platform. This package turns your Laravel Nova application into an AI-ready platform, exposing your business domain, processes, and operations through the MCP protocol.

With Nova MCP, AI assistants can:
- Manage your data through CRUD operations on Nova resources
- Execute business logic through custom actions
- Understand your domain via DBML schema definitions
- Follow your workflows via BPMN process definitions

![Demo](https://raw.githubusercontent.com/opscale-co/nova-mcp/refs/heads/main/screenshots/nova-mcp.gif)

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-mcp.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-mcp)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash

composer require opscale-co/nova-mcp

```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php

// in app/Providers/NovaServiceProvider.php
// ...
public function tools()
{
    return [
        // ...
        new \Opscale\NovaMCP\Tool(),
    ];
}

```

## Usage

The MCP server runs automatically in local configuration. To enable it, you need to implement the required resolvers in your application.

### 1. Implement Resolvers

Create implementations for the following resolver contracts in your service provider:

```php
use Opscale\NovaMCP\Contracts\ResourcesResolver;
use Opscale\NovaMCP\Contracts\ToolsResolver;
use Opscale\NovaMCP\Contracts\DomainResolver;
use Opscale\NovaMCP\Contracts\ProcessResolver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nova resources available for CRUD operations
        $this->app->singleton(ResourcesResolver::class, YourResourcesResolver::class);

        // Business logic actions (opscale-co/actions)
        $this->app->singleton(ToolsResolver::class, YourToolsResolver::class);

        // Domain schema in DBML format
        $this->app->singleton(DomainResolver::class, YourDomainResolver::class);

        // Business processes in BPMN format
        $this->app->singleton(ProcessResolver::class, YourProcessResolver::class);
    }
}
```

### 2. Resources Resolver

**Why:** Defines which Nova resources the AI can manage. Without this, the AI wouldn't know what data entities exist in your platform or how to create, read, update, or delete records.

```php
class YourResourcesResolver implements ResourcesResolver
{
    public function resolve(): array
    {
        return [
            \App\Nova\User::class,
            \App\Nova\Product::class,
            \App\Nova\Order::class,
        ];
    }
}
```

### 3. Tools Resolver

**Why:** Exposes your business logic actions to the AI. While CRUD handles data, tools handle operations with side effects like sending emails, processing payments, or triggering workflows. These are the "verbs" of your platform.

```php
class YourToolsResolver implements ToolsResolver
{
    public function resolve(): array
    {
        return [
            \App\Actions\SendInvoice::class,
            \App\Actions\ApproveOrder::class,
            \App\Actions\ResetPassword::class,
        ];
    }
}
```

### 4. Domain Resolver

**Why:** Provides the AI with your domain schema so it understands entity relationships, required fields, and data dependencies. This allows the AI to know that an Order requires a Customer, or that an Invoice needs line items.

```php
class YourDomainResolver implements DomainResolver
{
    public function resolve(): string
    {
        return file_get_contents(base_path('docs/domain.dbml'));
    }
}
```

### 5. Process Resolver

**Why:** Defines your business workflows so the AI knows the correct sequence of steps for each task. Instead of guessing, the AI follows your documented processes - knowing that user registration requires creating an account, then resetting the password, then sending a welcome email.

```php
class YourProcessResolver implements ProcessResolver
{
    public function resolve(): string
    {
        return file_get_contents(base_path('docs/processes.bpmn'));
    }
}
```

### Running the Server

The MCP server is automatically registered and available via the `mcp:serve` command:

```bash
php artisan mcp:serve nova-mcp
```

You can also use it with Claude Desktop by adding to your MCP configuration:

```json
{
  "mcpServers": {
    "nova-mcp": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "nova-mcp"],
      "cwd": "/path/to/your/laravel/app"
    }
  }
}
```

### Nova Tool

Click on the "nova-mcp" menu item in your Nova app to see the tool provided by this package.

## Testing

``` bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.